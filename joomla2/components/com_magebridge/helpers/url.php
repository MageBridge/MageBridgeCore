<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * General helper for usage in Joomla!
 */
class MageBridgeUrlHelper
{
    /*
     * Static variable for the current Magento request
     */
    public static $request = null;

    /*
     * Helper-method to reset the current Magento request
     *
     * @param string $request
     * @return string
     */
    static public function setRequest($request = null)
    {
        $request = trim($request);
        if (!empty($request)) {
            self::$request = $request;
        }
    }

    /*
     * Helper-method to determine the current Magento request
     *
     * @param string $force_request
     * @return string
     */
    static public function getRequest()
    {
        // Always override the current request with whatever comes from the bridge
        self::setRequest(MageBridgeModelBridge::getInstance()->getMageConfig('request', false));

        // If the request is not set by Magento, and if it is not set earlier in MageBridge, set it
        if (empty(self::$request)) {

            // If this is not the frontend, default to the root
            if (JFactory::getApplication()->isSite() == false) {
                $request = null;

            // If the MageBridge component is not called, default to the root
            } else if (JRequest::getCmd('option') != 'com_magebridge') {
                $request = null;

            // If the MageBridge component is called, parse the request 
            } else {

                if (empty($request)) {
                    $request = JRequest::getString('request');
                }

                // Build a list of current variables
                $current_vars = array('option','view','layout','format','request','Itemid','lang','tmpl');

                // If the request is set, filter all rubbish
                if (!empty($request)) {

                    // Parse the current request
                    $request = str_replace( 'index.php', '', $request );
                    $request = str_replace( '//', '/', $request );
                    $request = str_replace( '\/', '/', $request );
                    $request = preg_replace( '/(SID|sid)=(U|S)/', '', $request );
                    $request = preg_replace( '/^\//', '', $request );

                    // Convert the current request into an array (example: /checkout/cart)
                    /*$request_vars = explode('/', preg_replace('/\?([*]+)/', '', $request));
                    if (!empty($request_vars)) {
                        foreach ($request_vars as $var) {
                            $current_vars[] = $var;
                        }
                    }*/

                    // Convert the current GET-variables into an array (example: ?limit=25)
                    if (preg_match('/([^\?]+)\?/', $request)) {
                        $query = preg_replace('/([^\?]+)\?/', '', $request);
                        parse_str($query, $query_array);
                        if (!empty($query_array)) {
                            foreach ($query_array as $name => $value) {
                                $current_vars[] = $name;
                            }
                        }
                    }

                    // Catch illegal debugging entries
                    if (preg_match('/^magebridge\//', $request) && !preg_match('/^magebridge\/output\//', $request) && MageBridgeModelDebug::isDebug() == false) {
                        $request = null;
                    }
                }

                // Add custom GET variables
                $get = array();
                $get_vars = JRequest::get('get');
                if (!empty($get_vars)) {
                    foreach ($get_vars as $name => $value) {
                        if (!in_array($name, $current_vars) && !preg_match('/^quot;/', $name)) {
                            $get[$name] = $value;
                        }
                    }
                }

                if (!empty($get)) {
                    if (strstr($request, '?')) {
                        $request .= http_build_query($get);
                    } else {
                        $request .= '?'.http_build_query($get);
                    }
                }
            }

            $request = trim($request);
            if(!empty($request)) {
                self::$request = $request;
            }
        }

        return self::$request;
    }

    /*
     * Helper-method to get a URL replacement for a specific request
     *
     * @param string $request
     * @return string
     */
    static public function getReplacementUrls()
    {
        static $urls = null;
        if ($urls == null) {
            if (MagebridgeModelConfig::load('load_urls') == 1) {
                $query = "SELECT `id`,`source`,`source_type`,`destination`,`access` FROM #__magebridge_urls WHERE `published` = 1 ORDER BY `ordering`";
                $db = JFactory::getDBO();
                $db->setQuery($query);
                $urls = $db->loadObjectList();
            } else {
                $urls = array();
            }
        }
        return $urls;
    }

    /*
     * Helper-method to get all MageBridge menu-items
     *
     * @param bool $only_authorised
     * @return array
     */
    static public function getMenuItems($only_authorised = true)
    {
        static $items = array();
        if (empty($items)) {

            //require_once JPATH_SITE.'/includes/application.php'; // 2013-10-13 throws error in J32

            $component = JComponentHelper::getComponent('com_magebridge');
            $menu = JFactory::getApplication()->getMenu('site');

            if (!empty($menu)) {
                if (MageBridgeHelper::isJoomla15()) {
                    $items = $menu->getItems('componentid', $component->id);
                } else {
                    $items = $menu->getItems('component_id', $component->id);
                }
            }

            // Remove those menu-items that are not authorised
            if ($only_authorised && !empty($items)) {
                foreach ($items as $index => $item) {
                    if (MageBridgeHelper::isJoomla15()) {
                        $access = (JFactory::getUser()->guest == 0) ? 1 : 0;
                        $authorised = $menu->authorize($item->id, $access);
                    } else {
                        $authorised = $menu->authorise($item->id);
                    }

                    if ($authorised == false) {
                        unset($items[$index]);
                    }
                }
            }
        }
        return $items;
    }

