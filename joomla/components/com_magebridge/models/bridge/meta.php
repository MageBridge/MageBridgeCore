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
 * Main bridge class
 */
class MageBridgeModelBridgeMeta extends MageBridgeModelBridgeSegment
{
    /**
     * Singleton
     *
     * @param string $name
     *
     * @return MageBridgeModelBridgeMeta
     */
    public static function getInstance($name = null)
    {
        return parent::getInstance('MageBridgeModelBridgeMeta');
    }

    /**
     * Load the data from the bridge
     *
     * @return array
     */
    public function getResponseData()
    {
        return MageBridgeModelRegister::getInstance()
            ->getData('meta');
    }

    /**
     * Method to get the meta-data
     *
     * @return array
     */
    public function getRequestData()
    {
        // Compile the meta-data
        if (empty($this->_meta_data) || !is_array($this->_meta_data)) {
            $application = JFactory::getApplication();
            $input = $application->input;
            $user = JFactory::getUser();
            $uri = JUri::getInstance();
            $session = JFactory::getSession();
            $config = JFactory::getConfig();
            $storeHelper = MageBridgeStoreHelper::getInstance();

            $bridge = MageBridgeModelBridge::getInstance();
            $app_type = $storeHelper->getAppType();
            $app_value = $storeHelper->getAppValue();

            $arguments = [
                'api_session' => $bridge->getApiSession(),
                'api_user' => MageBridgeEncryptionHelper::encrypt(MageBridgeModelConfig::load('api_user')),
                'api_key' => MageBridgeEncryptionHelper::encrypt(MageBridgeModelConfig::load('api_key')),
                'api_url' => JUri::root() . 'component/magebridge/?controller=jsonrpc&task=call',
                'app' => $application->getClientId(), // 0 = site, 1 = admin
                'app_type' => $app_type,
                'app_value' => $app_value,
                'storeview' => MageBridgeModelConfig::load('storeview'),
                'storegroup' => MageBridgeModelConfig::load('storegroup'),
                'website' => MageBridgeModelConfig::load('website'),
                'customer_group' => MageBridgeModelConfig::load('customer_group'),
                'joomla_url' => $bridge->getJoomlaBridgeUrl(),
                'joomla_sef_url' => $bridge->getJoomlaBridgeSefUrl(),
                'joomla_sef_suffix' => (int) MageBridgeUrlHelper::hasUrlSuffix(),
                'joomla_user_email' => ($application->isSite() && !empty($user->email)) ? $user->email : null,
                'joomla_current_url' => $uri->current(),
                'modify_url' => MageBridgeModelConfig::load('modify_url'),
                'enforce_ssl' => MageBridgeModelConfig::load('enforce_ssl'),
                'has_ssl' => (int) $uri->isSSL(),
                'payment_urls' => MageBridgeModelConfig::load('payment_urls'),
                'enable_messages' => MageBridgeModelConfig::load('enable_messages'),
                'joomla_session' => session_id(),
                'joomla_conf_caching' => $config->get('caching', 60),
                'joomla_conf_lifetime' => ($config->get('lifetime', 60) * 60),
                'magento_session' => $bridge->getMageSession(),
                'magento_persistent_session' => $bridge->getMagentoPersistentSession(),
                'magento_user_allowed_save_cookie' => (isset($_COOKIE['user_allowed_save_cookie'])) ? $_COOKIE['user_allowed_save_cookie'] : null,
                'request_uri' => MageBridgeUrlHelper::getRequest(),
                'request_id' => md5(JUri::current() . serialize($input->get->getArray())),
                'post' => (!empty($_POST)) ? $_POST : null,
                'http_referer' => $bridge->getHttpReferer(),
                'http_host' => $uri->toString(['host']),
                'user_agent' => ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ''),
                'remote_addr' => ((isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ''),
                'supportkey' => MageBridgeModelConfig::load('supportkey'),
                'debug' => (int) MageBridgeModelDebug::isDebug(),
                'debug_level' => MageBridgeModelConfig::load('debug_level'),
                'debug_display_errors' => MageBridgeModelConfig::load('debug_display_errors'),
                'protocol' => MageBridgeModelConfig::load('protocol'),
                'state' => 'initializing',
                'ajax' => (int) $bridge->isAjax(),
                'disable_css' => MageBridgeHelper::getDisableCss(),
                'disable_js' => MageBridgeHelper::getDisableJs(), ];

            if (MageBridgeTemplateHelper::isMobile()) {
                $arguments['theme'] = MageBridgeModelConfig::load('mobile_magento_theme');
            } else {
                $arguments['theme'] = MageBridgeModelConfig::load('magento_theme');
            }

            foreach ($arguments as $name => $value) {
                if (is_string($value)) {
                    $arguments[$name] = MageBridgeEncryptionHelper::base64_encode($value);
                }
            }

            $this->_meta_data = $arguments;
        }

        return $this->_meta_data;
    }

    /**
     * Method to reset the meta-data
     *
     * @param null
     *
     * @return void
     */
    public function reset()
    {
        $this->_meta_data = null;
    }
}
