<?php
/**
 * MageBridgeRedirect
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2014
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * Observer class
 */
class Yireo_MageBridgeRedirect_Model_Observer
{
    /**
     * Event "controller_action_predispatch"
     */
    public function controllerActionPredispatch($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $currentUrl = Mage::app()->getRequest()->getOriginalPathInfo();

        // Check if this is a bridge-request
        if(Mage::helper('magebridge')->isBridge() == true) {
            return $this;
        }
        
        // Check whether redirection is enabled
        if(Mage::helper('magebridgeredirect')->enabled() == false) {
            return false;
        }

        // Fetch the MageBridge Root
        $magebridgeRootUrl = Mage::helper('magebridgeredirect')->getMageBridgeRoot();
        if(empty($magebridgeRootUrl)) {
            return false;
        }

        // Parse request URI
        $currentUrl = str_replace('/index.php/', '/', $currentUrl);
        if(preg_match('/\/$/', $magebridgeRootUrl)) $currentUrl = preg_replace('/^\//', '', $currentUrl);

        // Construct the new URL
        $newUrl = $magebridgeRootUrl.$currentUrl;

        // Redirect
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$newUrl);exit;
    }
}
