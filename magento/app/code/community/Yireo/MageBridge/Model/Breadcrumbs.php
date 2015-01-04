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
 * MageBridge model for getting the current breadcrumbs
 */
class Yireo_MageBridge_Model_Breadcrumbs 
{
    /*
     * Method to get the result of a specific API-call
     * 
     * @access public
     * @param null
     * @return array
     */
    public static function getBreadcrumbs()
    {
        // Initializing caching
        if(Mage::app()->useCache('block_html') && Mage::helper('magebridge/cache')->enabled()) {
            $uniquePageId = Mage::helper('magebridge/cache')->getPageId();
            $cacheId = 'magebridge_breadcrumbs_'.$uniquePageId;
            if($cache = Mage::app()->loadCache($cacheId)) {
                $results = unserialize($cache);
                if(!empty($results)) return $results;
            }
        }

        try {
            $controller = Mage::getSingleton('magebridge/core')->getController();
            $controller->getResponse()->clearBody();

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        try {
            $block = $controller->getAction()->getLayout()->getBlock('breadcrumbs');

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get breadcrumbs: '.$e->getMessage());
            return false;
        }

        try {
            if(!empty($block)) {
                $block->toHtml();
                $crumbs = $block->getCrumbs();

                // Save to cache
                if(Mage::app()->useCache('block_html') && Mage::helper('magebridge/cache')->enabled()) {
                    Mage::app()->saveCache(serialize($crumbs), $cacheId, array('block_html'), 86400);
                }

                return $crumbs;
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to set block: '.$e->getMessage());
            return false;
        }
    }
}
