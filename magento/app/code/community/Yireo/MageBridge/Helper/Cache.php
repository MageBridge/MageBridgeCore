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

class Yireo_MageBridge_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     * Helper-method to check whether caching is enabled
     *
     * @access public
     * @param null
     * @return bool
     */
    public function enabled()
    {
        if (Mage::helper('magebridge')->isBridge() == false) {
            return false;
        }
        return (bool)Mage::getStoreConfig('magebridge/cache/caching');
    }

    /**
     * Helper-method to return an unique identifier for the current page
     *
     * @access public
     * @param null
     * @return string
     */
    public function getPageId()
    {
        static $id;
        if (empty($id)) {
            $id = Mage::getSingleton('magebridge/core')->getMetaData('request_id');
            if (empty($id)) {
                $currentUrl = Mage::helper('core/url')->getCurrentUrl();
                $get = serialize($_GET);
                $id = md5($currentUrl.$get);
            }
        }
        return $id;
    }

    /**
     * Listen to the event core_block_abstract_to_html_before
     *
     * @access public
     * @parameter Varien_Event_Observer $observer
     * @return $this
     */
    public function allowCaching($block, $page)
    {
        $allowCaching = false;
        $blocksWhitelist = [
            'tags_popular',
            'catalog.product.related',
            'catalog.leftnav',
            'product_tag_list',
            'customer_account_navigation',
            'right.newsletter',
            'left.newsletter',
            'seo.searchterm',
            'top.search',
            'top.menu',
            'head',
        ];

        // Fetch some extra conditions
        $customerLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();

        // All catalog-pages for guest-users
        if ($customerLoggedIn == false && preg_match('/^\/catalog\//', $page) && $block == 'content') {
            $allowCaching = true;

        // All tag-listings for guest-users
        } elseif ($customerLoggedIn == false && preg_match('/^\/tag\/product\/list\//', $page) && $block == 'content') {
            $allowCaching = true;

        // Any block in the whitelist
        } elseif (in_array($block, $blocksWhitelist)) {
            $allowCaching = true;
        }

        return $allowCaching;
    }
}
