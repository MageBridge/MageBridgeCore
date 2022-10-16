<?php
/**
 * Joomla! MageBridge - Authentication plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Authentication Plugin
 */
class PlgAuthenticationMageBridge extends JPlugin
{
    // MageBridge constants
    public const MAGEBRIDGE_AUTHENTICATION_FAILURE = 0;
    public const MAGEBRIDGE_AUTHENTICATION_SUCCESS = 1;
    public const MAGEBRIDGE_AUTHENTICATION_ERROR = 2;

    /**
     * Constructor
     *
     * @access public
     *
     * @param object $subject
     * @param array  $config
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
     * Handle the event that is generated when an user tries to login
     *
     * @access public
     *
     * @param array  $credentials
     * @param array  $options
     * @param object $response
     *
     * @return bool
     */
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        // Do not continue if not available
        if ($this->isEnabled() == false) {
            return false;
        }

        // Skip if there is no password or username set
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return false;
        }

        MageBridgeModelDebug::getInstance()->notice('Authentication plugin: onAuthenticate called');

        if (JFactory::getApplication()->isSite() == false) {
            // Check if authentication is enabled for the backend
            if ($this->getParam('enable_auth_backend') != 1) {
                return false;
            }

            $application_name = 'admin';
        } else {
            // Check if authentication is enabled for the frontend
            if ($this->getParam('enable_auth_frontend') != 1) {
                return false;
            }

            $application_name = 'site';
        }

        // Lookup the email instead
        if ($this->params->get('lookup_email', 0) == 1) {
            $db = JFactory::getDbo();
            $db->setQuery("SELECT email FROM #__users WHERE username = " . $db->Quote($credentials['username']));
            $email = $db->loadResult();

            if (!empty($email)) {
                $credentials['username'] = $email;
            }
        }

        // Send credentials to the User-model (which forwards authentication through the bridge)
        $result = $this->getUser()->authenticate($credentials['username'], $credentials['password'], $application_name);
        MageBridgeModelDebug::getInstance()->trace('Authentication plugin: onAuthenticate data', $result);

        // Abort if the result is empty
        if (empty($result)) {
            MageBridgeModelDebug::getInstance()->notice('Authentication plugin: onAuthenticate returns empty');
            $response->status = (defined('JAuthentication::STATUS_FAILURE')) ? JAuthentication::STATUS_FAILURE : JAUTHENTICATE_STATUS_FAILURE;
            $response->error_message = 'Failed to authenticate';

            return false;
        }

        // Abort if the result contains an unknown state
        if (empty($result['state']) || $result['state'] != self::MAGEBRIDGE_AUTHENTICATION_SUCCESS) {
            MageBridgeModelDebug::getInstance()->notice('Authentication plugin: onAuthenticate returns false');
            $response->status = (defined('JAuthentication::STATUS_FAILURE')) ? JAuthentication::STATUS_FAILURE : JAUTHENTICATE_STATUS_FAILURE;
            $response->error_message = 'Failed to authenticate';

            return false;
        }

        // So far so good: instead of using the Magento user-details, we fetch the Joomla! data
        $user = $this->getUser()->loadByEmail($result['email']);

        // Compile the plugin-response
        $response->type = 'MageBridge';
        $response->status = (defined('JAuthentication::STATUS_SUCCESS')) ? JAuthentication::STATUS_SUCCESS : JAUTHENTICATE_STATUS_SUCCESS;
        $response->error_message = '';
        $response->email = $result['email'];
        $response->application = $application_name;
        $response->hash = $result['hash'];

        if (!empty($user)) {
            $response->username = $user->username;
            $response->fullname = $user->name;
        } else {
            $response->username = $result['username'];
            $response->fullname = $result['fullname'];
        }

        return true;
    }

    /**
     * Joomla! 1.5 alias
     *
     * @access public
     *
     * @param array  $credentials
     * @param array  $options
     * @param object $response
     *
     * @return bool
     */
    public function onAuthenticate($credentials, $options, &$response)
    {
        return $this->onUserAuthenticate($credentials, $options, $response);
    }

    /**
     * Return a MageBridge configuration parameter
     *
     * @access private
     *
     * @param string $name
     *
     * @return mixed $value
     */
    private function getParam($name = null)
    {
        return MageBridgeModelConfig::load($name);
    }

    /**
     * Return a MageBridge user-class
     *
     * @access private
     *
     * @param null
     *
     * @return mixed $value
     */
    private function getUser()
    {
        return MageBridge::getUser();
    }

    /**
     * Return whether MageBridge is available or not
     *
     * @access private
     *
     * @param null
     *
     * @return mixed $value
     */
    private function isEnabled()
    {
        if (class_exists('MageBridgeModelBridge')) {
            if (MageBridgeModelBridge::getInstance()->isOffline()) {
                return false;
            } elseif (MageBridge::isApiPage() == true) {
                return false;
            }

            return true;
        }

        return false;
    }
}
