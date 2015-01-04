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
 * MageBridge helper for data encryption and decryption
 */
class Yireo_MageBridge_Helper_Encryption extends Mage_Core_Helper_Abstract
{
    /*
     * Get some kind of string that is specific for this host
     *
     * @access public
     * @param string $string
     * @return string 
     */
    public function getSaltedKey($string)
    {
        $key = trim(Mage::getStoreConfig('magebridge/joomla/encryption_key'));
        if(empty($key)) $key = Mage::getSingleton('magebridge/core')->getLicenseKey();
        return md5($key.$string);
    }

    /*
     * Encrypt data for security
     *
     * @access public
     * @param mixed $data
     * @return string 
     */
    public function encrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);
        if(empty($data)) {
            return null;
        }

        // Check if SSL is already in use, so encryption is not needed
        if(Mage::getSingleton('magebridge/core')->getMetaData('protocol') == 'https') {
            return $data;
        }

        // Disable encryption if configured
        if((bool)Mage::getStoreConfig('magebridge/joomla/encryption') == false) {
            return $data;
        }

        // Generate a random key
        $random = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
        $key = Mage::helper('magebridge/encryption')->getSaltedKey($random);

        // PHP 5.5 version
        if(version_compare(PHP_VERSION, '5.5.0') >= 0) {

            try {
                $td = mcrypt_module_open(MCRYPT_CAST_256, '', 'ecb', '');
                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
                mcrypt_generic_init($td, $key, $iv);
                $encrypted = mcrypt_generic($td, $data);
                $encoded = Mage::helper('magebridge/encryption')->base64_encode($encrypted);

            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
                return null;
            }

        } else {

            try {
                $iv = substr($key, 0, mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB));
                $encrypted = @mcrypt_cfb(MCRYPT_CAST_256, $key, $data, MCRYPT_ENCRYPT, $iv);
                $encoded = Mage::helper('magebridge/encryption')->base64_encode($encrypted);

            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
                return null;
            }
        }

        return $encoded.'|=|'.$random;
    }

    /*
     * Decrypt data after encryption
     *
     * @access public
     * @param string $data
     * @return mixed
     */
    public function decrypt($data)
    {
        // Don't do anything with empty data
        if(empty($data) || (is_string($data) == false && is_numeric($data) == false)) {
            return null;
        }

        // Detect data that is not encrypted
        $data = urldecode($data);
        if(strstr($data, '|=|') == false) {
            return $data;
        }

        // This is a serious bug: Base64-encoding can include plus-signs, but JSON thinks these are URL-encoded spaces. 
        // We have to convert them back manually. Ouch! Another solution would be to migrate from JSON to another transport mechanism. Again ouch!
        $data = str_replace(' ', '+', $data);

        // Continue with decryption 
        $array = explode('|=|', $data);
        if(isset($array[0]) && isset($array[1])) {
            $encrypted = Mage::helper('magebridge/encryption')->base64_decode($array[0]);
            $key = Mage::helper('magebridge/encryption')->getSaltedKey($array[1]);
        } else {
            return null;
        }

        try {

            $td = mcrypt_module_open(MCRYPT_CAST_256, '', 'ecb', '');
            $iv = substr($key, 0, mcrypt_get_iv_size(MCRYPT_CAST_256,MCRYPT_MODE_CFB));
            mcrypt_generic_init($td, $key, $iv);
            $decrypted = mdecrypt_generic($td, $encrypted);
            $decrypted = trim($decrypted);
            return $decrypted;

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
            return null;
        }
    }

    /*
     * Simple Base64 encoding 
     *
     * @param mixed $string
     * @return string
     */
    public static function base64_encode($string = null)
    {
        return strtr(base64_encode($string), '+/=', '-_,');
    }

    /*
     * Simple Base64 decoding 
     *
     * @param mixed $string
     * @return string
     */
    public static function base64_decode($string = null)
    {
        return base64_decode(strtr($string, '-_,', '+/='));
    }
}
