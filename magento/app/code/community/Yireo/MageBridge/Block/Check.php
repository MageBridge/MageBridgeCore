<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge class for the check-block
 */
class Yireo_MageBridge_Block_Check extends Mage_Core_Block_Template
{
    private $mb_sytem_checks = array();

    const CHECK_OK = 'ok';
    const CHECK_WARNING = 'warning';
    const CHECK_ERROR = 'error';

    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area','adminhtml');
        $this->setTemplate('magebridge/check.phtml');
    }

    /*
     * Helper to return the header of this page
     *
     * @access public
     * @param string $title
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - '.$this->__($title);
    }

    /*
     * Helper to return the menu
     *
     * @access public
     * @param null
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /*
     * Helper to add a check to this list
     *
     * @access public
     * @param null
     * @return string
     */
    private function addResult($group, $check, $status = 0, $description = '')
    {
        $checks = $this->mb_system_checks;
        $checks[$group][] = array(
            'check' => $this->__($check),
            'status' => $status,
            'description' => $this->__($description),
        );

        $this->mb_system_checks = $checks;
        return;
    }

    /*
     * Check the license key
     *
     * @access public
     * @param null
     * @return string
     */
    public function getChecks()
    {
        $store = Mage::app()->getStore(Mage::getModel('magebridge/core')->getStore());

        $license = Mage::helper('magebridge')->getLicenseKey();
        if(empty($license) || strlen($license) < 20) {
            $result = self::CHECK_ERROR;
            $description = "You don't have a valid support-key to communicate with Joomla! yet";
        } else {
            $result = self::CHECK_OK;
            $description = "Your support-key is configured to communicate with Joomla!";
        }
        $this->addResult('conf', 'Support key', $result, $description);

        $api_url = Mage::getStoreConfig('magebridge/joomla/api_url');
        $result = (!empty($api_url)) ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('conf', 'Joomla! API', $result, 'Once Joomla! accesses MageBridge, the API URL is automatically configured');

        $result = ($store->getConfig('web/url/redirect_to_base') != '0') ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('conf', 'Redirect to Base', $result, 'The Magento setting "Redirect To Base" needs to be set to "No".');

        $result = ($store->getConfig('web/seo/use_rewrites') == '0') ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('conf', 'Use Rewrites', $result, 'The Magento setting "Use Rewrites" needs to be set to "Yes".');

        $result = ($store->getConfig('web/url/use_store') != '0') ? self::CHECK_WARNING : self::CHECK_OK;
        $this->addResult('conf', 'Add store-code to URL', $result, 'The Magento setting "Add store-code to URL" needs to be set to "No" in most cases.');

        $homepage = Mage::getModel('cms/page')->load(Mage::getStoreConfig('web/default/cms_home_page'), 'identifier');
        $result = (empty($homepage) || !$homepage->getId() > 0) ? self::CHECK_WARNING : self::CHECK_OK;
        $this->addResult('conf', 'Magento homepage', $result, 'An empty Magento homepage might cause strange results.');

        $global_base_url_unsecure = Mage::getStoreConfig('web/unsecure/base_url');
        $global_base_url_secure = Mage::getStoreConfig('web/secure/base_url');
        $override_global_base_url_unsecure = false;
        $override_global_base_url_secure = false;
        $websites = Mage::getModel('core/website')->getCollection();
        $description = 'System Configuration needs to have the MageBridge Root Menu-Item URL listed for the Website-scope';

        foreach($websites as $website ) {
            $store = $website->getDefaultStore();
            $base_url_unsecure = Mage::getStoreConfig('web/unsecure/base_url', $store);
            if($global_base_url_unsecure != $base_url_unsecure) {
                $override_global_base_url_unsecure = true;
            }

            $base_url_secure = Mage::getStoreConfig('web/secure/base_url', $store);
            if($global_base_url_secure != $base_url_secure) {
                $override_global_base_url_secure = true;
            }
        }

        $result = ($override_global_base_url_unsecure == false && $override_global_base_url_secure == false) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('conf', 'Joomla! unsecure URLs', $result, $description);

        $result = (version_compare(phpversion(), '5.2.8', '>=')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'PHP version', $result, "PHP version 5.2.8 or higher is needed. A latest PHP version is always recommended.");

        $current = ini_get('memory_limit');
        $result = (version_compare($current, '255M', '>')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'PHP memory', $result, "The minimum requirement for Magento itself is 256Mb. Current memory: ".$current);

        $file = Mage::getBaseDir().DS.'magebridge.php';
        $perms = substr(sprintf('%o', @fileperms($file)), -4);
        $result = (empty($perms) || strstr($perms, '666') || strstr($perms, '777')) ? self::CHECK_WARNING : self::CHECK_OK;
        $this->addResult('system', 'Bridge file', $result, 'A bridge-file "magebridge.php" with mode 666 or 777 might indicate problems with file permissions');

        $file = Mage::getBaseDir().DS.'js'.DS.'index.php';
        $perms = substr(sprintf('%o', @fileperms($file)), -4);
        $result = (empty($perms) || strstr($perms, '666') || strstr($perms, '777')) ? self::CHECK_WARNING : self::CHECK_OK;
        $this->addResult('system', 'JS index', $result, 'A file "js/index.php" with mode 666 or 777 might indicate problems with file permissions');

        $sapi = php_sapi_name();
        $result = (preg_match('/cgi/i', $sapi)) ? self::CHECK_WARNING : self::CHECK_OK;
        $this->addResult('system', 'PHP CGI', $result, 'When PHP is run as CGI, make sure all file permissions are correct. Current SAPI: '.$sapi);

        $result = (function_exists('json_decode')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'JSON', $result, 'The JSON-extension for PHP is needed');

        $result = (function_exists('curl_init')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'CURL', $result, 'The CURL-extension for PHP is needed');

        $result = (function_exists('simplexml_load_string')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'SimpleXML', $result, 'The SimpleXML-extension for PHP is needed');

        $result = (in_array('ssl', stream_get_transports())) ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('system', 'OpenSSL', $result, 'PHP support for OpenSSL is needed if you want to use HTTPS');

        $result = (function_exists('mcrypt_cfb')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'mcrypt', $result, 'The mcrypt-extension for PHP is needed');

        $result = (function_exists('iconv')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'iconv', $result, 'The iconv-extension for PHP is needed');

        $result = (@class_exists('ZipArchive')) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'ZipArchive', $result, 'ZipArchive in PHP is needed for one-click upgrades. This bundles with PECL-zip 1.1.0 or higher.');

        $result = (ini_get('safe_mode')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('system', 'Safe Mode', $result, 'PHP Safe Mode is strongly outdated and not supported by either Joomla! or Magento');

        $result = (ini_get('magic_quotes_gpc')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('system', 'Magic Quotes GPC', $result, 'Magic Quotes GPC is outdated and should be disabled');

        $cacheBackend = (string)Mage::getConfig()->getNode('global/cache/backend');
        $result = (in_array($cacheBackend, array('files', 'db'))) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('system', 'Caching Backend', $result, 'We recommend a fast caching backend like Redis or memcache [current: '.$cacheBackend.']');

        $apc = ini_get('apc.enabled');
        $memcached = extension_loaded('memcache');
        $xcache = extension_loaded('xcache');
        $eaccelerator = extension_loaded('eaccelerator');
        $opcache = (bool)ini_get('opcache.enable');
        $result = ($opcache || $apc || $memcached || $xcache || $eaccelerator) ? self::CHECK_OK : self::CHECK_WARNING;
        $this->addResult('system', 'OPC-caching', $result, 'An OPC-caching PHP-extension (like Zend OPC, APC or XCache) is highly recommended');

        $remote_domain = 'api.yireo.com';
        $result = (gethostbyname($remote_domain) == $remote_domain) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('system', 'DNS', $result, 'External DNS lookups need to be enabled');
        
        $result = (@fsockopen($remote_domain, 80, $errno, $errmsg, 5)) ? self::CHECK_OK : self::CHECK_ERROR;
        $this->addResult('system', 'Firewall', $result, 'Firewall needs to allow outgoing access on port 80.');

        $result = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('system', 'Operating System', $result, 'Windows platforms are not supported.');

        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        $result = (isset($modulesArray['ArtsOnIT_OfflineMaintenance']) && $modulesArray['ArtsOnIT_OfflineMaintenance']->is('active')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'ArtsOnIT_OfflineMaintenance', $result, 'MageBridge is not compatible with the module ArtsOnIT_OfflineMaintenance');

        $result = (!self::isMagebridgeClass('model', 'core/url')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override core URLs', $result, 'Core-model "core/url" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('model', 'adminhtml/url')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override backend URLs', $result, 'Core-model "adminhtml/url" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('model', 'customer/customer')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override customer-class', $result, 'Core-model "customer/customer" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('model', 'core/email_template_filter')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override email-filter', $result, 'Core-model "core/email_template_filter" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('model', 'core/message_collection')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override messages', $result, 'Core-model "core/message_collection" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('model', 'core/store')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override store-class', $result, 'Core-model "core/store" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('block', 'page/html_breadcrumbs')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override breadcrumbs', $result, 'Core-block "page/html_breadcrumbs" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('block', 'checkout/onepage_success')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override success-page', $result, 'Core-block "checkout/onepage_success" should be overwritten by MageBridge');

        $result = (!self::isMagebridgeClass('block', 'core/text_list')) ? self::CHECK_ERROR : self::CHECK_OK;
        $this->addResult('overrides', 'Override text-block', $result, 'Core-block "core/text_list" should be overwritten by MageBridge');

        return $this->mb_system_checks;
    }

    /*
     * Return the log URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getLogUrl($type = null)
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/log', array('type' => $type));
    }

    /*
     * Return the log URL
     *
     * @access public
     * @param string $type
     * @param string $code
     * @return bool
     */
    public function isMagebridgeClass($type, $code)
    {
        if($type == 'model') {
            $class_name = Mage::getConfig()->getModelClassName($code);
        } elseif($type == 'block') {
            $class_name = Mage::getConfig()->getBlockClassName($code);
        }

        if(preg_match('/^Yireo_MageBridge/', $class_name)) {
            return true;
        }

        return false;
    }
}
