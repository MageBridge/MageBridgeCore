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

class Yireo_MageBridge_Model_Rewrite_Url extends Mage_Core_Model_Url
{
    /*
     * Rewrite of original method
     * 
     * @param   null
     * @return  boolean
     */
    public function getSecure()
    {
        if(Mage::helper('magebridge')->isBridge() == false) {
            return parent::getSecure();
        }
        
        $request = Mage::getSingleton('magebridge/core')->getRequestUrl();
        return $this->isSecurePage($request);
    }

    /*
     * Rewrite of original method
     * 
     * @param   string $routePath
     * @param   array $routeParams
     * @return  string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        if(Mage::helper('magebridge')->isBridge() == false) {
            return parent::getUrl($routePath, $routeParams);
        }
        
        // Set the original URL when dealing with direct download-links
        $bridge_downloads = Mage::app()->getStore()->getConfig('magebridge/settings/bridge_downloads');
        if($bridge_downloads == 0 && preg_match('/(downloadable|\*)\/download\/link/', $routePath)) {
            return $this->setOriginalUrl($routePath, $routeParams);
        }

        // Initialize the parameters if needed
        if(!is_array($routeParams)) {
            $routeParams = array();
        } 

        // Make sure the SID is always removed
        $routeParams['_nosid'] = true;

        // Call the original URL
        $url = parent::getUrl($routePath, $routeParams);

        // Correct HTTP/HTTPS
        if($this->isSecurePage($routePath)) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        // Determine whether to add the Joomla! URL Suffix or not
        static $append_suffix = null;
        if($append_suffix == null) {
            $joomla_sef_suffix = Mage::getSingleton('magebridge/core')->getMetaData('joomla_sef_suffix');
            if($joomla_sef_suffix == 1) {
                $append_suffix = true;
            } else {
                $append_suffix = false;
            }
        }

        // Add the Joomla! URL Suffix if needed
        if($append_suffix) {
            if(!preg_match('/\/$/', $url) && !preg_match('/\.html$/', $url) && !preg_match('/\.html\?/', $url)) {
                if(preg_match('/\?/', $url)) {
                    $url = preg_replace('/([^\/]+)\?/', '$1.html?', $url);
                } else {
                    $url .= '.html';
                }
            }
        }
        return $url;
    }

    /*
     * Helper method
     * 
     * @param   string $routePath
     * @return  boolean
     */
    protected function isSecurePage($routePath = null) 
    {
        $routePath = preg_replace('/\*\//', Mage::app()->getRequest()->getRequestedRouteName().'/', $routePath);
        $routePath = preg_replace('/\/\*\//', Mage::app()->getRequest()->getRequestedControllerName().'/', $routePath);

        $redirect_ssl = Mage::getSingleton('magebridge/core')->getMetaData('enforce_ssl');
        $payment_urls = Mage::getSingleton('magebridge/core')->getMetaData('payment_urls');
        $payment_urls = explode(',', $payment_urls);

        $pages = array(
            'checkout/',
            'firecheckout/',
            'customer/',
            'wishlist/',
        );

        if(!empty($payment_urls)) {
            foreach($payment_urls as $payment_url) {
                $payment_url = trim($payment_url);
                if(!empty($payment_url)) {
                    $pages[] = $payment_url;
                }
            }
        }

        if($redirect_ssl == 1 || $redirect_ssl == 2) {
            return true;
    
        } elseif($redirect_ssl == 3) {
            foreach($pages as $page) {
                if(preg_match('/^'.str_replace('/', '\/', $page).'/', $routePath) == true) {
                    return true;
                }
            }
        }

        return false;
    }

    /*
     * Helper method
     * 
     * @param   string $routePath
     * @param   array $routeParams
     * @return  string
     */
    protected function setOriginalUrl($routePath = null, $routeParams = null)
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
        if($store->getConfig('web/secure/use_in_frontend') == 1 && isset($original_urls['web/secure/base_url'])) {
            $rt = str_replace( $store->getConfig('web/secure/base_url'), $original_urls['web/secure/base_url'], $rt);
        } elseif(isset($original_urls['web/unsecure/base_url'])) {
            $rt = str_replace( $store->getConfig('web/unsecure/base_url'), $original_urls['web/unsecure/base_url'], $rt);
        }

        return $rt;
    }
}