    /*
     * Helper-method to determine whether to enable the Root Menu-Item
     *
     * @param null
     * @return bool
     */
    static public function enableRootMenu()
    {
        if (MagebridgeModelConfig::load('use_rootmenu') == 1) {
            return true;
        }
        return false;
    }

    /*
     * Helper-method to determine whether to enforce the Root Menu-Item
     *
     * @param null
     * @return bool
     */
    static public function enforceRootMenu()
    {
        if (MagebridgeModelConfig::load('enforce_rootmenu') == 1) {
            return true;
        }
        return false;
    }

    /*
     * Helper method to determine the MageBridge Root Menu-Item is set to be default
     *
     * @param null
     * @return int
     */
    static public function isDefault()
    {
        $default = JFactory::getApplication()->getMenu('site')->getDefault();
        if (!empty($default) && $default->link == 'index.php?option=com_magebridge&view=root') {
            return $default->id;
        }
        return false;
    }

    /*
     * Helper-method to get the Root Menu-Item
     *
     * @param null
     * @return object
     */
    static public function getRootItem()
    {
        // Return false, if Root Menu-Item usage is disabled
        if (MagebridgeModelConfig::load('use_rootmenu') == false) {
            return false;
        }

        // Load the Root Menu-Items found in the Joomla! database
        static $root_items = null;
        if (empty($root_items)) {
            $items = MageBridgeUrlHelper::getMenuItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (isset($item->query['view']) && $item->query['view'] == 'root') {
                        $root_items[] = $item;
                    }
                }
            }
        }

        $current_item = MageBridgeUrlHelper::getCurrentItem();
        if (!empty($root_items)) {

            // Loop through all Root Menu-Items found, and return the one matching the current ID
            foreach ($root_items as $root_item) {
                if (!empty($current_item) && $root_item->id == $current_item->id) {
                    return $root_item;
                } else if ($root_item->id == JRequest::getInt('Itemid')) {
                    return $root_item;
                } else if (!empty($current_item) && is_array($current_item->tree) && in_array($root_item->id, $current_item->tree)) {
                    return $root_item;
                }
            }

            // Return the first Root Menu-Item found
            return $root_items[0];
        }

        return false;
    }

    /*
     * Helper-method to get the current Menu-Item
     *
     * @param null
     * @return object
     */
    static public function getCurrentItem()
    {
        static $current_item = null;
        if (empty($current_item)) {

            $menu = JFactory::getApplication()->getMenu('site');
            $current_item = $menu->getActive();
            if (empty($current_item) || $current_item->component != 'com_magebridge') {
                $items = MageBridgeUrlHelper::getMenuItems();
                if (!empty($items)) {
                    foreach ($items as $item) {
                        if ($item->id == JRequest::getInt('Itemid')) {
                            $current_item = $item;
                            break;
                        }
                    }
                }
            }
        }
        return $current_item;
    }

    /*
     * Helper-method to get the specified Menu-Item
     * 
     * @param int $id
     * @return object
     */
    static public function getItem($id = 0)
    {
        $items = MageBridgeUrlHelper::getMenuItems();
        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item->id == $id) {

                    if (!isset($item->route)) $item->route = null;
                    if (!isset($item->query)) $item->query = array();
                    if (!isset($item->query['view'])) $item->query['view'] = 'root';
                    if (!isset($item->query['request'])) $item->query['request'] = null;
                    if (!isset($item->query['layout'])) $item->query['layout'] = null;

                    // If the parameters include the request, set is as query-request
                    if (!empty($item->params)) {
                        if (is_object($item->params)) $item->params = YireoHelper::toRegistry($item->params);
                        if (is_object($item->params)) $item->query['request'] = $item->params->get('request');
                    }

                    return $item;
                }
            }
        }
        return null;
    }

    /*
     * Helper-method to get the current URL
     * 
     * @param null
     * @return string
     */
    static public function current()
    {
        return JURI::getInstance()->toString();
    }

    /*
     * Helper-method to strip domains from the URL
     *
     * @param string $url
     * @return string
     */
    static public function stripUrl($url)
    {
        $bridge = MageBridge::getBridge();
        $url = preg_replace('/:(443|80)\//', '/', $url); // Strip any port-number attached to the domain
        $url = str_replace($bridge->getJoomlaBridgeSefUrl(), '', $url); // Strip the Joomla! SEF URL
        $url = str_replace($bridge->getMagentoUrl(), '', $url); // Strip the Magento URL
        $url = preg_replace('/^(http|https):\/\/([a-zA-Z0-9\.\-\_]+)/', '', $url); // Strip all domain-information

        // Extra workaround if Magento hostname is same as current hostname
        $hostname = JURI::getInstance()->toString(array('host'));
        if ($hostname == MagebridgeModelConfig::load('host')) {
            $url = str_replace(MagebridgeModelConfig::load('protocol').'://'.MagebridgeModelConfig::load('host'), '', $url); // Strip the Magento host
        }

        return $url;
    }

    /*
     * Helper-method to get a Joomla! SEF URL
     *
     * @param string $url
     * @return string
     */
    static public function getSefUrl($url)
    {
        if ( MageBridgeModelBridge::sh404sef() == true ) {
            $oldurl = $url;
            $newurl = JRoute::_($oldurl);
            if (!empty($url)) {
                $url = $newurl;
                $sh404sef = shGetNonSefURLFromCache($oldurl, $newurl);
                if (!$sh404sef) {
                    shAddSefURLToCache( $oldurl, $url, sh404SEF_URLTYPE_CUSTOM);
                }
            }

        // Regular Joomla! SEF
        } else {
            $url = JRoute::_($url);
        }

        return $url;
    }

    /*
     * Helper method to check if the URL-suffix is used in Joomla!
     *
     * @param null
     * @return bool
     */ 
    static public function hasUrlSuffix()
    {
        $app = JFactory::getApplication();
        if ($app->getCfg('sef') == 1) return (boolean)$app->getCfg('sef_suffix');

        return false;
    }

    /*
     * Helper method to only return the Forward SEF option if SEF is actually enabled
     *
     * @param null
     * @return bool
     */ 
    static public function getLayoutUrl($layout = null, $id = null)
    {
        // Set the request based upon the choosen layout
        switch($layout) {

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
                if (!is_numeric($id)) return $id;
                return 'catalog/product/view/id/'.$id.'/';

            case 'addtocart':
                if (!is_numeric($id)) return $id;
                return 'checkout/cart/add/product/'.$id.'/';

            default:
                if (!is_numeric($id)) return $id;
                return 'catalog/category/view/id/'.$id.'/';
        }
    }

    /*
     * Helper method to only return the Forward SEF option if SEF is actually enabled
     *
     * @param null
     * @return bool
     */ 
    static public function getForwardSef()
    {
        $app = JFactory::getApplication();
        if ($app->getCfg('sef') == 1) {
            return 1;
        }
        return 0;
    }

    /*
     * Helper method to get the proper Itemid
     *
     * @param null
     * @return int
     */ 
    static public function getItemid()
    {
        $root_item = self::getRootItem();
        if (!empty($root_item) && $root_item->id > 0) {
            return $root_item->id;
        }
        
        return JRequest::getInt('Itemid');
    }

    /*
     * Helper method to generate a MageBridge URL
     *
     * @param string $request
     * @param boolean $xhtml
     * @return string
     */ 
    static public function route($request = null, $xhtml = true, $arguments = array())
    {
        $link_to_magento = MagebridgeModelConfig::load('link_to_magento');
        if ($link_to_magento == 1) {
            $bridge = MageBridge::getBridge();
            return $bridge->getMagentoUrl().$request;
        }

        $enforce_ssl = MagebridgeModelConfig::load('enforce_ssl');
        if ($enforce_ssl == 1 || $enforce_ssl == 2) {
            $ssl = 1;
        } else if ($enforce_ssl == 3 && self::isSSLPage($request)) {
            $ssl = 1;
        } else {
            $ssl = -1;
        }

        $url = 'index.php?option=com_magebridge&view=root&request='.$request;
        if(JRequest::getCmd('option') == 'com_magebridge') {
            $url .= '&Itemid='.self::getItemId();
        }

        if(!empty($arguments)) {
            $url .= '&'.http_build_query($arguments);
        }

        return JRoute::_($url, $xhtml, $ssl);
    }

    /**
     * Method to see whether a given page is a secure page
     * 
     * @access public
     * @param string $request
     * @return boolean
     */
    static public function isSSLPage($request = null)
    {
        // Default pages to be served with SSL
        $pages = array(
            'checkout/*',
            'customer/*',
            'wishlist/*',
        );

        // Extra payment-pages to be served with SSL
        $payment_urls = explode(',', MagebridgeModelConfig::load('payment_urls'));
        if (!empty($payment_urls)) {
            foreach ($payment_urls as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $pages[] = $url.'/*';
                }
            }
        }

        return MageBridgeTemplateHelper::isPage($pages, $request);
    }
}
