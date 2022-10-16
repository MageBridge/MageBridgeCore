<?php

/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

/**
 * MageBridge model serving as main bridge-resources which primarily handles the Magento configuration
 */
class Yireo_MageBridge_Model_Core
{
    /**
     * Bridge-request
     */
    protected $_request = [];

    /**
     * Bridge-request
     */
    protected $_response = [];

    /**
     * Meta-data
     */
    protected $_meta = [];

    /**
     * Magento configuration
     */
    protected $_mage_config = [];

    /**
     * System events
     */
    protected $_events = [];

    /**
     * Flag to enable event forwarding
     */
    protected $_enable_events = true;

    /**
     * Flag for forcing preoutput
     */
    protected $_force_preoutput = false;

    /**
     * Initialize the bridge-core
     *
     * @param array $meta
     * @param array $request
     *
     * @return bool
     */
    public function init($meta = null, $request = null)
    {
        // Set meta and request
        $this->_meta = $meta;
        $this->_request = $request;

        // Fill the response with the current request
        $this->setResponseData($request);

        // Decrypt everything that needs decrypting
        $this->_meta['api_user'] = $this->getMetaData('api_user');
        $this->_meta['api_key'] = $this->getMetaData('api_key');

        //Mage::getSingleton('magebridge/debug')->trace('Dump of meta', $this->_meta);
        //Mage::getSingleton('magebridge/debug')->trace('Dump of request', $this->_request);
        //Mage::getSingleton('magebridge/debug')->trace('Dump of GET', $_GET);
        //Mage::getSingleton('magebridge/debug')->trace('HTTP referer', (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null);

        // Overwrite the default error-handling by routing all magebridge/debug
        set_error_handler('Yireo_MageBridge_ErrorHandler');
        set_exception_handler('Yireo_MageBridge_ExceptionHandler');

        // Try to initialize the session
        $this->reinitializeSession();

        // Optionally disable form_key security
        $this->disableFormKey();

        // Set the magebridge-URLs
        $this->setConfig();

        // Handle post logins
        $this->postLoginUser();

        // Handle persistent logins
        $this->handlePersistentLogins();

        // Set the current store and URLs
        $this->setCurrentStore();
        $this->rewriteNonSefCategoryUrls();
        $this->setContinueShoppingToPreviousUrl();
        $this->setCustomerRedirectUrl();
        $this->redirectContinueShoppingToPreviousUrl();

        //$session = Mage::getSingleton('checkout/session');
        //Mage::getSingleton('magebridge/debug')->notice('Quote: '.$session->getQuoteId());

        return true;
    }

    /**
     * @return bool
     */
    protected function reinitializeSession()
    {
        try {
            $session = Mage::getSingleton('core/session', ['name' => 'frontend']);
            $session->start();
            Mage::getSingleton('magebridge/debug')->notice('Core session started: ' . $session->getSessionId());
        } catch (Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Unable to instantiate core/session: ' . $e->getMessage());
            $_COOKIE = [];
            $_SESSION = [];
            return false;
        }

        return true;
    }

    /**
     * Disable the form key if configured
     *
     * @return bool
     */
    protected function disableFormKey()
    {
        $disableFormKey = (bool) Mage::getStoreConfig('magebridge/settings/disable_form_key');

        if ($disableFormKey === false && !strstr($this->getRequestUrl(), 'checkout/cart/add')) {
            return false;
        }

        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $request = Mage::app()->getRequest();
        $request->setPathInfo(preg_replace('/\/form_key\/([^\/]+)/', '', $request->getPathInfo()));
        $request->setRequestUri(preg_replace('/\/form_key\/([^\/]+)/', '', $request->getRequestUri()));
        $request->setParam('form_key', $formKey);

        Mage::getSingleton('magebridge/debug')->notice('Spoofing form key: ' . $formKey);
        return true;
    }

    /**
     * Post-login a Joomla! user
     *
     * @return bool
     */
    protected function postLoginUser()
    {
        $joomlaUserEmail = $this->getMetaData('joomla_user_email');
        if (empty($joomlaUserEmail)) {
            return false;
        }

        if (Mage::getModel('customer/session')->isLoggedIn()) {
            return false;
        }

        $data = [
            'email' => $joomlaUserEmail,
            'application' => 'site',
            'disable_events' => true,
        ];

        Mage::getModel('magebridge/user_api')->login($data);
        return true;
    }

