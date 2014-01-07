<?php
/**
 * Joomla! MageBridge - User plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );
        
// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge User Plugin
 */
class plgUserMageBridge extends JPlugin
{
    /**
     * Return a MageBridge configuration parameter
     * 
     * @access private
     * @param string $name
     * @return mixed $value
     */
    private function getParam($name = null)
    {
        return MagebridgeModelConfig::load($name);
    }

    /**
     * Return the MageBridge user-object
     * 
     * @access private
     * @param string $name
     * @return mixed $value
     */
    private function getUser()
    {
        return MageBridge::getUser();
    }

    /**
     * Event onUserAfterDelete
     * 
     * @access public
     * @param array $user
     * @param bool $success
     * @param string $msg
     * @return null
     */
    public function onUserAfterDelete($user, $succes, $msg = '')
    {
        MageBridgeModelDebug::getInstance()->notice( "onUserAfterDelete::userDelete on user ".$user['username'] );

        // Check if we can run this event or not
        if (MageBridgePluginHelper::allowEvent('onUserAfterDelete') == false) return;

        // Use the delete-function in the bridge
        $this->getUser()->delete($user);

        return null;
    }

    /**
     * Event onUserAfterSave
     * 
     * @access public
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     * @return bool
     */
    public function onUserAfterSave($user, $isnew, $success, $msg)
    {
        // Check if we can run this event or not
        if (MageBridgePluginHelper::allowEvent('onUserAfterSave') == false) return;

        // Get system variables
        $application = JFactory::getApplication();

        // Copy the username to the email address (if this is configured)
        if ($application->isSite() == true && $this->getParam('username_from_email') == 1 && $user['username'] != $user['email']) {

            MageBridgeModelDebug::getInstance()->notice( "onUserAfterSave::bind on user ".$user['username'] );

            // Load the right JUser object
            $data = array('username' => $user['email']);
            $object = new JUser();
            $object->load($user['id']);

            // Check whether user-syncing is allowed for this user
            if ($this->getUser()->allowSynchronization($object, 'save') == true) {

                // Change the record in the database
                $object->bind($data);
                $object->save();
        
                // Bind this new user-object into the session 
                $session = JFactory::getSession();
                $session_user = $session->get('user');
                if ($session_user->id == $user['id']) {
                    $session_user->username = $user['email'];
                }
            }
        }

        // Synchronize this user-record with Magento
        if ($this->getParam('enable_usersync') == 1) {
            MageBridgeModelDebug::getInstance()->notice( "onUserAfterSave::usersync on user ".$user['username'] );

            // Sync this user-record with the bridge
            $this->getUser()->synchronize($user);
        }

        return true;
    }

    /**
     * Event onUserLogin
     * 
     * @access public
     * @param array $user
     * @param array $options
     * @return bool
     */
    public function onUserLogin( $user = null, $options = array())
    {
        // Check if we can run this event or not
        if (MageBridgePluginHelper::allowEvent('onUserLogin', $options) == false) {
            return;
        }

        // Get system variables
        $application = JFactory::getApplication();
            
        // Synchronize this user-record with Magento
        if ($this->getParam('enable_usersync') == 1 && $application->isSite()) {
            $user['id'] = JFactory::getUser()->id;
            $user = $this->getUser()->synchronize($user);
        }

        // Perform a login
        $this->getUser()->login($user['email']);

        // Check whether SSO is enabled
        if ($this->getParam('enable_sso') == 1) {

            if ($application->isSite() && $this->getParam('enable_auth_frontend') == 1) {
                MageBridgeModelUserSSO::doSSOLogin($user);
            } else if ($application->isAdmin() && $this->getParam('enable_auth_backend') == 1) {
                MageBridgeModelUserSSO::doSSOLogin($user);
            }
        }
        return true;
    }

    /**
     * Event onUserLogout
     * 
     * @access public
     * @param array $user
     * @param array $options
     * @return bool
     */
    public function onUserLogout( $user = null, $options = array())
    {
        // Check if we can run this event or not
        if (MageBridgePluginHelper::allowEvent('onUserLogout', $options) == false) {
            return;
        }

        // Get system variables
        $application = JFactory::getApplication();
        $session = JFactory::getSession();
        $bridge = MageBridgeModelBridge::getInstance();
        $register = MageBridgeModelRegister::getInstance();

        // Remove the Magento cookies
        $cookies = array('frontend', 'user_allowed_save_cookie', 'persistent_shopping_cart');
        foreach($cookies as $cookie) {
            if(isset($_COOKIE[$cookie])) unset($_COOKIE['frontend']);
            setcookie($cookie, '', time()-1000);
            setcookie($cookie, '', time()-1000, '/');
            setcookie($cookie, '', time()-1000, '/', '.'.JURI::getInstance()->toString(array('host')));
            JRequest::setVar($cookie, null, 'cookie');
            JFactory::getSession()->set('magebridge.cookie.'.$cookie, null);
        }

        // Set the Magento session to null
        $session->set('magento_session', null);

        // Build the bridge and fetch the result
        $arguments = array('disable_events' => 1);
        $id = $register->add('logout', null, $arguments);
        $bridge->build();

        // Check whether SSO is enabled
        if ($this->getParam('enable_sso') == 1 && isset($user['username'])) {
            if ($application->isSite() && $this->getParam('enable_auth_frontend') == 1) {
                MageBridgeModelUserSSO::doSSOLogout($user['username']);
            } else if ($application->isAdmin() && $this->getParam('enable_auth_backend') == 1) {
                MageBridgeModelUserSSO::doSSOLogout($user['username']);
            }
        }

        return true;
    }

    /**
     * Joomla! 1.5 alias
     * 
     * @access public
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     * @return null
     */
    public function onAfterStoreUser($user, $isnew, $success, $msg)
    {
        return $this->onUserAfterSave($user, $isnew, $success, $msg);
    }

    /**
     * Joomla! 1.5 alias
     * 
     * @access public
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     * @return null
     */
    public function onAfterDeleteUser($user, $succes, $msg)
    {
        return $this->onUserAfterDelete($user, $succes, $msg);
    }

    /**
     * Joomla! 1.5 alias
     * 
     * @access public
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     * @return null
     */
    public function onLoginUser($user, $options)
    {
        return $this->onUserLogin($user, $options);
    }

    /**
     * Joomla! 1.5 alias
     * 
     * @access public
     * @param array $user
     * @param bool $isnew
     * @param bool $success
     * @param string $msg
     * @return null
     */
    public function onLogoutUser($user)
    {
        return $this->onUserLogout($user);
    }
}
