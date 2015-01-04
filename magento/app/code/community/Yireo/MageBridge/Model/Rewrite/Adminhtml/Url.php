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

class Yireo_MageBridge_Model_Rewrite_Adminhtml_Url extends Mage_Adminhtml_Model_Url
{
    /*
     * Rewrite of original method
     * 
     * @param   string $routePath
     * @param   array $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        // Get the original URLs from the registry
        $original_urls = Mage::registry('original_urls');

        // If this value is empty, it is not yet initialized
        if(empty($original_urls)) {
            return parent::getUrl($routePath, $routeParams);
        }

        // Fetch the result from this method
        $rt = parent::getUrl($routePath, $routeParams);

        // Replace the current URL with the original URL
        $store = Mage::app()->getStore();
        if($store->getConfig('web/secure/use_in_adminhtml') == 1 && isset($original_urls['web/secure/base_url'])) {
            $rt = str_replace( $store->getConfig('web/secure/base_url'), $original_urls['web/secure/base_url'], $rt);
        } elseif(isset($original_urls['web/unsecure/base_url'])) {
            $rt = str_replace( $store->getConfig('web/unsecure/base_url'), $original_urls['web/unsecure/base_url'], $rt);
        }

        return $rt;
    }
}
