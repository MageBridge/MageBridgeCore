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
 * MageBridge rewrite of the default success-block
 */
class Yireo_MageBridge_Block_Rewrite_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
    /*
     * Override method to get the correct continue-shopping URL
     *
     * @access public
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = array())
    {
        if(empty($route) && empty($params)) {
            $next_url = Mage::getSingleton('customer/session')->getNextUrl();
            if(!empty($next_url)) {
                return $next_url;
            }
        }

        return parent::getUrl($route, $params);
    }
}
