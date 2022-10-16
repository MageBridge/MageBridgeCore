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
class MageBridgeModelBridge
{
    /**
     * Instance variable
     */
    protected static $_instance = null;

    /**
     * API state
     */
    private $_api_state = '';

    /**
     * API extra
     */
    private $_api_extra = '';

    /**
     * HTTP Referer
     */
    private $_http_referer = '';

    /**
     * Singleton
     *
     * @return MageBridgeModelBridge
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Method to return the Joomla!/MageBridge System URL
     *
     * @param string $request
     * @param int    $forceSsl
     *
     * @return string
     */
    public function getJoomlaBridgeUrl($request = null, $forceSsl = null)
    {
        // Get important variables
        $application = JFactory::getApplication();
        $uri = JUri::getInstance();

        // Catch the backend URLs
        if ($application->isAdmin()) {
            $baseUri = $uri->toString([
                'scheme',
                'host',
                'port', ]);

            return $baseUri . '/administrator/index.php?option=com_magebridge&view=root&format=raw&request=' . $request;
        } else {
            // Return the MageBridge Root if the request is empty
            if (empty($request)) {
                $root_item = MageBridgeUrlHelper::getRootItem();
                $root_item_id = ($root_item && $root_item->id > 0) ? $root_item->id : JFactory::getApplication()->input->getInt('Itemid');

                // Allow for experimental support for MijoSEF and sh404SEF
                if (self::sh404sef() || self::mijosef()) {
                    $route = 'index.php?option=com_magebridge&view=root&Itemid=' . $root_item_id . '&request=';
                } else {
                    $route = JRoute::_('index.php?option=com_magebridge&view=root&Itemid=' . $root_item_id, false);
                }
            } else {
                $route = MageBridgeUrlHelper::route($request, false);
            }

            // Remove the html-suffix for Magento
            $route = preg_replace('/\.html$/', '', $route);

            // Add a / as suffix
            if (!strstr($route, '?') && !preg_match('/\/$/', $route)) {
                $route .= '/';
            }

            // Prepend the hostname
            if (!preg_match('/^(http|https):\/\//', $route)) {
                $url = JUri::getInstance()
                    ->toString(['scheme', 'host', 'port']);
                if (!preg_match('/^\//', $route) && !preg_match('/\/$/', $url)) {
                    $route = $url . '/' . $route;
                } else {
                    $route = $url . $route;
                }
            }

            return $route;
        }
    }

    /**
     * Method to return the Joomla!/MageBridge SEF URL
     *
     * @param string $request
     * @param int    $forceSsl
     *
     * @return string
     */
    public function getJoomlaBridgeSefUrl($request = null, $forceSsl = null)
    {
        return self::getJoomlaBridgeUrl($request, $forceSsl);
    }

    /**
     * Method to return the Magento/MageBridge URL
     *
     * @return string
     */
    public function getMagentoBridgeUrl()
    {
        $url = $this->getMagentoUrl();
        if (!empty($url)) {
            return $url . 'magebridge.php';
        } else {
            return null;
        }
    }

    /**
     * Method to return the Magento Admin Panel URL
     *
     * @param string $path
     *
     * @return string
     */
    public function getMagentoAdminUrl($path = null)
    {
        $url = MageBridgeModelBridge::getMagentoUrl();
        if (!empty($url)) {
            $path = preg_replace('/^\//', '', $path);
            $url = $url . 'index.php/' . MageBridgeModelConfig::load('backend') . '/' . $path;

            return $url;
        } else {
            return null;
        }
    }

    /**
     * Magento default URL
     *
     * @return string
     */
    public function getMagentoUrl()
    {
        $url = MageBridgeModelConfig::load('url');
        if (!empty($url)) {
            return preg_replace('/\/\/$/', '/', MageBridgeModelConfig::load('url'));
        } else {
            return null;
        }
    }

