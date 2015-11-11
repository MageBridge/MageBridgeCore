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

class Yireo_MageBridge_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Definition of current MageBridge store
     */
    private $magebridge_store = null;

    /*
     * Helper-method to check whether MageBridge is enabled or not
     *
     * @access public
     * @param null
     * @return bool
     */
    public function enabled()
    {
        return (bool)Mage::getStoreConfig('magebridge/settings/active');
    }

    /*
     * Helper-method to check whether this is the Magento frontend or the MageBridge/Joomla frontend
     *
     * @access public
     * @param null
     * @return bool
     */
    public function isBridge()
    {
        $metadata = Mage::getSingleton('magebridge/core')->getMetaData();
        if(empty($metadata)) {
            return false;
        }
        return true;
    }

    /*
     * Helper-method to set the current MageBridge store in the registry
     *
     * @access public
     * @param mixed $store
     * @return null
     */
    public function setStore($store = null)
    {
        $this->magebridge_store = $store;
    }

    /*
     * Helper-method to get the current MageBridge store from the registry
     *
     * @access public
     * @param null
     * @return mixed
     */
    public function getStore()
    {
        $store = $this->magebridge_store;
        if(!empty($store)) {
            return $store;
        }
        return Mage::app()->getStore()->getId();
    }

    /*
     * Helper-method to check whether Joomla! authentication is enabled (used in Yireo_MageBridge_Model_Rewrite_Customer)
     *
     * @access public
     * @param null
     * @return bool
     */
    public function allowJoomlaAuth()
    {
        $joomla_auth = Mage::getStoreConfig('magebridge/joomla/auth');
        if(empty($joomla_auth)) {
            return false;
        }

        return (bool)$joomla_auth;
    }

    /*
     * Helper-method to check whether Joomla! mapping is enabled
     *
     * @access public
     * @param null
     * @return bool
     */
    public function useJoomlaMap()
    {
        $joomla_map = Mage::getStoreConfig('magebridge/joomla/map');
        if(empty($joomla_map)) {
            return false;
        }

        return (bool)$joomla_map;
    }

    /*
     * Helper-method to get the current license key
     *
     * @access public
     * @param null
     * @return string
     */
    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/hidden/support_key');
    }

    /*
     * Helper-method to determine whether to autodetect API-details
     *
     * @access public
     * @param null
     * @return string
     */
    public function useApiDetect()
    {
        return (bool)Mage::getStoreConfig('magebridge/joomla/api_detect', self::getStore());
    }

    /*
     * Helper-method to return the API URL of Joomla!
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiUrl($arguments = null, $store = null)
    {
        if(empty($store)) {
            $store = self::getStore();
        }

        $apiUrl = Mage::getSingleton('magebridge/core')->getMetaData('api_url');
        if(empty($apiUrl)) {
            $apiUrl = Mage::getStoreConfig('magebridge/joomla/api_url', $store);
        } 

        if(is_array($arguments) && !empty($arguments)) {
            foreach($arguments as $argumentName => $argumentValue) {
                if(($argumentName == 'controller' || $argumentName == 'task') && stristr($apiUrl, $argumentName.'=')) {
                    $apiUrl = preg_replace('/'.$argumentName.'=([a-zA-Z0-9]+)/', $argumentName.'='.$argumentValue, $apiUrl);
                } else {
                    $apiUrl .= '&'.$argumentName.'='.$argumentValue;
                }
            }
        }
    
        return $apiUrl;
    }

    /*
     * Helper-method to return the API-username
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiUser($store = null)
    {
        if(empty($store)) {
            $store = self::getStore();
        }

        $value = Mage::getSingleton('magebridge/core')->getMetaData('api_user');
        if(empty($value)) {
            $value = Mage::getStoreConfig('magebridge/joomla/api_user', $store);
        } 
        return $value;
    }

    /*
     * Helper-method to return the API-key (aka API-password)
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiKey($store = null)
    {
        if(empty($store)) {
            $store = self::getStore($store);
        }

        $value = Mage::getSingleton('magebridge/core')->getMetaData('api_key');
        if(empty($value)) {
            $value = Mage::getStoreConfig('magebridge/joomla/api_key', $store);
        } 
        return $value;
    }

    /*
     * Helper-method to return the direct output URLs
     *
     * @access public
     * @param null
     * @return string
     */
    public function getDirectOutputUrls()
    {
        $return = array(
            'rss/catalog',
            'sales/order/print',
            'sales/order/printInvoice',
        );

        $value = Mage::getStoreConfig('magebridge/settings/direct_output', self::getStore());
        if(!empty($value)) {
            $value = str_replace("\n", ',', $value);
            $values = explode(',', $value);
            foreach($values as $value) {
                $value = trim($value);
                if(!empty($value)) {
                    $return[] = $value;
                }
            }
        }
        return $return;
    }

    /*
     * Helper-method to convert a string into a method-name
     *
     * @access public
     * @param string $string 
     * @param string $prefix
     * @return string
     */
    public function stringToSetMethod($string = null, $prefix = 'set')
    {
        $method = '';
        $array = explode('_', $string);
        foreach($array as $i => $a) {
            $method .= ucfirst($a);
        }

        if(preg_match('/^([0-9]+)/', $method) || strlen($method) < 2) {
            return null;
        }

        $method = $prefix.$method;
        return $method;
    } 

    /*
     * Helper-method to quickly write debugging information to a file
     *
     * @access public
     * @param string $message
     * @param mixed $variable
     * @return null
     */
    public function debug($message, $variable = null)
    {
        // Do not write anything to the log if disabled
        if(Mage::getStoreConfig('magebridge/debug/log') == 0) {
            return false;
        }

        // Append the variable if needed
        if(!empty($variable)) {
            $message .= ': '.var_export($variable, true);
        }

        // Add the remote IP if set
        if(isset($_SERVER['REMOTE_ADDR'])) {
            $message = $_SERVER['REMOTE_ADDR'].': '.$message;
        }

        // Define the log-file
        $log_dir = Mage::getBaseDir().DS.'var'.DS.'log';
        $log_file = $log_dir.DS.'magebridge.log';

        // Actually log to the log-file is possible
        if(is_dir($log_dir) == false) @mkdir($log_dir);
        if(is_writable($log_file) || is_writable($log_dir)) {
            $message = $message."\n";
            $message = str_replace("\n\n", "\n", $message);
            @file_put_contents($log_file, $message, FILE_APPEND);
        }
    }
}