    /**
     * Workaround for persistent logins
     *
     * @return bool
     */
    protected function handlePersistentLogins()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return false;
        }

        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (!array_key_exists('Mage_Persistent', $modules)) {
            return false;
        }

        $persistentHelper = Mage::helper('persistent/session');
        $persistentCustomerId = (int)$persistentHelper->getSession()->getCustomerId();
        if (empty($persistentCustomerId)) {
            return false;
        }

        $customer = Mage::getModel('customer/customer')->load($persistentCustomerId);
        if (!$customer->getId() > 0) {
            return false;
        }

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer)->renewSession();
        return true;
    }

    /**
     * Rewrite non SEF URLs
     */
    protected function rewriteNonSefUrls()
    {
        $this->rewriteNonSefCategoryUrls();
        $this->rewriteNonSefProductUrls();
    }

    /**
     * @return bool
     */
    protected function rewriteNonSefCategoryUrls()
    {
        $request = Mage::app()->getRequest();
        if (!preg_match('/catalog\/category\/view\/id\/([0-9]+)/', $request->getRequestUri(), $requestMatch)) {
            return false;
        }

        $categoryId = $requestMatch[1];
        if (!$categoryId > 0) {
            return false;
        }

        $category = Mage::getModel('catalog/category')->load($categoryId);
        $sefUrl = $category->getRequestPath();
        if (empty($sefUrl)) {
            return false;
        }

        $request->setRequestUri($sefUrl);
        return true;
    }

    /**
     * @return bool
     */
    protected function rewriteNonSefProductUrls()
    {
        $request = Mage::app()->getRequest();
        if (!preg_match('/catalog\/product\/view\/id\/([0-9]+)/', $request->getRequestUri(), $requestMatch)) {
            return false;
        }

        $productId = $requestMatch[1];
        if (!$productId > 0) {
            return false;
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        $sefUrl = $product->getRequestPath();
        if (empty($sefUrl)) {
            return false;
        }

        $request->setRequestUri($sefUrl);
        return true;
    }

    /**
     * Set the current store of this request
     *
     * @exception Exception
     */
    protected function setCurrentStore()
    {
        try {
            Mage::app()->setCurrentStore($this->getStoreObject());
        } catch (Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to intialize store "' . $this->getStore() . '":' . $e->getMessage());
            // Do not return, but just keep on going with the default configuration
        }
    }

    /**
     * Manual hack to set the right continue-shopping URL to the HTTP_REFERER, even if it isn't "internal"
     *
     * @return bool
     */
    protected function setContinueShoppingToPreviousUrl()
    {
        if (Mage::getStoreConfig('magebridge/settings/continue_shopping_to_previous') != 1) {
            return false;
        }

        if (empty($_SERVER['HTTP_REFERER'])) {
            return false;
        }

        $customerSession = Mage::getSingleton('checkout/session');
        if (strstr($this->getRequestUrl(), 'checkout/cart')) {
            $customerSession->setContinueShoppingUrl($_SERVER['HTTP_REFERER']);
        } elseif (strstr($this->getRequestUrl(), 'firecheckout')) {
            $customerSession->setContinueShoppingUrl($_SERVER['HTTP_REFERER']);
        } elseif (strstr($this->getRequestUrl(), 'checkout/onepage/success')) {
            $customerSession->setNextUrl($_SERVER['HTTP_REFERER']);
        }
        return true;
    }

    /**
     * Manual hack to set the right customer-redirect URL
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    protected function setCustomerRedirectUrl()
    {
        if (!strstr($this->getRequestUrl(), 'customer/account/loginPost')) {
            return false;
        }

        if (Mage::getStoreConfig('customer/startup/redirect_dashboard') !== 0) {
            return false;
        }

        $location = $this->getStoreObject()->getBaseUrl();
        if (preg_match('/(uenc|referer)\/([^\/]+)/', $this->getRequestUrl(), $match)) {
            /** @var Yireo_MageBridge_Helper_Encryption */
            $helper = Mage::helper('magebridge/encryption');
            $location = $helper->base64_decode($match[2]);
        }

        $referer = null;
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        if (stristr($referer, '/checkout/') == false && stristr($referer, 'firecheckout') == false) {
            header('X-MageBridge-Location-Customer: ' . $location);
        }
        return true;
    }

    /**
     * Manual hack to set the right customer-redirect URL
     */
    protected function redirectContinueShoppingToPreviousUrl()
    {
        $continueShoppingToPrevious = (bool)Mage::getStoreConfig('magebridge/settings/continue_shopping_to_previous');
        $redirectToCart = (bool)Mage::getStoreConfig('checkout/cart/redirect_to_cart');

        if ($redirectToCart == false && $continueShoppingToPrevious && strstr($this->getRequestUrl(), 'checkout/cart/add')) {
            $location = null;
            if (preg_match('/(uenc|referer)\/([^\/]+)/', $this->getRequestUrl(), $match)) {
                /** @var Yireo_MageBridge_Helper_Encryption */
                $helper = Mage::helper('magebridge/encryption');
                $location = $helper->base64_decode($match[2]);
            }

            if (!empty($location)) {
                Mage::app()->getRequest()->setParam('return_url', $location);
            }
        }
    }

    /**
     * Method to change the regular Magento configuration as needed
     *
     * @return void
     */
    public function setConfig()
    {
        // To start with, save the meta data
        $this->saveMetaData();

        // Fetch a list of all stores
        $stores = Mage::app()->getStores();
        $websiteId = $this->getMetaData('website');

        // Loop through the stores to modify data
        foreach ($stores as $store) {
            /** @var Mage_Core_Model_Store $store */

            // Do not override stores outside this website
            if ($store->getWebsiteId() != $websiteId) {
                continue;
            }

            try {
                $this->setConfigPerStore($store);
            } catch (Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Unable to modify configuration: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param Mage_Core_Model_Store $store
     */
    protected function setConfigPerStore(Mage_Core_Model_Store $store)
    {
        //Mage::getSingleton('magebridge/debug')->notice('Override store configuration "'.$store->getCode().'"');
        $config_values = [];

        // If URL-modification is disabled, exit
        if ($this->getMetaData('modify_url') == 1) {
            // Get the current store
            //Mage::getSingleton('magebridge/debug')->notice('Set URLs of store "'.$store->getName().'" to '.$this->getMageBridgeSefUrl());

            // Collect the unmodified original URLs from the Configuration
            $urls = [];
            $urls['web/unsecure/base_url'] = $store->getConfig('web/unsecure/base_url');
            $urls['web/unsecure/base_link_url'] = $store->getConfig('web/unsecure/base_link_url');
            $urls['web/unsecure/base_media_url'] = $store->getConfig('web/unsecure/base_media_url');
            $urls['web/unsecure/base_skin_url'] = $store->getConfig('web/unsecure/base_skin_url');
            $urls['web/unsecure/base_js_url'] = $store->getConfig('web/unsecure/base_js_url');
            $urls['web/secure/base_url'] = $store->getConfig('web/secure/base_url');
            $urls['web/secure/base_link_url'] = $store->getConfig('web/secure/base_link_url');
            $urls['web/secure/base_media_url'] = $store->getConfig('web/secure/base_media_url');
            $urls['web/secure/base_skin_url'] = $store->getConfig('web/secure/base_skin_url');
            $urls['web/secure/base_js_url'] = $store->getConfig('web/secure/base_js_url');

            // Store the unmodified URLs in the registry for later reference
            if (Mage::registry('original_urls') == null) {
                Mage::register('original_urls', $urls);
            }

            // Proxy static content as well
            /**
             * if($store->getConfig('magebridge/settings/bridge_all') == 1) {
             * $proxy = 'index.php?option=com_magebridge&view=proxy&url=';
             * $base_media_url = str_replace($base_url, $proxy, $base_media_url);
             * $base_skin_url = str_replace($base_url, $proxy, $base_skin_url);
             * $base_js_url = str_replace($base_url, $proxy, $base_js_url);
             * }
             */

            // Set the main URL to Joomla! instead of Magento
            $urls['web/unsecure/base_url'] = $this->getMageBridgeSefUrl();
            $urls['web/secure/base_url'] = $this->getMageBridgeSefUrl();
            $urls['web/unsecure/base_link_url'] = $this->getMageBridgeSefUrl();
            $urls['web/secure/base_link_url'] = $this->getMageBridgeSefUrl();

            // Correct HTTP and HTTPS URLs in all URLs
            $has_ssl = Mage::getSingleton('magebridge/core')->getMetaData('has_ssl');
            foreach ($urls as $index => $url) {
                if ($has_ssl == true) {
                    $urls[$index] = preg_replace('/^http:/', 'https:', $url);
                } else {
                    $urls[$index] = preg_replace('/^https:/', 'http:', $url);
                }
            }

            // Rewrite of configuration values
            $config_values['web/unsecure/base_url'] = $urls['web/unsecure/base_url'];
            $config_values['web/unsecure/base_link_url'] = $urls['web/unsecure/base_link_url'];
            $config_values['web/unsecure/base_media_url'] = $urls['web/unsecure/base_media_url'];
            $config_values['web/unsecure/base_skin_url'] = $urls['web/unsecure/base_skin_url'];
            $config_values['web/unsecure/base_js_url'] = $urls['web/unsecure/base_js_url'];
            $config_values['web/secure/base_url'] = $urls['web/secure/base_url'];
            $config_values['web/secure/base_link_url'] = $urls['web/secure/base_link_url'];
            $config_values['web/secure/base_media_url'] = $urls['web/secure/base_media_url'];
            $config_values['web/secure/base_skin_url'] = $urls['web/secure/base_skin_url'];
            $config_values['web/secure/base_js_url'] = $urls['web/secure/base_js_url'];
        }

        // Apply other settings
        $config_values['web/seo/use_rewrites'] = 1;
        $config_values['web/session/use_remote_addr'] = 0;
        $config_values['web/session/use_http_via'] = 0;
        $config_values['web/session/use_http_x_forwarded_for'] = 0;
        $config_values['web/session/use_http_user_agent'] = 0;
        $config_values['web/cookie/cookie_domain'] = '';

        // Rewrite specific values
        if ($this->getMetaData('joomla_conf_lifetime') > 0) {
            $config_values['web/cookie/cookie_lifetime'] = $this->getMetaData('joomla_conf_lifetime');
        }

        if ($this->getMetaData('customer_group') > 0) {
            $config_values['customer/create_account/default_group'] = $this->getMetaData('customer_group');
        }

        if (strlen($this->getMetaData('theme')) > 0) {
            $theme = $this->getMetaData('theme');
            if (preg_match('/([a-zA-Z0-9\-\_]+)\/([a-zA-Z0-9\-\_]+)/', $theme, $match)) {
                $config_values['design/package/name'] = $match[1];
                $config_values['design/theme/default'] = $match[2];
                $config_values['design/theme/skin'] = $match[2];
                $config_values['design/theme/locale'] = $match[2];
                $config_values['design/theme/layout'] = $match[2];
                $config_values['design/theme/template'] = $match[2];
            } else {
                $config_values['design/theme/default'] = $theme;
                $config_values['design/theme/skin'] = $theme;
                $config_values['design/theme/locale'] = $theme;
                $config_values['design/theme/layout'] = $theme;
                $config_values['design/theme/template'] = $theme;
            }
        }

        // Rewrite these values for all stores
        foreach ($config_values as $path => $value) {
            if (method_exists($store, 'overrideCachedConfig')) {
                $store->overrideCachedConfig($path, $value);
            }
        }

        // Make sure we do not use SID= in the URL
        Mage::getModel('core/url')->setUseSession(false);
        Mage::getModel('core/url')->setUseSessionVar(true);
        //Mage::getSingleton('magebridge/debug')->notice('URL test 1: '.Mage::app()->getRequest()->getHttpHost());
        //Mage::getSingleton('magebridge/debug')->notice('URL test 2: '.Mage::helper('core/url')->getCurrentUrl());
        //Mage::getSingleton('magebridge/debug')->notice('URL test 3: '.Mage::helper('catalog/product')->getProductUrl(17));
        //Mage::getSingleton('magebridge/debug')->notice('URL test 4: '.$this->getRequestUrl());
    }

    /**
     * Method to set the current URL to the MageBridge SEF URL
     *
     * @access public
     *
     * @param null
     *
     * @return void
     */
    public function setSefUrl()
    {
        // Modify the configuration values
        $config_values = [
            'web/unsecure/base_url' => $this->getMageBridgeSefUrl(),
            'web/unsecure/base_link_url' => $this->getMageBridgeSefUrl(),
            'web/secure/base_url' => $this->getMageBridgeSefUrl(),
            'web/secure/base_link_url' => $this->getMageBridgeSefUrl(),
        ];

        // Rewrite the configuration
        $store = $this->getStoreObject();
        foreach ($config_values as $path => $value) {
            if (method_exists($store, 'overrideCachedConfig')) {
                $store->overrideCachedConfig($path, $value);
            }
        }
    }

    /**
     * Method to save metadata in the Magento Configuration
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function saveMetaData()
    {
        // List of keys (meta => conf)
        $keys = [
            'api_url' => 'api_url',
            'api_user' => 'api_user',
            'api_key' => 'api_key',
        ];

        // Check the Joomla! settings
        $refreshCache = false;
        foreach ($keys as $meta_key => $key) {
            $rt = $this->saveConfig($key, $this->getMetaData($meta_key), 'default', 0);
            if ($rt === true) {
                $refreshCache = true;
            }

            $rt = $this->saveConfig($key, $this->getMetaData($meta_key), 'websites', $this->getMetaData('website'));
            if ($rt === true) {
                $refreshCache = true;
            }
        }

        // Refresh the cache
        /** @var Yireo_MageBridge_Helper_Data */
        $helper = Mage::helper('magebridge');
        if ($refreshCache == true && Mage::app()->useCache('config') && $helper->useApiDetect() == true) {
            Mage::getSingleton('magebridge/debug')->notice('Refresh configuration cache');
            Mage::getConfig()->removeCache();
        }

        // Automatically append current host to allowed IPs (which is save because API authentication already succeeded)
        $this->autosaveAllowedIps();
    }

    /**
     * Automatically save allowed IPs settings
     *
     * @return bool
     */
    protected function autosaveAllowedIps()
    {
        /** @var Yireo_MageBridge_Model_Config_AllowedIps $allowedIps */
        $allowedIps = Mage::getModel('magebridge/config_allowedIps', $this->getStoreObject());

        if ($allowedIps->allowAutoConfig() === false) {
            return false;
        }

        $currentIps = $allowedIps->appendUrlAsIp($this->getMetaData('api_url'));

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $currentIps[] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            $currentIps[] = $_SERVER['SERVER_ADDR'];
        }

        $allowedIps->save($currentIps);
        return true;
    }

    /**
     * Method to cache API-details in the Magento configuration
     *
     * @access public
     *
     * @param string $key
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     *
     * @return bool
     */
    public function saveConfig($key, $value, $scope, $scopeId, $override = false)
    {
        // Do not save empty values
        if (empty($value)) {
            return false;
        }

        // Make sure the scope-ID is an integer
        $scopeId = (int)$scopeId;

        // Skip the Admin-scope
        if ($scope == 'websites' && $scopeId == 0) {
            return false;
        }

        // Fetch the current value
        if ($scope == 'default') {
            $current_value = (string)Mage::getConfig()->getNode('magebridge/joomla/' . $key, 'default');
        } else {
            $current_value = (string)Mage::getConfig()->getNode('magebridge/joomla/' . $key, $scope, $scopeId);
        }

        /** @var Yireo_MageBridge_Model_Debug */
        $magebridgeDebug = Mage::getSingleton('magebridge/debug');

        /** @var Yireo_MageBridge_Helper_Data */
        $helper = Mage::helper('magebridge');

        // Determine whether to save the current value
        $save = false;
        if (empty($current_value)) {
            $save = true;
        } elseif ($helper->useApiDetect() == true && $scope != 'default') {
            if ($key == 'api_url' && preg_replace('/^(http|https)\:/', '', $current_value) != preg_replace('/^(http|https)\:/', '', $value)) {
                $magebridgeDebug->notice('New API-value for "' . $key . '": "' . $current_value . '"; previously "' . $value . '"');
                $save = true;
            } elseif ($key != 'api_url' && $current_value != $value) {
                $magebridgeDebug->notice('New API-value for "' . $key . '": "' . $current_value . '"; previously "' . $value . '"');
                $save = true;
            }
        }

        // Save the value
        if ($save == true) {
            $magebridgeDebug->notice('saveConfig: magebridge/joomla/' . $key . ' = ' . $value . ' [' . $scope . '/' . $scopeId . ' ]');
            Mage::getConfig()->saveConfig('magebridge/joomla/' . $key, $value, $scope, $scopeId);
            return true;
        }

        return false;
    }

    /**
     * Method to get the currently defined API-user
     *
     * @access public
     *
     * @param null
     *
     * @return Mage_Api_Model_User
     */
    public function getApiUser()
    {
        $api_user_id = Mage::getStoreConfig('magebridge/joomla/api_user_id');

        if (!$api_user_id > 0) {
            $collection = Mage::getResourceModel('api/user_collection');
            foreach ($collection as $user) {
                $api_user_id = $user->getId();
                break;
            }
        }

        $api_user = Mage::getModel('api/user')->load($api_user_id);
        return $api_user;
    }

    /**
     * Method to authenticate usage of the MageBridge API
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function authenticate($api_user, $api_key)
    {
        // Fetch the variables from the meta-data
        $api_session = $this->getMetaData('api_session');
        if (empty($api_user)) {
            $api_user = $this->getMetaData('api_user');
        }
        if (empty($api_key)) {
            $api_key = $this->getMetaData('api_key');
        }

        // If the API-session matches, we don't need authenticate any more
        if ($api_session == md5(session_id() . $api_user . $api_key)) {
            return true;
        }

        // If we still need authentication, authenticate against the Magento API-class
        try {
            $api = Mage::getModel('api/user');
            if ($api->authenticate($api_user, $api_key) == true) {
                $this->setMetaData('api_session', md5(session_id() . $api_user . $api_key));
                return true;
            }
        } catch (Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Exception while authorizing: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Method to catch premature output in case of AJAX-stuff
     *
     * @access public
     *
     * @param null
     *
     * @return bool
     */
    public function preoutput()
    {
        // Match configured direct output
        $direct_output = Mage::helper('magebridge')->getDirectOutputUrls();
        if (!empty($direct_output)) {
            foreach ($direct_output as $url) {
                $url = trim($url);
                if (strstr($this->getRequestUrl(), $url)) {
                    Mage::getSingleton('magebridge/core')->getController(false);
                    return true;
                }
            }
        }

        // Check for URLs that look like AJAX URLs
        $request = Mage::app()->getRequest();
        if (stristr($request->getControllerName(), 'ajax') || stristr($request->getActionName(), 'ajax') || stristr($this->getRequestUrl(), 'ajax')) {
            Mage::getSingleton('magebridge/core')->getController(false);
            return true;
        }

        // Check if preoutput is forced manually
        if (stristr($this->getRequestUrl(), 'getAdditional')) {
            Mage::app()->getFrontController()->dispatch();
            return true;
        }

        // Preoutput for AW AJAX Cart
        if (isset($_REQUEST['awacp']) && $_REQUEST['awacp'] == 1) {
            Mage::app()->getFrontController()->dispatch();
            return true;
        }

        // Preoutput for Temando
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if (!empty($_REQUEST['country_id']) && !empty($_REQUEST['postcode']) && !empty($_REQUEST['product_id'])) {
                Mage::app()->getFrontController()->dispatch();
                return true;
            }
        }

        // Check if preoutput is forced manually
        if ($this->getForcePreoutput() == true) {
            Mage::getSingleton('magebridge/core')->getController(false);
            return true;
        }

        // Do NOT ever preoutput in the Joomla! backend
        if ($this->getMetaData('app') == 1) {
            return false;
        }

        // Preoutput when MageBridge has set the AJAX-flag (and there is no POST)
        if ($this->getMetaData('ajax') == 1 && ($this->getMetaData('post') == null && empty($_POST))) {
            if (strstr($_SERVER['HTTP_REFERER'], 'option=com_jmap')) {
                return false;
            } elseif (strstr($_SERVER['HTTP_REFERER'], 'option=com_menus')) {
                return false;
            }

            Mage::getSingleton('magebridge/core')->getController(false);
            return true;
        }

        // Initialize the frontcontroller
        $controller = Mage::getSingleton('magebridge/core')->getController(true);

        // Start the buffer and fetch the output from Magento
        $body = Mage::app()->getResponse()->getBody();
        if ($body != '') {
            $controller->getResponse()->clearBody();
            return true;
        }

        // Determine whether to preoutput compare links
        if (strstr($this->getRequestUrl(), 'catalog/product_compare/index')) {
            if ($this->getStoreObject()->getConfig('magebridge/settings/preoutput_compare') == 1) {
                echo $controller->getAction()->getLayout()->getOutput();
                return true;
            } else {
                return false;
            }
        }

        // Determine whether to preoutput gallery links
        if (strstr($this->getRequestUrl(), 'catalog/product/gallery')) {
            if ($this->getStoreObject()->getConfig('magebridge/settings/preoutput_gallery') == 1) {
                echo $controller->getAction()->getLayout()->getOutput();
                return true;
            } else {
                return false;
            }
        }

        // Scan for modified HTTP-headers
        foreach ($controller->getResponse()->getHeaders() as $header) {
            if (strtolower($header['name']) == 'content-type' && strstr($header['value'], 'text/xml')) {
                echo $controller->getAction()->getLayout()->getOutput();
                return true;
            }
        }

        // Get the current handles
        $handles = $controller->getAction()->getLayout()->getUpdate()->getHandles();

        // Check if there are any handles at all
        if (empty($handles)) {
            echo $controller->getAction()->getLayout()->getOutput();
            return true;
        }

        // Correct the session
        $session = Mage::getSingleton('customer/session');
        $sessionId = $session->getSessionId();
        if (empty($sessionId) && $session->getCustomerId() > 0) {
            $customer = Mage::getModel('customer/customer')->load($session->getCustomerId());
            $session->setCustomer($customer);
        }

        // Reset session if session_regenerate is used
        $session = Mage::getModel('core/session');
        if (!empty($_GET['SID']) && $session->getSessionId() != $_GET['SID']) {
            $session->setSessionId($_GET['SID']);
            session_id($_GET['SID']);
        }

        // Do not return direct output
        return false;
    }

    /**
     * Method to output the regular bridge-data through JSON
     *
     * @access public
     *
     * @param bool $complete
     *
     * @return bool
     */
    public function output($complete = true)
    {
        if ($complete) {
            $this->closeBridge();
        } else {
            $this->addResponseData('meta', [
                'type' => 'meta',
                'data' => [
                    'state' => $this->getMetaData('state'),
                    'extra' => $this->getMetaData('extra'),
                ],
            ]);
        }

        if ($this->getMetaData('debug')) {
            $debug = Mage::getSingleton('magebridge/debug')->getData();
            if (!empty($debug)) {
                $this->addResponseData('debug', [
                    'type' => 'debug',
                    'data' => $debug,
                ]);
            }
        }

        // Output the response
        $data = json_encode($this->getResponseData());
        return $data;
    }

    /**
     * Method to close the bridge and add the final data
     *
     * @access public
     *
     * @param null
     *
     * @return void
     */
    public function closeBridge()
    {
        // Add extra information
        $this->setMetaData('magento_session', session_id());
        $this->setMetaData('magento_version', Mage::getVersion());

        // Append customer-data
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $this->setMetaData('magento_customer', [
            'fullname' => $customer->getName(),
            'username' => $customer->getEmail(),
            'email' => $customer->getEmail(),
            'hash' => $customer->getPasswordHash(),
        ]);

        // Append Magento-data
        $this->setMetaData('magento_config', $this->getMageConfig());

        // Add events to the response
        $events = $this->getEvents();
        if (!empty($events)) {
            $this->addResponseData('events', [
                'type' => 'events',
                'data' => $events,
            ]);
        }

        // Add metadata to the response
        $metadata = $this->getMetaData();
        if (!empty($metadata)) {
            $this->addResponseData('meta', [
                'type' => 'meta',
                'data' => $metadata,
            ]);
        }
    }

    /**
     * Helper-function to parse Magento output for usage in Joomla!
     *
     * @access public
     *
     * @param string $string
     *
     * @return string
     */
    public function parse($string)
    {
        $string = str_replace(Mage::getUrl(), $this->getMageBridgeUrl(), $string);
        return $string;
    }

    /**
     * Return information on the current Magento configuration
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public function getMageConfig()
    {
        /** @var Yireo_MageBridge_Helper_Core */
        $coreHelper = Mage::helper('magebridge/core');
        /** @var Yireo_MageBridge_Helper_User */
        $userHelper = Mage::helper('magebridge/user');
        // Fetch current information
        $currentCategoryId = $coreHelper->getCurrentCategoryId();
        $currentCategoryPath = $coreHelper->getCurrentCategoryPath();
        $currentProductId = $coreHelper->getCurrentProductId();
        $currentProductSku = $coreHelper->getCurrentProductSku();

        // Construct extra data
        $store = $this->getStoreObject();
        $data = [
            'session_id' => Mage::getModel('core/session')->getSessionId(),
            'catalog/seo/product_url_suffix' => $store->getConfig('catalog/seo/product_url_suffix'),
            'catalog/seo/category_url_suffix' => $store->getConfig('catalog/seo/category_url_suffix'),
            'admin/security/session_cookie_lifetime' => $store->getConfig('admin/security/session_cookie_lifetime'),
            'web/cookie/cookie_lifetime' => $store->getConfig('web/cookie/cookie_lifetime'),
            'customer/email' => Mage::getModel('customer/session')->getCustomer()->getEmail(),
            'customer/joomla_id' => $userHelper->getCurrentJoomlaId(),
            'customer/magento_id' => Mage::getModel('customer/session')->getCustomerId(),
            'customer/magento_group_id' => Mage::getModel('customer/session')->getCustomer()->getGroupId(),
            'backend/path' => $this->getAdminPath(),
            'store_name' => $store->getName(),
            'store_code' => $store->getCode(),
            'base_js_url' => Mage::getBaseUrl('js'),
            'base_media_url' => Mage::getBaseUrl('media'),
            'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            'root_template' => $this->getRootTemplate(),
            'root_category' => $store->getRootCategoryId(),
            'current_category_id' => $currentCategoryId,
            'current_category_path' => $currentCategoryPath,
            'current_product_id' => $currentProductId,
            'current_product_sku' => $currentProductSku,
            'referer' => Mage::app()->getRequest()->getServer('HTTP_REFERER'),
            'controller' => Mage::app()->getRequest()->getControllerName(),
            'action' => Mage::app()->getRequest()->getActionName(),
            'request' => $this->getRequestUrl(),
            'store_urls' => [],
            'handles' => [],
        ];

        // Add store URLs for current page
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
            $code = $store->getCode();

            // Product URL
            if ($currentProductId > 0) {
                $url = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($currentProductId)->getUrlPath();
                $data['store_urls'][$code] = $url;

            // Category URL
            } elseif ($currentCategoryId > 0) {
                $url = Mage::getModel('catalog/category')->setStoreId($store->getId())->load($currentCategoryId)->getUrlPath();
                $data['store_urls'][$code] = $url;
            }
        }

        // Add available handles
        $controller = Mage::getSingleton('magebridge/core')->getController();
        $handles = $controller->getAction()->getLayout()->getUpdate()->getHandles();
        if (!empty($handles)) {
            foreach ($handles as $handle) {
                $data['handles'][] = $handle;
            }
        }

        // Append extra data
        foreach ($data as $name => $value) {
            $this->_mage_config[$name] = $value;
        }

        return $this->_mage_config;
    }

    /**
     * Set Magento config-data to return through the bridge
     *
     * @access public
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function setMageConfig($name, $value)
    {
        $this->_mage_config[$name] = $value;
    }

    /**
     * Return the current URL
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public function getRequestUrl()
    {
        return preg_replace('/^\//', '', Mage::getModel('core/url')->getRequest()->getRequestUri());
    }

    /**
     * Return the path to the Magento Admin Panel
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public function getAdminPath()
    {
        $routeName = 'adminhtml';
        $route = Mage::app()->getFrontController()->getRouterByRoute($routeName);
        $backend = $route->getFrontNameByRoute($routeName);
        return $backend;
    }

    /**
     * Return the current page layout for the Magento theme
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public function getRootTemplate()
    {
        $block = Mage::getModel('magebridge/block')->getBlock('root');
        $root_block = 'none';
        if (!empty($block)) {
            $root_block = $block->getTemplate();
        }
        return $root_block;
    }

    /**
     * Helper-method to get the Front-controller
     *
     * @access public
     *
     * @param boolean $norender
     *
     * @return object
     */
    public static function getController($norender = true)
    {
        // Default variables
        $fullDispatch = (bool)Mage::getStoreConfig('magebridge/settings/full_dispatch');
        $httpResponseSendBefore = false;

        // Workaround for AJAX Cart Pro
        $awacp = (isset($_REQUEST['awacp']) && $_REQUEST['awacp'] == 1) ? true : false;
        if ($awacp) {
            $fullDispatch = false;
        }

        // Singleton to initialize the front-controller
        static $controller;
        if (empty($controller)) {
            // Initialize the front-controller
            yireo_benchmark('MB_Core::getFrontController() - start');
            $controller = Mage::app()->getFrontController();
            $controller->setNoRender($norender);


            // Run the controller_front_init_before event
            Mage::dispatchEvent('controller_front_init_before', ['front' => $controller]);

            if ($fullDispatch == true) {
                $controller->dispatch();
                yireo_benchmark('MB_Core::getFrontController() - fully dispatched');
            } else {
                // Replicate the dispatch() method of the front-controller, without sending a response
                $request = $controller->getRequest();
                $request->setPathInfo()->setDispatched(false);
                if (!$request->isStraight()) {
                    Mage::getModel('core/url_rewrite')->rewrite();
                }
                $controller->rewrite();

                $i = 0;
                $routers = $controller->getRouters();
                while (!$request->isDispatched() && $i++ < 50) {
                    foreach ($routers as $router) {
                        if ($router->match($controller->getRequest())) {
                            break;
                        }
                    }
                }
                Varien_Profiler::stop('mage::dispatch::routers_match');
                if ($i > 100) {
                    Mage::throwException('Front controller reached 100 router match iterations');
                }

                // Call upon events that need to do something before the layout renders
                if (Mage::registry('mb_controller_action_layout_render_before') == false) {
                    Mage::getSingleton('magebridge/debug')->notice('MB throws event "controller_action_layout_render_before"');
                    Mage::dispatchEvent('controller_action_layout_render_before');
                    Mage::register('mb_controller_action_layout_render_before', true);
                }

                // Simulate sending a response (but without outputBody())
                Mage::dispatchEvent('controller_front_send_response_before', ['front' => $controller]);
                $response = $controller->getResponse();
                if ($httpResponseSendBefore) {
                    Mage::dispatchEvent('http_response_send_before', ['response' => $response]);
                }
                $response->sendHeaders();
                Mage::dispatchEvent('controller_front_send_response_after', ['front' => $controller]);
            }

            // Preset some HTTP-headers
            header('X-MageBridge-Customer: ' . Mage::getModel('customer/session')->getCustomer()->getEmail());
            header('X-MageBridge-Form-Key: ' . Mage::getSingleton('core/session')->getFormKey());
            // Note: Do not use the Magento API for this, because it is not used by magebridge.class.php > output

            yireo_benchmark('MB_Core::getFrontController() - end');
        }

        return $controller;
    }

    /**
     * Helper-method to get the bridge-request
     *
     * @access public
     *
     * @param null
     *
     * @return array
     */
    public function getRequestData()
    {
        return $this->_request;
    }

    /**
     * Helper-method to get the bridge-response
     *
     * @access public
     *
     * @param null
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->_response;
    }

    /**
     * Helper-method to set the bridge-response
     *
     * @access public
     *
     * @param array $data
     *
     * @return null
     */
    public function setResponseData($data)
    {
        $this->_response = $data;
    }

    /**
     * Helper-method to add some data to the bridge-response
     *
     * @access public
     *
     * @param string $name
     * @param array $data
     *
     * @return null
     */
    public function addResponseData($name, $data)
    {
        $this->_response[$name] = $data;
        return true;
    }

    /**
     * Helper-method to get the meta-data
     *
     * @access public
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getMetaData($name = null)
    {
        if ($name == null) {
            return $this->_meta;
        } elseif (isset($this->_meta[$name])) {
            return $this->decrypt($this->_meta[$name]);
        } else {
            return null;
        }
    }

    /**
     * Helper-method to set the meta-data
     *
     * @access public
     *
     * @param string $name
     * @param mixed $value
     *
     * @return null
     */
    public function setMetaData($name = null, $value = null)
    {
        $this->_meta[$name] = $value;
        return null;
    }

    /**
     * Helper-method to get the flag for preoutput-forcing
     *
     * @access public
     *
     * @param null
     *
     * @return array
     */
    public function getForcePreoutput()
    {
        return $this->_force_preoutput;
    }

    /**
     * Helper-method to set the flag for preoutput-forcing
     *
     * @access public
     *
     * @param null
     *
     * @return void
     */
    public function setForcePreoutput($force_preoutput)
    {
        $this->_force_preoutput = $force_preoutput;
    }

    /**
     * Helper-method to get the system events from the session and clean up afterwards
     *
     * @access public
     *
     * @param null
     *
     * @return array
     */
    public function getEvents()
    {
        $events = Mage::getSingleton('magebridge/session')->getEvents();
        Mage::getSingleton('magebridge/session')->cleanEvents();
        return $events;
    }

    /**
     * Helper-method to set the system events
     *
     * @access public
     *
     * @param array
     *
     * @return void
     */
    public function setEvents($events)
    {
        $this->_events = $events;
        return null;
    }

    /**
     * Helper-method to get the Joomla! URL from the meta-data
     *
     * @access public
     *
     * @param null
     *
     * @return string
     * @deprecated
     */
    public function getMageBridgeUrl()
    {
        return $this->getMageBridgeSefUrl();
    }

    /**
     * Helper-method to get the Joomla! SEF URL from the meta-data
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public function getMageBridgeSefUrl()
    {
        if ($this->getMetaData('app') == 1) {
            return $this->getMetaData('joomla_url');
        } else {
            return $this->getMetaData('joomla_sef_url');
        }
    }

    /**
     * Helper-method to get the requested store-name from the meta-data
     *
     * @return string
     */
    public function getStore()
    {
        return $this->getMetaData('store');
    }

    /**
     * Helper-method to get the requested store-name from the meta-data
     *
     * @return Mage_Core_Model_Store
     */
    public function getStoreObject()
    {
        return Mage::app()->getStore($this->getStore());
    }

    /**
     * Return the configured license key
     *
     * @return string
     */
    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/hidden/support_key');
    }

    /**
     * Return the current session ID
     *
     * @return string
     */
    public function getMageSession()
    {
        return session_id();
    }

    /**
     * Encrypt data for security
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        /** @var Yireo_MageBridge_Helper_Encryption */
        $helper = Mage::helper('magebridge/encryption');
        return $helper->encrypt($data);
    }

    /**
     * Decrypt data after encryption
     *
     * @param mixed $data
     *
     * @return string
     */
    public function decrypt($data)
    {
        /** @var Yireo_MageBridge_Helper_Encryption */
        $helper = Mage::helper('magebridge/encryption');
        return $helper->decrypt($data);
    }

    /**
     * Determine whether event forwarding is enabled
     *
     * @return bool
     */
    public function isEnabledEvents()
    {
        return $this->_enable_events;
    }

    /**
     * Disable event forwarding
     *
     * @return bool
     */
    public function disableEvents()
    {
        $this->_enable_events = false;
        return $this->_enable_events;
    }
}
