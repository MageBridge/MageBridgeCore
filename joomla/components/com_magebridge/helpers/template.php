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
 * Helper for usage in Joomla!/MageBridge modules and templates
 */
class MageBridgeTemplateHelper
{
    /**
     * Determine if the bridge is loaded with some CSS-stylesheets
     *
     * @param null
     *
     * @return bool
     */
    public static function hasCss()
    {
        $stylesheets = MageBridgeModelBridgeHeaders::getInstance()
            ->getStylesheets();

        if (empty($stylesheets)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the bridge is loaded with some JavaScript-scripts
     *
     * @param null
     *
     * @return bool
     */
    public static function hasJs()
    {
        $scripts = MageBridgeModelBridgeHeaders::getInstance()
            ->getScripts();

        if (empty($scripts)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the bridge is loaded with ProtoType
     *
     * @param null
     *
     * @return bool
     */
    public static function hasPrototypeJs()
    {
        return MageBridgeModelBridgeHeaders::getInstance()
            ->hasProtoType();
    }

    /**
     * Function to remove Magento scripts from the page
     *
     * @param null
     *
     * @return void
     */
    public static function removeMagentoScripts()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $document = JFactory::getDocument();

        $bridge->build();
        $headers = $document->getHeadData();
        $mageurl = $bridge->getMagentoUrl();

        foreach ($headers['scripts'] as $index => $header) {
            if (strstr($header, $mageurl)) {
                unset($headers['scripts'][$index]);
            }
        }

        foreach ($headers['custom'] as $index => $header) {
            if (strstr($header, $mageurl) || strstr($header, 'new Translate')) {
                unset($headers['custom'][$index]);
            } else {
                if (strstr($header, 'protoaculous')) {
                    unset($headers['custom'][$index]);
                }
            }
        }

        $document->setHeadData($headers);

        MageBridgeModelConfig::load('disable_js_footools', 1);
        MageBridgeModelConfig::load('disable_js_mootools', 0);
    }

    /**
     * Alternative for getRootTemplate
     *
     * @param null
     *
     * @return string
     */
    public static function getPageLayout()
    {
        return self::getRootTemplate();
    }

    /**
     * Get the current page layout
     *
     * @param null
     *
     * @return string
     */
    public static function getRootTemplate()
    {
        static $tmpl = null;

        if ($tmpl == null) {
            $tmpl = MageBridge::getBridge()
                ->getSessionData('root_template');
            $tmpl = preg_replace('/^page\//', '', $tmpl);
            $tmpl = preg_replace('/\.phtml$/', '', $tmpl);
        }

        return $tmpl;
    }

    /**
     * Get the Magento XML-handles
     *
     * @param null
     *
     * @return array
     */
    public static function getHandles()
    {
        static $handles = null;

        if ($handles == null) {
            $handles = MageBridge::getBridge()
                ->getSessionData('handles');
        }

        return $handles;
    }

    /**
     * Check for a specific Magento XML-handles
     *
     * @param string
     *
     * @return boolean
     */
    public static function hasHandle($match)
    {
        $handles = MageBridge::getBridge()
            ->getSessionData('handles');

        if (!empty($handles)) {
            foreach ($handles as $handle) {
                if ($handle == $match) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the Magento theme is using the left-column layout
     *
     * @param null
     *
     * @return bool
     */
    public static function hasLeftColumn()
    {
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        if ($layout == '2columns-left' || $layout == '3columns') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the Magento theme is using the right-column layout
     *
     * @param null
     *
     * @return bool
     */
    public static function hasRightColumn()
    {
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        if ($layout == '2columns-right' || $layout == '3columns') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the Magento layout uses all three columns
     *
     * @param null
     *
     * @return bool
     */
    public static function hasAllColumns()
    {
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        if (preg_match('/^3columns/', $layout)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the Magento layout uses the two columns (content + one side-column
     *
     * @param null
     *
     * @return bool
     */
    public static function hasTwoColumns()
    {
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        if (preg_match('/^2columns/', $layout)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the Magento layout uses only the main component area
     *
     * @param null
     *
     * @return bool
     */
    public static function hasOneColumn()
    {
        if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return true;
        }

        $layout = self::getPageLayout();

        if ($layout == '1column' || $layout == 'one-column') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the current Magento store-name
     *
     * @param null
     *
     * @return bool
     */
    public static function getStore()
    {
        return MageBridge::getBridge()
            ->getSessionData('store_code');
    }

    /**
     * Get the current Magento page-request
     *
     * @param null
     *
     * @return bool
     */
    public static function getRequest()
    {
        return MageBridgeUrlHelper::getRequest();
    }

    /**
     * Determine if the current request is the homepage
     *
     * @param null
     *
     * @return bool
     */
    public static function isHomePage()
    {
        $request = self::getRequest();
        $request = preg_replace('/\?(.*)/', '', $request); // Strip out GET-arguments

        if (JFactory::getApplication()->input->getCmd('option') == 'com_magebridge' && empty($request)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the current request is a specific page
     *
     * @param null
     *
     * @return bool
     */
    public static function isPage($pages = null, $request = null)
    {
        if (empty($request) && JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') {
            return false;
        }

        if (empty($request)) {
            $request = self::getRequest();
        }

        if (empty($request)) {
            return false;
        }

        if (!empty($pages)) {
            if (is_string($pages)) {
                $pages = [$pages];
            }

            foreach ($pages as $page) {
                $page = trim($page);

                if (empty($page)) {
                    continue;
                }

                $page = preg_replace('/\/$/', '', $page); // Strip the backslash in the end
                $page = str_replace('/', '\/', $page); // Transform this string for use within preg_match
                $page = preg_replace('/\.\*$/', '', $page); // Remove .* at end of string
                $page = preg_replace('/\*$/', '', $page); // Remove * at end of string
                $page = preg_replace('/\*/', '\\\*', $page); // Escape any remaining characters

                if (preg_match('/^' . $page . '/', $request)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the current request is a catalog page
     *
     * @param null
     *
     * @return bool
     */
    public static function isCatalogPage()
    {
        return self::isPage('catalog/*');
    }

    /**
     * Determine if the current request is a catalog product page
     *
     * @param null
     *
     * @return bool
     */
    public static function isProductPage()
    {
        return self::isPage('catalog/product/*') || self::isPage('checkout/cart/configure/id/*');
    }

    /**
     * Determine if the current request is a catalog category page
     *
     * @param null
     *
     * @return bool
     */
    public static function isCategoryPage()
    {
        return self::isPage('catalog/category/*');
    }

    /**
     * Determine if the current request is a customer page
     *
     * @param null
     *
     * @return bool
     */
    public static function isCustomerPage()
    {
        if (self::isPage('customer/*') || self::isPage('sales/*') || self::isPage('review/customer/*') || self::isPage('tag/customer/*') || self::isPage('wishlist/*') || self::isPage('oauth/customer_token/*') || self::isPage('newsletter/manage/*') || self::isPage('downloadable/customer/*')) {
            return true;
        }

        $customer_pages = trim(MageBridgeModelConfig::load('customer_pages'));

        if (!empty($customer_pages)) {
            $customer_pages = explode("\n", $customer_pages);

            foreach ($customer_pages as $customer_page) {
                if (self::isPage($customer_page)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the current request is the shopping cart
     *
     * @param null
     *
     * @return bool
     */
    public static function isCartPage()
    {
        return self::isPage('checkout/cart');
    }

    /**
     * Determine if the current request is a checkout page
     *
     * @param $only_checkout Parameter to skip cart-page
     *
     * @return bool
     */
    public static function isCheckoutPage($only_checkout = false)
    {
        $only_checkout = (bool) $only_checkout;

        if (self::isCartPage() && $only_checkout == true) {
            return false;
        }

        if (self::isPage('checkout/*') || self::isPage('onestepcheckout/*') || self::isPage('firecheckout') || self::isPage('onepage')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the current request is a sales page
     *
     * @param null
     *
     * @return bool
     */
    public static function isSalesPage()
    {
        return self::isPage('sales/*');
    }

    /**
     * Determine if the current request is the wishlist
     *
     * @param null
     *
     * @return bool
     */
    public static function isWishlistPage()
    {
        return self::isPage('wishlist/*');
    }

    /**
     * Return the current product ID
     *
     * @param null
     *
     * @return int
     */
    public static function getProductId()
    {
        $product_id = MageBridge::getBridge()
            ->getSessionData('current_product_id');

        if ($product_id > 0) {
            return $product_id;
        }

        $request = self::getRequest();

        if (preg_match('/catalog\/product\/view\/id\/([0-9]+)/', $request, $match)) {
            return $match[1];
        }

        return 0;
    }

    /**
     * Check whether the current category-ID is x
     *
     * @param int $category_id
     *
     * @return bool
     */
    public static function isCategoryId($category_id = 0)
    {
        if (self::getCategoryId() == $category_id) {
            return true;
        }

        $category_path = MageBridge::getBridge()
            ->getSessionData('current_category_path');

        if (!empty($category_path)) {
            $category_path = explode('/', $category_path);

            if (in_array($category_id, $category_path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the current category ID
     *
     * @param null
     *
     * @return int
     */
    public static function getCategoryId()
    {
        $category_id = MageBridge::getBridge()
            ->getSessionData('current_category_id');

        if ($category_id > 0) {
            return $category_id;
        }

        $request = self::getRequest();

        if (preg_match('/catalog\/category\/view\/id\/([0-9]+)/', $request, $match)) {
            return $match[1];
        }

        return 0;
    }

    /**
     * Determine if MageBridge is loaded
     *
     * @param null
     *
     * @return bool
     */
    public static function isLoaded()
    {
        if (JFactory::getApplication()->input->getCmd('option') == 'com_magebridge') {
            return true;
        } else {
            $document = JFactory::getDocument();
            $modules = MageBridgeModuleHelper::loadMageBridgeModules();
            $buffer = $document->getBuffer();

            foreach ($modules as $module) {
                if (preg_match('/^mod_magebridge_/', $module->module)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the browser is a mobile browser or not
     *
     * @param null
     *
     * @return bool
     */
    public static function isMobile()
    {
        if (class_exists('MobileDetector')) {
            return MobileDetector::isMobile();
        }

        jimport('joomla.environment.browser');
        $browser = JBrowser::getInstance();

        if (method_exists($browser, 'isMobile')) {
            return (bool) $browser->isMobile();
        } elseif (method_exists($browser, 'get')) {
            return (bool) $browser->get('mobile');
        }

        return false;
    }

    /**
     * Method to determine whether a certain module is loaded or not
     *
     * @static
     * @access public
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasModule($name = '')
    {
        // Import the module helper
        jimport('joomla.application.module.helper');

        $instance = JModuleHelper::getModule($name);

        if (is_object($instance)) {
            return true;
        }

        return false;
    }

    /**
     * Copy of the original JDocumentHTML::countModules() method, but this copy skips empty modules as well
     *
     * @param string $condition
     *
     * @return integer
     */
    public static function countModules($condition)
    {
        $result = '';
        $document = JFactory::getDocument();

        $words = explode(' ', $condition);

        for ($i = 0; $i < count($words); $i += 2) {
            // odd parts (modules)
            $name = strtolower($words[$i]);
            $buffer = $document->getBuffer('modules', $name);

            if (!isset($buffer) || $buffer === false || empty($buffer)) {
                $words[$i] = 0;
            } else {
                $words[$i] = count(JModuleHelper::getModules($name));
            }
        }

        return (int) array_sum($words);
    }

    /**
     * Function that determines whether a certain module-position should be "flushed" depending on MageBridge settings
     *
     * @param string $condition
     *
     * @return bool
     */
    public static function allowPosition($position)
    {
        // If the position is empty, default to true
        $position = trim($position);

        if (empty($position)) {
            return true;
        }

        // Check for a certain page
        $setting = self::getFlushSettingByPage();

        // If the page-check returns empty, default to true
        if (empty($setting)) {
            return true;
        }

        // Check for flushing of positions within the MageBridge configuration
        $globalArray = explode(',', MageBridgeModelConfig::load($setting));
        $plugin = JPluginHelper::getPlugin('system', 'magebridgepositions');
        $pluginParams = json_decode($plugin->params, true);
        $pluginArray = (isset($pluginParams[$setting])) ? explode(',', $pluginParams[$setting]) : [];

        $array = array_merge($globalArray, $pluginArray);

        if (!empty($array)) {
            foreach ($array as $a) {
                if ($position == trim($a)) {
                    return false;
                }
            }
        }

        // Default to true
        return true;
    }

    public static function getFlushSettingByPage()
    {
        if (MageBridgeTemplateHelper::isHomePage()) {
            return 'flush_positions_home';
        }

        if (MageBridgeTemplateHelper::isCustomerPage()) {
            return 'flush_positions_customer';
        }

        if (MageBridgeTemplateHelper::isProductPage()) {
            return 'flush_positions_product';
        }

        if (MageBridgeTemplateHelper::isCategoryPage()) {
            return 'flush_positions_category';
        }

        if (MageBridgeTemplateHelper::isCartPage()) {
            return 'flush_positions_cart';
        }

        if (MageBridgeTemplateHelper::isCheckoutPage()) {
            return 'flush_positions_checkout';
        }
    }

    /**
     * Function to load a Magento stylesheet
     *
     * @param string $file
     *
     * @return null
     */
    public static function addMagentoStylesheet($file = null, $theme = 'default', $interface = 'default', $attribs = [])
    {
        if (empty($file)) {
            return;
        }

        if (!preg_match('/^(http|https):\/\//', $file)) {
            $file = MageBridge::getBridge()
                    ->getMagentoUrl() . 'skin/frontend/' . $interface . '/' . $theme . '/css/' . $file;
        }

        $document = JFactory::getDocument();
        $document->addStylesheet($file, 'text/css', null, $attribs);
    }

    /**
     * Function to load a specific stylesheet or script
     *
     * @param string $type
     * @param string $file
     *
     * @return void
     */
    public static function load($type, $file = null)
    {
        // Fetch system-variables
        $template = JFactory::getApplication()
            ->getTemplate();
        $document = JFactory::getDocument();
        $application = JFactory::getApplication();

        // Handle shortcuts to specific scripts or stylesheets
        switch ($type) {
            case 'jquery':

                // Load jQuery through the Google API
                if (MageBridgeModelConfig::load('use_google_api') == 1) {
                    $prefix = (JUri::getInstance()
                        ->isSSL()) ? 'https' : 'http';
                    $document->addScript($prefix . '://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
                } else {
                    YireoHelper::jquery();
                }

                // Set the flag that jQuery has been loaded
                if (method_exists($application, 'set')) {
                    $application->set('jquery', true);
                }

                return;

            case 'jquery-easing':
                self::load('js', 'jquery/jquery.easing.1.3.js');

                return;

            case 'jquery-fancybox':
                self::load('js', 'jquery/jquery.fancybox-1.3.1.pack.js');

                return;

            case 'jquery-mousewheel':
                self::load('js', 'jquery/jquery.mousewheel-3.0.2.pack.js');

                return;

            case 'jquery-carousel':
                self::load('js', 'jquery/jquery.jcarousel.pack.js');

                return;

            case 'jquery-lazyload':
                self::load('jquery');
                self::load('js', 'jquery/jquery.lazyload.mini.js');
                $ll_options = '{effect:"fadeIn", treshhold:20}';
                $ll_elements = 'a.product-image img';
                $ll_script = 'jQuery(document).ready(function($) {jQuery("' . $ll_elements . '").lazyload(' . $ll_options . ');});';
                $document->addCustomTag('<script type="text/javascript">' . $ll_script . '</script>');

                return;
        }

        // Check whether a file of a certain type exists - either as a template override, or as original file
        $path = self::getPath($type, $file);

        if (empty($path)) {
            return;
        }

        // Handle JavaScript
        if ($type == 'js') {
            // Load ProtoType-scripts as a custom tag so it loads after the main library (hopefully)
            if (stristr($path, 'prototype')) {
                $html = '<script type="text/javascript" src="' . $path . '"></script>';
                $document->addCustomTag($html);
            } else {
                $file = basename($path);
                $path = dirname($path);
                $document->addScript($path . '/' . $file);
            }
        } else {
            // Handle CSS
            $document->addStylesheet($path);
        }
    }

    /**
     * Function to get a certain path of a script
     *
     * @param string $type
     * @param string $file
     *
     * @return bool
     */
    public static function getPath($type, $file)
    {
        // Get system variables
        $template = JFactory::getApplication()
            ->getTemplate();

        // Check whether a file of a certain type exists - either as a template override, or as original file
        if (file_exists(JPATH_SITE . '/templates/' . $template . '/' . $type . '/com_magebridge/' . $file)) {
            $path = 'templates/' . $template . '/' . $type . '/com_magebridge/' . $file;
        } else {
            if (file_exists(JPATH_SITE . '/templates/' . $template . '/html/com_magebridge/' . $type . '/' . $file)) {
                $path = 'templates/' . $template . '/html/com_magebridge/' . $type . '/' . $file;
            } else {
                if ($file == 'default.css' && file_exists(JPATH_SITE . '/media/com_magebridge/' . $type . '/default.' . $template . '.css')) {
                    $path = 'media/com_magebridge/' . $type . '/' . 'default.' . $template . '.css';
                } else {
                    if (file_exists(JPATH_SITE . '/media/com_magebridge/' . $type . '/' . $file)) {
                        $path = 'media/com_magebridge/' . $type . '/' . $file;
                    } else {
                        $path = null;
                    }
                }
            }
        }

        $root = JUri::root();

        if (JUri::getInstance()
                ->isSSL() == true
        ) {
            $root = preg_replace('/^http:\/\//', 'https://', $root);
        }

        if (JUri::getInstance()
                ->isSSL() == false
        ) {
            $root = preg_replace('/^https:\/\//', 'http://', $root);
        }

        if (!empty($path)) {
            return $root . $path;
        }

        return null;
    }

    /**
     * Function to get a specific variable
     *
     * @param string $type
     * @param string $file
     *
     * @return string
     */
    public static function get($variable = null)
    {
        switch ($variable) {
            case 'jquery':
                return 'jquery/jquery-1.8.1.min.js';
        }

        return '';
    }

    /**
     * Function to enable debugging of MageBridge templating
     *
     * @param string $type
     * @param string $file
     *
     * @return void
     */
    public static function debug()
    {
        $prototype_loaded = (MageBridgeTemplateHelper::hasPrototypeJs()) ? 'Yes' : 'No';

        JError::raiseNotice('notice', JText::sprintf('View: %s', JFactory::getApplication()->input->getCmd('view')));
        JError::raiseNotice('notice', JText::sprintf('Page layout: %s', MageBridgeTemplateHelper::getPageLayout()));
        JError::raiseNotice('notice', JText::sprintf('Prototype JavaScript loaded: %s', $prototype_loaded));
    }
}
