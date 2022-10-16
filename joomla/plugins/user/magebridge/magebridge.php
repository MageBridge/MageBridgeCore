<?php
/**
 * Joomla! MageBridge - User plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com/
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
class PlgUserMageBridge extends MageBridgePlugin
{
    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var MageBridgeModelUser
     */
    protected $userModel;

    /**
     * @var MageBridgePluginHelper
     */
    protected $pluginHelper;

    /*
     * Temporary container for original user-data
     */
    private $original_data = [];

    /**
     * Initialization function
     */
    protected function initialize()
    {
        $this->userModel    = MageBridge::getUser();
        $this->debug        = MageBridgeModelDebug::getInstance();
        $this->pluginHelper = MageBridgePluginHelper::getInstance();
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
        $this->debug->notice("onUserAfterDelete::userDelete on user " . $user['username']);

        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserAfterDelete') == false) {
            return;
        }

        // Use the delete-function in the bridge
        $this->userModel->delete($user);

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
        if (isset($oldUser['id'])) {
            $id = $oldUser['id'];
        } else {
            $id = 0;
        }

        $this->original_data[$id] = ['email' => $oldUser['email']];

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
        if (isset($user['id'])) {
            $id = $user['id'];
        } else {
            $id = 0;
        }

        if (isset($this->original_data[$id])) {
            $user['original_data'] = $this->original_data[$id];
        }

        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserAfterSave') == false) {
            return false;
        }

        // Copy the username to the email address (if this is configured)
        if ($this->app->isSite() == true && $this->getConfigValue('username_from_email') == 1 && $user['username'] != $user['email']) {
            $this->debug->notice("onUserAfterSave::bind on user " . $user['username']);

            // Load the right JUser object
            $data   = ['username' => $user['email']];
            $object = JFactory::getUser($user['id']);

            // Check whether user-syncing is allowed for this user
            if ($this->userModel->allowSynchronization($object, 'save') == true) {
                // Change the record in the database
                $object->bind($data);
                $object->save();

                // Bind this new user-object into the session
                $session      = JFactory::getSession();
                $session_user = $session->get('user');

                if ($session_user->id == $user['id']) {
                    $session_user->username = $user['email'];
                }
            }
        }

        // Synchronize this user-record with Magento
        if ($this->getConfigValue('enable_usersync') == 1) {
            $this->debug->notice("onUserAfterSave::usersync on user " . $user['username']);

            // Sync this user-record with the bridge
            $this->userModel->synchronize($user);
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
    public function onUserLogin($user = null, $options = [])
    {
        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserLogin', $options) == false) {
            return true;
        }

        // Synchronize this user-record with Magento
        if ($this->getConfigValue('enable_usersync') == 1 && $this->app->isSite()) {
            $user['id'] = JFactory::getUser()->id;
            $user       = $this->userModel->synchronize($user);
        }

        // Perform a login
        $this->userModel->login($user['email']);

        return true;
    }

    /**
     * Event onUserAfterLogin
     *
     * @param array $options
     *
     * @return bool
     */
    public function onUserAfterLogin($options = [])
    {
        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserLogin', $options) === false) {
            //return true;
        }

        // Check whether SSO is enabled
        if ($this->getConfigValue('enable_sso') != 1) {
            return true;
        }

        $user = $options['user'];

        if ($this->app->isSite() && $this->getConfigValue('enable_auth_frontend') == 1) {
            MageBridgeModelUserSSO::getInstance()->doSSOLogin($user);
        }

        if ($this->app->isAdmin() && $this->getConfigValue('enable_auth_backend') == 1) {
            MageBridgeModelUserSSO::getInstance()->doSSOLogin($user);
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
    public function onUserLogout($user = null, $options = [])
    {
        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserLogout', $options) == false) {
            return true;
        }

        // Get system variables
        $session = JFactory::getSession();
        $uri     = JUri::getInstance();

        $bridge   = MageBridgeModelBridge::getInstance();
        $register = MageBridgeModelRegister::getInstance();

        // Remove the Magento cookies
        $cookies = ['frontend', 'user_allowed_save_cookie', 'persistent_shopping_cart', 'mb_postlogin'];

        foreach ($cookies as $cookie) {
            if (isset($_COOKIE[$cookie])) {
                unset($_COOKIE[$cookie]);
            }

            setcookie($cookie, '', time() - 1000);
            setcookie($cookie, '', time() - 1000, '/');
            setcookie($cookie, '', time() - 1000, '/', '.' . $uri->toString(['host']));

            $this->app->input->set($cookie, null, 'cookie');
            $session->set('magebridge.cookie.' . $cookie, null);
        }

        // Set the Magento session to null
        $session->set('magento_session', null);

        // Build the bridge and fetch the result
        if ($this->getConfigValue('link_to_magento') == 0) {
            $arguments = ['disable_events' => 1];
            $id        = $register->add('logout', null, $arguments);
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
    public function onUserAfterLogout($options = [])
    {
        // Check if we can run this event or not
        if ($this->pluginHelper->isEventAllowed('onUserLogout', $options) == false) {
            return true;
        }

        // Check whether SSO is enabled
        if ($this->getConfigValue('enable_sso') !== 1 || !isset($options['username'])) {
            return true;
        }

        if ($this->app->isSite() && $this->getConfigValue('enable_auth_frontend') == 1) {
            MageBridgeModelUserSSO::getInstance()->doSSOLogout($options['username']);
        }

        if ($this->app->isAdmin() && $this->getConfigValue('enable_auth_backend') == 1) {
            MageBridgeModelUserSSO::getInstance()->doSSOLogout($options['username']);
        }

        return true;
    }
}