    /**
     * Method to handle Magento events
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setEvents($data = null)
    {
        return MageBridgeModelBridgeEvents::getInstance()
            ->setEvents($data);
    }

    /**
     * Method to set the breadcrumbs
     *
     * @return mixed
     */
    public function setBreadcrumbs()
    {
        return MageBridgeModelBridgeBreadcrumbs::getInstance()
            ->setBreadcrumbs();
    }

    /**
     * Method to get the headers
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return MageBridgeModelBridgeHeaders::getInstance()
            ->getResponseData();
    }

    /**
     * Method to set the headers
     *
     * @param string $type
     *
     * @return mixed
     */
    public function setHeaders($type = null)
    {
        return MageBridgeModelBridgeHeaders::getInstance()
            ->setHeaders($type);
    }

    /**
     * Method to get a segment by its ID
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getSegment($id = null)
    {
        return MageBridgeModelRegister::getInstance()
            ->getById($id);
    }

    /**
     * Method to get a segment by its ID
     *
     * @param string $id
     *
     * @return array
     */
    public function getSegmentData($id = null)
    {
        return MageBridgeModelRegister::getInstance()
            ->getDataById($id);
    }

    /**
     * Method to get the category tree
     *
     * @param array $arguments
     *
     * @return array
     */
    public function getCatalogTree($arguments = null)
    {
        return $this->getAPI('magebridge_category.tree', $arguments);
    }

    /**
     * Method to get the products by tag
     *
     * @param array $tags
     *
     * @return array
     */
    public function getProductsByTags($tags = [])
    {
        return $this->getAPI('magebridge_tag.list', $tags);
    }

    /**
     * Method to get a specific API resource
     *
     * @param string $resource
     * @param mixed  $arguments
     * @param string $id
     *
     * @return array
     */
    public function getAPI($resource = null, $arguments = null, $id = null)
    {
        return MageBridgeModelRegister::getInstance()
            ->getData('api', $resource, $arguments, $id);
    }

    /**
     * Method to get the Magento debug-messages
     *
     * @return array
     */
    public function getDebug()
    {
        return MageBridgeModelRegister::getInstance()
            ->getData('debug');
    }

    /**
     * Method to return a specific block
     *
     * @param string $block_name
     * @param mixed  $arguments
     *
     * @return array
     */
    public function getBlock($block_name, $arguments = null)
    {
        return MageBridgeModelBridgeBlock::getInstance()
            ->getBlock($block_name, $arguments);
    }

    /**
     * Method to return a specific widget
     *
     * @param string $widget_name
     * @param mixed  $arguments
     *
     * @return array
     */
    public function getWidget($widget_name, $arguments = null)
    {
        return MageBridgeModelBridgeWidget::getInstance()
            ->getWidget($widget_name, $arguments);
    }

