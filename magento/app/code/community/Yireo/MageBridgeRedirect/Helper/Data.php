<?php
/**
 * MageBridgeRedirect
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

class Yireo_MageBridgeRedirect_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to return whether this module is enabled
     *
     * @access public
     * @param null
     * @return boolean
     */
    public function enabled()
    {
        $value = Mage::getStoreConfig('magebridge/redirect/enabled');
        return (bool)$value;
    }

    /*
     * Helper-method to return the MageBridgeRoot URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getMageBridgeRoot()
    {
        $value = trim(Mage::getStoreConfig('magebridge/redirect/magebridge_root'));
        if(!empty($value) && preg_match('/\/$/', $value) == false) $value .= '/';
        return $value;
    }

    /*
     * Helper-method to prevent redirect on specific ip v4 adresses
     *
     * @access public
     * @param null
     * @return boolean
     */
    public function checkIPv4()
    {
        $value = Mage::getStoreConfig('magebridge/redirect/magebridge_ipv4');
        $value = trim($value);
        if(empty($value)) $value = null;

        if($value != null) {
            $ips = explode(PHP_EOL, $value);
            foreach ($ips as $key => &$ip) {
            	$ip = trim($ip);
            	if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            		unset($ips[$key]);
            		continue;
            	}
            }

            $ips = array_values($ips);
            if(in_array($_SERVER['REMOTE_ADDR'], $ips)) {
                return true;
            }
        }

        return false;
    }

}
