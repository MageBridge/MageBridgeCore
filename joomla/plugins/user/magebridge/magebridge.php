<?php
/**
 * Joomla! MageBridge - User plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge User Plugin
 */
class PlgUserMageBridge extends JPlugin
{
	/*
	 * Temporary container for original user-data
	 */
	private $original_data = array();

	/**
	 * Return a MageBridge configuration parameter
	 *
	 * @param string $name
	 *
	 * @return mixed $value
	 */
	private function getParam($name = null)
	{
		return MagebridgeModelConfig::load($name);
	}

	/**
	 * Return the MageBridge user-object
	 *
	 * @return mixed $value
	 */
	private function getUser()
	{
		return MageBridge::getUser();
	}

	/**
	 * Event onUserAfterDelete
	 *
	 * @param array  $user
	 * @param bool   $success
	 * @param string $msg
	 *
	 * @return null
	 */
	public function onUserAfterDelete($user, $success, $msg = '')
	{
		MageBridgeModelDebug::getInstance()->notice("onUserAfterDelete::userDelete on user " . $user['username']);

		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserAfterDelete') == false)
		{
			return;
		}

		// Use the delete-function in the bridge
		$this->getUser()->delete($user);

		return;
	}

	/**
	 * Event onUserBeforeSave
	 *
	 * @param array $oldUser
	 * @param bool  $isnew
	 * @param array $newUser
	 *
	 * @return bool
	 */
	public function onUserBeforeSave($oldUser, $isnew, $newUser)
	{
		if (isset($oldUser['id']))
		{
			$id = $oldUser['id'];
		}
		else
		{
			$id = 0;
		}

		$this->original_data[$id] = array('email' => $oldUser['email']);

		return true;
	}

	/**
	 * Event onUserAfterSave
	 *
	 * @param array  $user
	 * @param bool   $isnew
	 * @param bool   $success
	 * @param string $msg
	 *
	 * @return bool
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		if (isset($user['id']))
		{
			$id = $user['id'];
		}
		else
		{
			$id = 0;
		}

		if (isset($this->original_data[$id]))
		{
			$user['original_data'] = $this->original_data[$id];
		}

		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserAfterSave') == false)
		{
			return false;
		}

		// Get system variables
		$application = JFactory::getApplication();

		// Copy the username to the email address (if this is configured)
		if ($application->isSite() == true && $this->getParam('username_from_email') == 1 && $user['username'] != $user['email'])
		{
			MageBridgeModelDebug::getInstance()->notice("onUserAfterSave::bind on user " . $user['username']);

			// Load the right JUser object
			$data = array('username' => $user['email']);
			$object = JFactory::getUser($user['id']);

			// Check whether user-syncing is allowed for this user
			if ($this->getUser()->allowSynchronization($object, 'save') == true)
			{
				// Change the record in the database
				$object->bind($data);
				$object->save();

				// Bind this new user-object into the session
				$session = JFactory::getSession();
				$session_user = $session->get('user');

				if ($session_user->id == $user['id'])
				{
					$session_user->username = $user['email'];
				}
			}
		}

		// Synchronize this user-record with Magento
		if ($this->getParam('enable_usersync') == 1)
		{
			MageBridgeModelDebug::getInstance()->notice("onUserAfterSave::usersync on user " . $user['username']);

			// Sync this user-record with the bridge
			$this->getUser()->synchronize($user);
		}

		return true;
	}

	/**
	 * Event onUserLogin
	 *
	 * @param array $user
	 * @param array $options
	 *
	 * @return bool
	 */
	public function onUserLogin($user = null, $options = array())
	{
		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserLogin', $options) == false)
		{
			return true;
		}

		// Get system variables
		$application = JFactory::getApplication();

		// Synchronize this user-record with Magento
		if ($this->getParam('enable_usersync') == 1 && $application->isSite())
		{
			$user['id'] = JFactory::getUser()->id;
			$user = $this->getUser()->synchronize($user);
		}

		// Perform a login
		$this->getUser()->login($user['email']);

		return true;
	}

	/**
	 * Event onUserAfterLogin
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function onUserAfterLogin($options = array())
	{
		$application = JFactory::getApplication();

		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserLogin', $options) == false)
		{
			return true;
		}

		// Check whether SSO is enabled
		if ($this->getParam('enable_sso') == 1)
		{

			$user = $options['user'];

			if ($application->isSite() && $this->getParam('enable_auth_frontend') == 1)
			{
				MageBridgeModelUserSSO::doSSOLogin($user);

			}
			else
			{
				if ($application->isAdmin() && $this->getParam('enable_auth_backend') == 1)
				{
					MageBridgeModelUserSSO::doSSOLogin($user);
				}
			}
		}

		return true;
	}

	/**
	 * Event onUserLogout
	 *
	 * @param array $user
	 * @param array $options
	 *
	 * @return bool
	 */
	public function onUserLogout($user = null, $options = array())
	{
		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserLogout', $options) == false)
		{
			return true;
		}

		// Get system variables
		$application = JFactory::getApplication();
		$session = JFactory::getSession();
		$bridge = MageBridgeModelBridge::getInstance();
		$register = MageBridgeModelRegister::getInstance();

		// Remove the Magento cookies
		$cookies = array('frontend', 'user_allowed_save_cookie', 'persistent_shopping_cart', 'mb_postlogin');

		foreach ($cookies as $cookie)
		{
			if (isset($_COOKIE[$cookie]))
			{
				unset($_COOKIE[$cookie]);
			}

			setcookie($cookie, '', time() - 1000);
			setcookie($cookie, '', time() - 1000, '/');
			setcookie($cookie, '', time() - 1000, '/', '.' . JURI::getInstance()->toString(array('host')));
			$application->input->set($cookie, null, 'cookie');
			JFactory::getSession()->set('magebridge.cookie.' . $cookie, null);
		}

		// Set the Magento session to null
		$session->set('magento_session', null);

		// Build the bridge and fetch the result
		if ($this->getParam('link_to_magento') == 0)
		{
			$arguments = array('disable_events' => 1);
			$id = $register->add('logout', null, $arguments);
			$bridge->build();
		}

		return true;
	}

	/**
	 * Event onUserAfterLogout
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function onUserAfterLogout($options = array())
	{
		$application = JFactory::getApplication();

		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserLogout', $options) == false)
		{
			return true;
		}

		// Check whether SSO is enabled
		if ($this->getParam('enable_sso') == 1 && isset($options['username']))
		{
			if ($application->isSite() && $this->getParam('enable_auth_frontend') == 1)
			{
				MageBridgeModelUserSSO::doSSOLogout($options['username']);

			}
			else
			{
				if ($application->isAdmin() && $this->getParam('enable_auth_backend') == 1)
				{
					MageBridgeModelUserSSO::doSSOLogout($options['username']);
				}
			}
		}

		return true;
	}
}