    /**
     * Method to add something to the bridge register
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function register($type = null, $name = null, $arguments = null)
    {
        return MageBridgeModelRegister::getInstance()
            ->add($type, $name, $arguments);
    }

    /**
     * Method to collect the data from the proxy
     *
     * @return array
     */
    public function build()
    {
        $application = JFactory::getApplication();
        $register = MageBridgeModelRegister::getInstance();
        $proxy = MageBridgeModelProxy::getInstance();
        $proxy->reset();

        // Initialize the register if possible
        MageBridgeRegisterHelper::preload();

        // Load cached data into the register
        $register->loadCache();

        // Exit immediately if the bridge is set offline
        if ($this->isOffline()) {
            MageBridgeModelDebug::getInstance()
                ->error('Bridge is set offline');

            return $register->getRegister();
        }

        // Exit immediately if the api_user and api_key are not configured yet
        if (strlen(MageBridgeModelConfig::load('api_user')) == 0 && strlen(MageBridgeModelConfig::load('api_key')) == 0) {
            MageBridgeModelDebug::getInstance()
                ->error('No API user or no API key');

            return $register->getRegister();
        }

        // Exit if the proxy doesn't work (after 10 proxy-requests)
        if ($proxy->getCount() > 10) {
            MageBridgeModelDebug::getInstance()
                ->notice('Too many requests');

            return $register->getRegister();
        }

        // Only continue if we have no data yet, or when we're dealing with a new (or empty) register
        if (count($register->getPendingRegister()) > 0) {
            // Allow modification before we build the bridge
            MageBridgeModelDebug::beforeBuild();

            //MageBridgeModelDebug::getInstance()->trace('Backtrace', debug_backtrace());
            //MageBridgeModelDebug::getInstance()->trace('Declared classes', get_declared_classes());

            MageBridgeModelDebug::getInstance()
                ->notice('Building bridge for ' . count($register->getPendingRegister()) . ' items');

            // Extra debugging options
            if (!defined('MAGEBRIDGE_MODULEHELPER_OVERRIDE')) {
                MageBridgeModelDebug::getInstance()
                    ->warning('Modulehelper override not active');
            }

            foreach ($register->getPendingRegister() as $segment) {
                switch ($segment['type']) {
                    case 'api':
                        MageBridgeModelDebug::getInstance()
                            ->notice('Pending Segment API resource: ' . $segment['name']);
                        break;
                    case 'block':
                        MageBridgeModelDebug::getInstance()
                            ->notice('Pending Segment block: ' . $segment['name']);
                        break;
                    case 'widget':
                        MageBridgeModelDebug::getInstance()
                            ->notice('Pending Segment widget: ' . $segment['name']);
                        break;
                    default:
                        $name = (isset($segment['name'])) ? $segment['name'] : null;
                        $type = (isset($segment['type'])) ? $segment['type'] : null;
                        if (empty($name)) {
                            MageBridgeModelDebug::getInstance()
                                ->notice('Pending Segment: ' . $type);
                        } else {
                            MageBridgeModelDebug::getInstance()
                                ->notice('Pending Segment: ' . $type . '/' . $name);
                        }
                        break;
                }
            }

            // Initialize proxy-settings
            if ($application->isSite() && JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
                $proxy->setAllowRedirects(false);
            } else {
                if ($application->isAdmin() && (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge' || JFactory::getApplication()->input->getCmd('view') != 'root')) {
                    $proxy->setAllowRedirects(false);
                }
            }

            // Allow others to hook into this event
            $this->beforeBuild();

            // Get the proxy and push the registry through the proxy
            //MageBridgeModelDebug::getInstance()->trace( 'Register', $register->getPendingRegister());
            MageBridgeModelDebug::getInstance()
                ->notice('HTTP Referer: ' . $this->getHttpReferer());

            // Build the bridge through the proxy
            $data = $proxy->build($register->getPendingRegister());
            //MageBridgeModelDebug::getInstance()->trace( 'Bridge-data', $data );

            // Set the API-state flag
            $this->setApiState($proxy->getState());

            // Exit, if the result is empty
            if (empty($data) || !is_array($data)) {
                return $register->getRegister();
            }

            // Merge the new data with the already existing register
            $register->merge($data);

            if (isset($data['meta']['data']['state'])) {
                $this->setApiState($data['meta']['data']['state']);
            }

            if (isset($data['meta']['data']['extra'])) {
                $this->setApiExtra($data['meta']['data']['extra']);
            }

            if (isset($data['meta']['data']['api_session'])) {
                $this->setApiSession($data['meta']['data']['api_session']);
            }

            if (isset($data['meta']['data']['magento_config'])) {
                $this->setSessionData($data['meta']['data']['magento_config']);
            }

            // Allow others to hook into this event
            $this->afterBuild();

            // Fire all Magento events defined in the incoming bridge-data
            $this->setEvents();
        }

        //MageBridgeModelDebug::getInstance()->trace('Register data', $register->getRegister());
        //MageBridgeModelDebug::getInstance()->trace('Function stack', xdebug_get_function_stack());

        MageBridgeModelDebug::getInstance()
            ->getBridgeData();

        return $register->getRegister();
    }

    /**
     * Method to do things before building the bridge
     */
    public function beforeBuild()
    {
        jimport('joomla.plugin.helper');
        JPluginHelper::importPlugin('magebridge');
        $application = JFactory::getApplication();
        $application->triggerEvent('onBeforeBuildMageBridge');
    }

    /**
     * Method to do things after building the bridge
     */
    public function afterBuild()
    {
        jimport('joomla.plugin.helper');
        JPluginHelper::importPlugin('magebridge');
        $application = JFactory::getApplication();
        $application->triggerEvent('onAfterBuildMageBridge');
    }

    /**
     * Helper-method to get the HTTP Referer to send to Magento
     *
     * @return string
     */
    public function storeHttpReferer()
    {
        // Singleton method
        static $stored = false;
        if ($stored == true) {
            return;
        }
        $stored = true;

        // Fetch the current referer
        $referer = $this->getHttpReferer();
        if (!empty($referer)) {
            $session = JFactory::getSession();
            $session->set('magebridge.http_referer', $referer);

            $this->_http_referer = $referer;
        }

        return;
    }

    /**
     * Helper-method to get the HTTP Referer to send to Magento
     *
     * @return string
     */
    public function getHttpReferer()
    {
        // If this is a non-MageBridge page, use it
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            $referer = JUri::getInstance()
                ->toString();

        // If the referer is set on the URL, use it also
        } elseif (preg_match('/\/(uenc|referer)\/([a-zA-Z0-9\,\_\-]+)/', JUri::current(), $match)) {
            $referer = MageBridgeEncryptionHelper::base64_decode($match[2]);

        // If this is the MageBridge page checkout/cart/updatePost, return to the checkout
        } else {
            if (preg_match('/\/checkout\/cart\/([a-zA-Z0-9]+)Post/', JUri::current()) == true) {
                $referer = MageBridgeUrlHelper::route('checkout/cart');

            // If this is a MageBridge page, use it only if its not a customer-page, or homepage
            } else {
                if (preg_match('/\/customer\/account\//', JUri::current()) == false && preg_match('/\/persistent\/index/', JUri::current()) == false && preg_match('/\/review\/product\/post/', JUri::current()) == false && preg_match('/\/remove\/item/', JUri::current()) == false && preg_match('/\/newsletter\/subscriber/', JUri::current()) == false && preg_match('/\/checkout\/cart/', JUri::current()) == false && $this->isAjax() == false && JUri::current() != $this->getJoomlaBridgeUrl()) {
                    $referer = JUri::getInstance()
                        ->toString();
                }
            }
        }

        // Load the stored referer from the session
        if (empty($referer)) {
            $session = JFactory::getSession();
            $referer = $session->get('magebridge.http_referer');
        }

        // Use the default referer
        if (empty($this->_http_referer)) {
            if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != JUri::current()) {
                $referer = $_SERVER['HTTP_REFERER'];
            }
        }

