<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge Single Sign On class
 */
class MageBridgeModelUserSSO extends MageBridgeModelUser
{
	/**
	 * Method for logging in with Magento (Single Sign On)
	 * 
	 * @param array $user
	 * @return bool|exit
	 */
	static public function doSSOLogin($user = null)
	{
		// Abort if the input is not valid
		if (empty($user) || (empty($user['email']) && empty($user['username']))) {
			return false;
		}

		// Get system variables
		$application = JFactory::getApplication();
		$session = JFactory::getSession();

		// Store the current page, so we can redirect to it after SSO is done
		if ($return = JFactory::getApplication()->input->get('return', '', 'base64')) {
			$return = base64_decode($return);
		} else {
			$return = MageBridgeUrlHelper::current();
		}

		$session->set('magento_redirect', $return);

		// Determine the user-name
		$application_name = ($application->isAdmin()) ? 'admin' : 'frontend';
		if ($application_name == 'admin') {
			$username = $user['username'];
		} else if (!empty($user['email'])) {
			$username = $user['email'];
		} else {
			$username = $user['username'];
		}

		// Get the security token
		$token = (method_exists('JSession', 'getFormToken')) ? JSession::getFormToken() : JUtility::getToken();

		// Construct the URL
		$arguments = array(
			'sso=login',
			'app='.$application_name,
			'base='.base64_encode(JURI::base()),
			'userhash='.MageBridgeEncryptionHelper::encrypt($username),
			'token='.$token,
		);

		$url = MageBridgeModelBridge::getInstance()->getMagentoBridgeUrl().'?'.implode('&', $arguments);

		// Redirect the browser to Magento
		MageBridgeModelDebug::getInstance()->trace( "SSO: Sending arguments", $arguments );
		$application->redirect($url);

		return true;
	}

	/**
	 * Method for logging out with Magento (Single Sign On)
	 * 
	 * @param string $username
	 * @return bool|exit
	 */
	static public function doSSOLogout($username = null)
	{
		// Abort if the input is not valid
		if (empty($username)) {
			return false;
		}

		// Get system variables
		$application = JFactory::getApplication();
		$session = JFactory::getSession();

		// Determine the application
		$application_name = ($application->isAdmin()) ? 'admin' : 'frontend';

		// Get the security token
		$token = (method_exists('JSession', 'getFormToken')) ? JSession::getFormToken() : JUtility::getToken();
		
		// Set the redirection URL
		if ($application_name == 'admin') {
			$redirect = JURI::current();
		} else {
			$redirect = MageBridgeUrlHelper::current();
		}

		// Construct the URL
		$arguments = array(
			'sso=logout',
			'app='.$application_name,
			'redirect='.base64_encode($redirect),
			'userhash='.MageBridgeEncryptionHelper::encrypt($username),
			'token='.$token,
		);
		$url = MageBridgeModelBridge::getInstance()->getMagentoBridgeUrl().'?'.implode('&', $arguments);

		// Redirect the browser to Magento
		MageBridgeModelDebug::getInstance()->notice( "SSO: Logout of '$username' from ".$application_name);
		$application->redirect($url);

		return true;
	}

	/**
	 * Method to check the SSO-request coming back from Magento
	 * 
	 * @param null
	 * @return bool|exit
	 */
	static public function checkSSOLogin()
	{
		// Check the security token
		JSession::checkToken('get') or die('SSO redirect failed due to wrong token');

		// Get system variables
		$application = JFactory::getApplication();
		$session = JFactory::getSession();

		// Get the current Magento session
		$magento_session = JFactory::getApplication()->input->getCmd('session');
		if (!empty($magento_session)) {
			MageBridgeModelBridge::getInstance()->setMageSession($magento_session);
			MageBridgeModelDebug::getInstance()->notice( "SSO: Magento session ".$magento_session);
		}

		// Redirect back to the original URL
		$redirect = $session->get('magento_redirect', JURI::base());
		if (empty($redirect)) {
			$redirect = MageBridgeUrlHelper::route('customer/account');
		}
		MageBridgeModelDebug::getInstance()->notice( "SSO: Redirect to $redirect" );
		$application->redirect($redirect);
		return true;
	}
}
