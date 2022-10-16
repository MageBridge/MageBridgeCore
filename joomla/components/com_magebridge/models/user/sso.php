<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge Single Sign On class
 */
class MageBridgeModelUserSSO
{
    /**
     * Instance variable
     */
    protected static $_instance = null;

    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var MageBridgeModelBridge
     */
    protected $bridge;

    /**
     * @var MageBridgeModelDebug
     */
    protected $debug;

    /**
     * Singleton
     *
     * @return MageBridgeModelUserSSO $_instance
     */
    public static function getInstance()
    {
        static $instance;

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * MageBridgeModelUser constructor.
     */
    public function __construct()
    {
        $this->app    = JFactory::getApplication();
        $this->bridge = MageBridgeModelBridge::getInstance();
        $this->debug  = MageBridgeModelDebug::getInstance();
    }

    /**
     * Method for logging in with Magento (Single Sign On)
     *
     * @param array $user
     *
     * @return bool
     */
    public function doSSOLogin($user = null)
    {
        if ($user instanceof JUser) {
            $user = \Joomla\Utilities\ArrayHelper::fromObject($user);
        }

        // Abort if the input is not valid
        if (empty($user) || (empty($user['email']) && empty($user['username']))) {
            return false;
        }

        // Get system variables
        $session = JFactory::getSession();

        // Store the current page, so we can redirect to it after SSO is done
        if ($return = $this->app->input->get('return', '', 'base64')) {
            $return = base64_decode($return);
        } else {
            $return = MageBridgeUrlHelper::current();
        }

        $session->set('magento_redirect', $return);

        // Determine the user-name
        $appName = $this->getCurrentApp();

        if ($appName == 'admin') {
            $username = $user['username'];
        } else {
            if (!empty($user['email'])) {
                $username = $user['email'];
            } else {
                $username = $user['username'];
            }
        }

        // Get the security token
        $token = JSession::getFormToken();

        // Construct the URL
        $arguments = [
            'sso=login',
            'app=' . $appName,
            'base=' . base64_encode(JUri::base()),
            'userhash=' . MageBridgeEncryptionHelper::encrypt($username),
            'token=' . $token,
        ];

        $url = $this->bridge->getMagentoBridgeUrl() . '?' . implode('&', $arguments);

        // Redirect the browser to Magento
        $this->debug->trace("SSO: Sending arguments", $arguments);
        $this->app->redirect($url);

        return true;
    }

    /**
     * Method for logging out with Magento (Single Sign On)
     *
     * @param string $username
     *
     * @return bool
     */
    public function doSSOLogout($username = null)
    {
        // Abort if the input is not valid
        if (empty($username)) {
            return false;
        }

        // Determine the application
        $appName = $this->getCurrentApp();

        // Get the security token
        $token = JSession::getFormToken();

        // Get the redirection URL
        $redirect = $this->getCurrentUrl();

        // Construct the URL
        $arguments = [
            'sso=logout',
            'app=' . $appName,
            'redirect=' . base64_encode($redirect),
            'userhash=' . MageBridgeEncryptionHelper::encrypt($username),
            'token=' . $token,
        ];

        $url = $this->bridge->getMagentoBridgeUrl() . '?' . implode('&', $arguments);

        // Redirect the browser to Magento
        $this->debug->notice("SSO: Logout of '$username' from " . $appName);
        $this->app->redirect($url);

        return true;
    }

    /**
     * Method to check the SSO-request coming back from Magento
     *
     * @param null
     *
     * @return bool
     */
    public function checkSSOLogin()
    {
        // Check the security token
        JSession::checkToken('get') or die('SSO redirect failed due to wrong token');

        // Get system variables
        $session = JFactory::getSession();

        // Get the current Magento session
        $magento_session = $this->app->input->getCmd('session');

        if (!empty($magento_session)) {
            $this->bridge->setMageSession($magento_session);
            $this->debug->notice("SSO: Magento session " . $magento_session);
        }

        // Redirect back to the original URL
        $redirect = $session->get('magento_redirect', JUri::base());

        if (empty($redirect)) {
            $redirect = MageBridgeUrlHelper::route('customer/account');
        }

        $this->debug->notice("SSO: Redirect to $redirect");
        $this->app->redirect($redirect);

        return true;
    }

    /**
     * @return string
     */
    protected function getCurrentApp()
    {
        return ($this->app->isAdmin()) ? 'admin' : 'frontend';
    }

    /**
     * @return string
     */
    protected function getCurrentUrl()
    {
        // Set the redirection URL
        if ($this->getCurrentApp() == 'admin') {
            return JUri::current();
        }

        return MageBridgeUrlHelper::current();
    }
}
