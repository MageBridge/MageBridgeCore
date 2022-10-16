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
 * General helper for usage in Joomla!
 */
class MageBridgeUrlHelper
{
    /**
     * Static variable for the current Magento request
     */
    public static $request = null;

    /**
     * Static variable for the original Magento request
     */
    public static $original_request = null;

    /**
     * Helper-method to reset the current Magento request
     *
     * @param string $request
     *
     * @return bool
     */
    public static function setRequest($request = null)
    {
        $request = trim($request);

        if (empty($request)) {
            return false;
        }

        if ($request == 'magebridge.php') {
            return false;
        }

        self::$request = $request;

        if (empty(self::$original_request)) {
            self::$original_request = $request;
        }

        return true;
    }

    /**
     * Helper-method to determine the current Magento request
     *
     * @return string
     */
    public static function getOriginalRequest()
    {
        return self::$original_request;
    }

    /**
     * Helper-method to determine the current Magento request
     *
     * @return string
     */
    public static function getRequest()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $bridge = MageBridgeModelBridge::getInstance();

        // Always override the current request with whatever comes from the bridge
        self::setRequest($bridge->getSessionData('request', false));

        // If the request is not set by Magento, and if it is not set earlier in MageBridge, set it
        if (empty(self::$request)) {
            // If this is not the frontend, default to the root
            if ($app->isSite() == false) {
                $request = null;
            }
            // If the MageBridge component is not called, default to the root
            else {
                if ($input->getCmd('option') != 'com_magebridge') {
                    $request = null;
                }
                // If the MageBridge component is called, parse the request
                else {
                    if (empty($request)) {
                        $request = $input->getString('request');
                    }

                    // Build a list of current variables
                    $currentVars = ['option', 'view', 'layout', 'format', 'request', 'Itemid', 'lang', 'tmpl'];

                    // Add the form token to current variables
                    $currentsVars[] = JSession::getFormToken();

                    // If the request is set, filter all rubbish
                    if (!empty($request)) {
                        // Parse the current request
                        $request = str_replace('index.php', '', $request);
                        $request = str_replace('//', '/', $request);
                        $request = str_replace('\/', '/', $request);
                        $request = preg_replace('/(SID|sid)=(U|S)/', '', $request);
                        $request = preg_replace('/^\//', '', $request);

                        // Convert the current request into an array (example: /checkout/cart)
                        /**$request_vars = explode('/', preg_replace('/\?([*]+)/', '', $request));
                         * if (!empty($request_vars)) {
                         * foreach ($request_vars as $var) {
                         * $currentVars[] = $var;
                         * }
                         * }*/

                        // Convert the current GET-variables into an array (example: ?limit=25)
                        if (preg_match('/([^\?]+)\?/', $request)) {
                            $query = preg_replace('/([^\?]+)\?/', '', $request);
                            parse_str($query, $query_array);

                            if (!empty($query_array)) {
                                foreach ($query_array as $name => $value) {
                                    $currentVars[] = $name;
                                }
                            }
                        }

                        // Catch illegal debugging entries
                        if (preg_match('/^magebridge\//', $request) && !preg_match('/^magebridge\/output\//', $request) && MageBridgeModelDebug::isDebug() == false) {
                            $request = null;
                        }
                    }

                    // Add custom GET variables
                    $get = [];
                    $getVars = $input->get->getArray();

                    if (!empty($getVars)) {
                        foreach ($getVars as $name => $value) {
                            if (in_array($name, $currentVars)) {
                                continue;
                            }

                            if (preg_match('/^quot;/', $name)) {
                                continue;
                            }

                            if (strlen($name) == 32 && $value == 1) {
                                continue;
                            }

                            $get[$name] = $value;
                        }
                    }

                    if (!empty($get)) {
                        if (strstr($request, '?')) {
                            $request .= http_build_query($get);
                        } else {
                            $request .= '?' . http_build_query($get);
                        }
                    }
                }
            }

            $request = trim($request);
            self::setRequest($request);
        }