        $this->_http_referer = $referer;

        return $this->_http_referer;
    }

    /**
     * Helper-method to set the HTTP Referer to send to Magento
     *
     * @param string $httpReferer
     * @param string $type
     *
     * @return void
     */
    public function setHttpReferer($httpReferer = null, $type = 'magento')
    {
        if ($type == 'magento') {
            $httpReferer = $this->getJoomlaBridgeSefUrl($httpReferer);
        }

        $this->_http_referer = $httpReferer;
    }

    /**
     * Helper-method to return the API state
     *
     * @return string
     */
    public function getApiState()
    {
        return $this->_api_state;
    }

    /**
     * Helper-method to set the API state
     *
     * @param string $api_state
     *
     * @return void
     */
    public function setApiState($api_state = null)
    {
        $this->_api_state = $api_state;
    }

    /**
     * Helper-method to return the API extra data
     *
     * @return string
     */
    public function getApiExtra()
    {
        return $this->_api_extra;
    }

    /**
     * Helper-method to set the API extra data
     *
     * @param string $api_extra
     *
     * @return void
     */
    public function setApiExtra($api_extra = null)
    {
        $this->_api_extra = $api_extra;
    }

    /**
     *
     * Helper-method to return the API session
     *
     * @return string
     */
    public function getApiSession()
    {
        $session = JFactory::getSession();

        return $session->get('api_session');
    }

    /**
     * Helper-method to set the API session
     *
     * @param string $api_session
     *
     * @return string
     */
    public function setApiSession($api_session = null)
    {
        $session = JFactory::getSession();
        if (!empty($api_session) && preg_match('/^([a-zA-Z0-9]{12,46})$/', $api_session)) {
            $session->set('api_session', $api_session);
        }

        return $session->get('api_session');
    }

    /**
     * Helper-method to return the Magento configuration
     *
     * @deprecated Use getSessionData() instead
     *
     * @param string  $name
     * @param boolean $allow_cache
     *
     * @return mixed
     */
    public function getMageConfig($name = null, $allow_cache = true)
    {
        return $this->getSessionData($name, $allow_cache);
    }

    /**
     * Helper-method to return the Magento configuration
     *
     * @param string  $name
     * @param boolean $allow_cache
     *
     * @return mixed
     */
    public function getSessionData($name = null, $allow_cache = true)
    {
        // Do not use this function, when Joomla! has not routed the request yet
        $option = JFactory::getApplication()->input->getCmd('option');
        if (empty($option)) {
            return null;
        }

        // Fetch the current register
        $data = MageBridgeModelRegister::getInstance()
            ->getRegister();
        if (isset($data['meta']['data']['magento_config'][$name])) {
            return $data['meta']['data']['magento_config'][$name];
        }

        if ($allow_cache == false) {
            return null;
        }

        $session = JFactory::getSession();
        $data = $session->get('magento_config');
        if (!empty($name)) {
            if (isset($data[$name])) {
                return $data[$name];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Helper-method to set a specific value in MageBridge session
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function addSessionData($name, $value)
    {
        $session = JFactory::getSession();
        $data = $session->get('magento_config');

        if (!is_array($data)) {
            $data = [];
        }

        $data[$name] = $value;
        $session->set('magento_config', $data);
    }

    /**
     * Helper-method to set the Magento configuration
     *
     * @param array $data
     *
     * @return mixed
     */
    public function setSessionData($data = [])
    {
        $session = JFactory::getSession();

        if (!empty($data) && is_array($data)) {
            $session->set('magento_config', $data);
        }

        return $session->get('magento_config');
    }

    /**
     * Helper-method to return the Magento session
     *
     * @return string
     */
    public function getMageSession()
    {
        $session_value = JFactory::getApplication()->input->cookie->getCmd('frontend');

        if (empty($session_value)) {
            $session_value = JFactory::getSession()
                ->get('magebridge.cookie.frontend');
        }

        return $session_value;
    }

    /**
     * Helper-method to return the Magento persistent session
     *
     * @return string
     */
    public function getMagentoPersistentSession()
    {
        $session = JFactory::getSession();

        return $session->get('magento_persistent_session');
    }

    /**
     * Helper-method to set the Magento session
     *
     * @param string $mage_session
     *
     * @return string
     */
    public function setMageSession($mage_session = null)
    {
        if (!headers_sent()) {
            setcookie('frontend', $mage_session, 0, '/', '.' . JUri::getInstance()
                    ->toString(['host']));
        }
        JFactory::getSession()
            ->set('magebridge.cookie.frontend', $mage_session);

        return JFactory::getApplication()->input->cookie->getCmd('frontend');
    }

    /**
     * Method to get the meta-request data
     *
     * @return array
     */
    public function getMeta()
    {
        return MageBridgeModelBridgeMeta::getInstance()
            ->getRequestData();
    }

    /**
     * Helper method to check if sh404SEF is installed
     *
     * @return bool
     */
    public static function sh404sef()
    {
        $class = JPATH_ADMINISTRATOR . '/components/com_sh404sef/sh404sef.class.php';

        if (!is_file($class) || !is_readable($class)) {
            return false;
        }

        jimport('joomla.application.component.helper');

        if (JComponentHelper::isEnabled('com_sh404sef') == false) {
            return false;
        }

        include_once($class);

        if (!class_exists('SEFConfig')) {
            return false;
        }

        $sefConfig = new SEFConfig();
        if ($sefConfig->Enabled == 0) {
            return false;
        }

        return true;
    }

    /**
     * Helper method to check if MijoSEF is installed
     *
     * @return bool
     */
    public static function mijosef()
    {
        $class = JPATH_ADMINISTRATOR . '/components/com_mijosef/library/mijosef.php';
        if (!is_file($class) || !is_readable($class)) {
            return false;
        }

        jimport('joomla.application.component.helper');

        if (JComponentHelper::isEnabled('com_mijosef') == false) {
            return false;
        }

        include_once($class);

        if (!class_exists('Mijosef')) {
            return false;
        }

        $app = JFactory::getApplication();
        $config = JFactory::getConfig();

        if ($app->isAdmin()) {
            return false;
        }

        if ($config->get('sef') == false) {
            return false;
        }

        $MijosefConfig = Mijosef::getConfig();

        if ($MijosefConfig->mode == 0) {
            return false;
        }

        if (!JPluginHelper::isEnabled('system', 'mijosef')) {
            return false;
        }

        return true;
    }

    /**
     * Helper method to check if SEF is enabled
     *
     * @return bool
     */
    public function sef()
    {
        $application = JFactory::getApplication();
        $router = $application->getRouter();

        if ($router->getMode() == JROUTER_MODE_RAW) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Method to determine whether to enable SSL or not
     *
     * @return bool
     */
    public function enableSSL()
    {
        $enforce_ssl = MageBridgeModelConfig::load('enforce_ssl');

        if (JFactory::getApplication()->input->getCmd('option') == 'com_magebridge' && $enforce_ssl > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the current page is based on the MageBridge component
     *
     * @return bool
     */
    public function isShopPage()
    {
        if (JFactory::getApplication()->input->getCmd('option') == 'com_magebridge') {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the bridge is currently offline
     *
     * @return bool
     */
    public function isOffline()
    {
        // Set the bridge offline by using a flag
        if (JFactory::getApplication()->input->getInt('offline', 0) == 2) {
            return false;
        }

        // Set the bridge offline by using a flag
        if (JFactory::getApplication()->input->getInt('offline', 0) == 1) {
            return true;
        }

        // Set the bridge offline when configured, except for specific IPs
        if (MageBridgeModelConfig::load('offline') == 1) {
            $ips = MageBridgeModelConfig::load('offline_exclude_ip');

            if (!empty($ips)) {
                $ips = explode(',', trim($ips));

                if (in_array($_SERVER['REMOTE_ADDR'], $ips)) {
                    return false;
                }
            }

            return true;
        }

        // Set the bridge when editing an article in the frontend
        $option = JFactory::getApplication()->input->getCmd('option');
        $view = JFactory::getApplication()->input->getCmd('view');
        $layout = JFactory::getApplication()->input->getCmd('layout');

        if ($option == 'com_content' && $view == 'form' && $layout == 'edit') {
            return true;
        } elseif (in_array($option, ['com_scriptmerge'])) {
            return true;
        }

        return false;
    }

    /**
     * Method to determine whether the current request is an AJAX request
     *
     * @return bool
     */
    public function isAjax()
    {
        if (in_array(JFactory::getApplication()->input->getCmd('format'), ['xml', 'json', 'ajax'])) {
            return true;
        }

        // Things to consider: Backend Lightbox-effect, frontend AJAX-lazyloading
        $check_xrequestedwith = true;

        if (JFactory::getApplication()
                ->isSite() == false
        ) {
            $check_xrequestedwith = false;
        } else {
            if (JFactory::getApplication()->input->getCmd('view') == 'ajax') {
                $check_xrequestedwith = false;
            }
        }

        // Detect the X-Requested-With headers
        if ($check_xrequestedwith) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                if (isset($headers['X-Requested-With']) && strtolower($headers['X-Requested-With']) == 'xmlhttprequest') {
                    return true;
                }
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    return true;
                }
            }
        }

        // Simple check to see if AJAX is mentioned in the current Magento URL
        $current_url = MageBridgeUrlHelper::getRequest();

        if (stristr($current_url, 'ajax')) {
            return true;
        }

        return false;
    }
}