        return self::$request;
    }

    /**
     * Helper-method to get a URL replacement for a specific request
     *
     * @return array
     */
    public static function getReplacementUrls()
    {
        static $urls = null;

        if ($urls == null) {
            if (MageBridgeModelConfig::load('load_urls') == 1) {
                $query = "SELECT `id`,`source`,`source_type`,`destination`,`access` FROM #__magebridge_urls WHERE `published` = 1 ORDER BY `ordering`";
                $db = JFactory::getDbo();
                $db->setQuery($query);
                $urls = $db->loadObjectList();
            } else {
                $urls = [];
            }
        }

        return $urls;
    }

    /**
     * Helper-method to get all MageBridge menu-items
     *
     * @param bool $onlyAuthorised
     *
     * @return array
     */
    public static function getMenuItems($onlyAuthorised = true)
    {
        static $items = [];

        if (!empty($items)) {
            return $items;
        }

        $app = JFactory::getApplication();
        $component = JComponentHelper::getComponent('com_magebridge');
        $menu = $app->getMenu('site');

        if (empty($menu)) {
            return [];
        }

        $items = $menu->getItems(['component_id'], [$component->id]);

        // Remove those menu-items that are not authorised
        if ($onlyAuthorised && !empty($items)) {
            foreach ($items as $index => $item) {
                $authorised = $menu->authorise($item->id);

                if ($authorised == false) {
                    unset($items[$index]);
                }
            }
        }

        return $items;
    }

    /**
     * Helper-method to determine whether to enable the Root Menu-Item
     *
     * @param null
     *
     * @return bool
     */
    public static function enableRootMenu()
    {
        if (MageBridgeModelConfig::load('use_rootmenu') == 1) {
            return true;
        }

        return false;
    }

    /**
     * Helper-method to determine whether to enforce the Root Menu-Item
     *
     * @param null
     *
     * @return bool
     */
    public static function enforceRootMenu()
    {
        if (MageBridgeModelConfig::load('enforce_rootmenu') == 1) {
            return true;
        }

        return false;
    }

    /**
     * Helper method to determine the MageBridge Root Menu-Item is set to be default
     *
     * @param null
     *
     * @return int
     */
    public static function isDefault()
    {
        $default = JFactory::getApplication()
            ->getMenu('site')
            ->getDefault();

        if (!empty($default) && $default->link == 'index.php?option=com_magebridge&view=root') {
            return $default->id;
        }

        return false;
    }

    /**
     * @param boolean
     *
     * @return array|null
     */
    public static function getRootItems($onlyAuthorised = true)
    {
        // Load the Root Menu-Items found in the Joomla! database
        static $rootItems = null;

        if (!empty($rootItems)) {
            return $rootItems;
        }

        $items = MageBridgeUrlHelper::getMenuItems($onlyAuthorised);

        if (empty($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (isset($item->query['view']) && $item->query['view'] == 'root') {
                $rootItems[] = $item;
            }
        }

        return $rootItems;
    }

    /**
     * Helper-method to get the Root Menu-Item
     *
     * @param null
     *
     * @return object
     */
    public static function getRootItem()
    {
        // Return false, if Root Menu-Item usage is disabled
        if (MageBridgeModelConfig::load('use_rootmenu') == false) {
            return false;
        }

        $rootItems = self::getRootItems(true);

        $currentItem = MageBridgeUrlHelper::getCurrentItem();

        if (empty($rootItems)) {
            return false;
        }

        // Loop through all Root Menu-Items found, and return the one matching the current ID
        foreach ($rootItems as $rootItem) {
            if (!empty($currentItem) && $rootItem->id == $currentItem->id) {
                return $rootItem;
            }

            if ($rootItem->id == JFactory::getApplication()->input->getInt('Itemid')) {
                return $rootItem;
            }

            if (!empty($currentItem) && is_array($currentItem->tree) && in_array($rootItem->id, $currentItem->tree)) {
                return $rootItem;
            }
        }

        // Return the first Root Menu-Item found
        return $rootItems[0];
    }

    /**
     * Helper-method to get the current Menu-Item
     *
     * @param null
     *
     * @return object
     */
    public static function getCurrentItem()
    {
        static $currentItem = null;

        if (empty($currentItem)) {
            $menu = JFactory::getApplication()
                ->getMenu('site');
            $currentItem = $menu->getActive();

            if (empty($currentItem) || $currentItem->component != 'com_magebridge') {
                $items = MageBridgeUrlHelper::getMenuItems();

                if (!empty($items)) {
                    foreach ($items as $item) {
                        if ($item->id == JFactory::getApplication()->input->getInt('Itemid')) {
                            $currentItem = $item;
                            break;
                        }
                    }
                }
            }
        }

        return $currentItem;
    }

    /**
     * Helper-method to get the specified Menu-Item
     *
     * @param int $id
     *
     * @return object
     */
    public static function getItem($id = 0)
    {
        $items = MageBridgeUrlHelper::getMenuItems();

        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item->id == $id) {
                    if (!isset($item->route)) {
                        $item->route = null;
                    }

                    if (!isset($item->query)) {
                        $item->query = [];
                    }

                    if (!isset($item->query['view'])) {
                        $item->query['view'] = 'root';
                    }

                    if (!isset($item->query['request'])) {
                        $item->query['request'] = null;
                    }

                    if (!isset($item->query['layout'])) {
                        $item->query['layout'] = null;
                    }

                    // If the parameters include the request, set is as query-request
                    if (!empty($item->params)) {
                        if (is_object($item->params)) {
                            $item->params = YireoHelper::toRegistry($item->params);
                        }

                        if (is_object($item->params)) {
                            $item->query['request'] = $item->params->get('request');
                        }
                    }

                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Helper-method to get the current URL
     *
     * @param null
     *
     * @return string
     */
    public static function current()
    {
        return JUri::getInstance()
            ->toString();
    }

    /**
     * Helper-method to strip domains from the URL
     *
     * @param string $url
     *
     * @return string
     */
    public static function stripUrl($url)
    {
        $bridge = MageBridge::getBridge();
        $url = preg_replace('/:(443|80)\//', '/', $url); // Strip any port-number attached to the domain
        $url = str_replace($bridge->getJoomlaBridgeSefUrl(), '', $url); // Strip the Joomla! SEF URL
        $url = str_replace($bridge->getMagentoUrl(), '', $url); // Strip the Magento URL
        $url = preg_replace('/^(http|https):\/\/([a-zA-Z0-9\.\-\_]+)/', '', $url); // Strip all domain-information

        // Extra workaround if Magento hostname is same as current hostname
        $hostname = JUri::getInstance()
            ->toString(['host']);

        if ($hostname == MageBridgeModelConfig::load('host')) {
            $url = str_replace(MageBridgeModelConfig::load('protocol') . '://' . MageBridgeModelConfig::load('host'), '', $url); // Strip the Magento host
        }

        return $url;
    }

    /**
     * Helper-method to get a Joomla! SEF URL
     *
     * @param string $url
     *
     * @return string
     */
    public static function getSefUrl($url)
    {
        if (MageBridgeModelBridge::sh404sef() == true) {
            $oldUrl = $url;
            $newUrl = JRoute::_($oldUrl);

            if (!empty($url)) {
                $url = $newUrl;
                $sh404sef = shGetNonSefURLFromCache($oldUrl, $newUrl);

                if (!$sh404sef) {
                    shAddSefURLToCache($oldUrl, $url, sh404SEF_URLTYPE_CUSTOM);
                }
            }

        // Regular Joomla! SEF
        } else {
            $url = JRoute::_($url);
        }

        return $url;
    }

    /**
     * Helper method to check if the URL-suffix is used in Joomla!
     *
     * @param null
     *
     * @return bool
     */
    public static function hasUrlSuffix()
    {
        $config = JFactory::getConfig();

        if ($config->get('sef') == 1) {
            return (bool) $config->get('sef_suffix');
        }

        return false;
    }

    /**
     * Helper method to only return the Forward SEF option if SEF is actually enabled
     *
     * @param string $layout
     * @param int    $id
     *
     * @return string
     */
    public static function getLayoutUrl($layout = null, $id = null)
    {
        // Set the request based upon the chosen layout
        switch ($layout) {
            case 'search':
                return 'catalogsearch/advanced';

            case 'account':
                return 'customer/account/index';
                break;

            case 'address':
                return 'customer/address';

            case 'orders':
                return 'sales/order/history';

            case 'register':
                return 'customer/account/create';

            case 'login':
                return 'customer/account/login';

            case 'logout':
                return 'customer/account/logout';

            case 'tags':
                return 'tag/customer';

            case 'wishlist':
                return 'wishlist';

            case 'newsletter':
                return 'newsletter/manage/index';

            case 'checkout':
                return 'checkout/onepage';

            case 'cart':
                return 'checkout/cart';

            case 'product':
                if (!is_numeric($id)) {
                    return $id;
                }

                return 'catalog/product/view/id/' . $id . '/';

            case 'addtocart':
                if (!is_numeric($id)) {
                    return $id;
                }

                return 'checkout/cart/add/product/' . $id . '/';

            default:
                if (!is_numeric($id)) {
                    return $id;
                }

                return 'catalog/category/view/id/' . $id . '/';
        }
    }

    /**
     * Helper method to only return the Forward SEF option if SEF is actually enabled
     *
     * @return bool
     */
    public static function getForwardSef()
    {
        $config = JFactory::getConfig();

        if ($config->get('sef') == 1) {
            return 1;
        }

        return 0;
    }

    /**
     * Helper method to get the proper Itemid
     *
     * @return int
     */
    public static function getItemid()
    {
        $rootItem = self::getRootItem();

        if (!empty($rootItem) && $rootItem->id > 0) {
            return $rootItem->id;
        }

        return JFactory::getApplication()->input->getInt('Itemid');
    }

    /**
     * Helper method to generate a MageBridge URL
     *
     * @param string  $request
     * @param boolean $xhtml
     * @param array   $arguments
     *
     * @return string
     */
    public static function route($request = null, $xhtml = true, $arguments = [])
    {
        if (preg_match('/^(http|https):\/\//', $request)) {
            // Try to strip domain part
            $url = JUri::base();
            $request = str_replace($url, '', $request);
            $request = str_replace(str_replace('https://', 'http://', $url), '', $request);
            $request = str_replace(str_replace('http://', 'https://', $url), '', $request);

            return $request;
        }

        $link_to_magento = MageBridgeModelConfig::load('link_to_magento');

        if ($link_to_magento == 1) {
            $bridge = MageBridge::getBridge();
            $config = JFactory::getConfig();

            if ((bool) $config->get('sef_suffix') == true) {
                if (preg_match('/\/$/', $request) == false) {
                    $request .= '.html';
                }
            }

            return $bridge->getMagentoUrl() . $request;
        }

        $enforce_ssl = MageBridgeModelConfig::load('enforce_ssl');

        if ($enforce_ssl == 1 || $enforce_ssl == 2) {
            $ssl = 1;
        } else {
            if ($enforce_ssl == 3 && self::isSSLPage($request)) {
                $ssl = 1;
            } else {
                $ssl = -1;
            }
        }

        $url = 'index.php?option=com_magebridge&view=root&request=' . $request;
        $url .= '&Itemid=' . self::getItemId();

        if (!empty($arguments)) {
            $url .= '&' . http_build_query($arguments);
        }

        return JRoute::_($url, $xhtml, $ssl);
    }

    /**
     * Method to see whether a given page is a secure page
     *
     * @param string $request
     *
     * @return boolean
     */
    public static function isSSLPage($request = null)
    {
        $app = JFactory::getApplication();

        // Check current page
        if ($app->input->getCmd('option') == 'com_magebridge' && $app->input->getCmd('view') == 'content') {
            if (in_array($app->input->getCmd('layout'), ['checkout', 'cart'])) {
                return true;
            }
        }

        // Default pages to be served with SSL
        $pages = [
            'checkout/*',
            'customer/*',
            'wishlist/*',
        ];

        // Extra payment-pages to be served with SSL
        $payment_urls = explode(',', MageBridgeModelConfig::load('payment_urls'));

        if (!empty($payment_urls)) {
            foreach ($payment_urls as $url) {
                $url = trim($url);

                if (!empty($url)) {
                    $pages[] = $url . '/*';
                }
            }
        }

        return MageBridgeTemplateHelper::isPage($pages, $request);
    }
}
