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

/**
 * Class Yireo_MageBridge_Helper_Encryption
 *
 * MageBridge helper for data encryption and decryption
 */
class Yireo_MageBridge_Helper_Encryption extends Mage_Core_Helper_Abstract
{
    /**
     * @return string
     */
    public function getEncryptionKey()
    {
        $key = trim(Mage::getStoreConfig('magebridge/joomla/encryption_key'));
        if (empty($key)) {
            $key = Mage::getSingleton('magebridge/core')->getLicenseKey();
        }

        return $key;
    }

    /*
     * Get some kind of string that is specific for this host
     *
     * @param string $string
     * @return string
     */
    public function getSaltedKey($string)
    {
        $key = $this->getEncryptionKey();
        $salted = md5($key . $string);
        return $salted;
    }

    /*
     * Encrypt data for security
     *
     * @param mixed $data
     * @return string
     */
    public function encrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);
        if (empty($data)) {
            return null;
        }

        // Check if SSL is already in use, so encryption is not needed
        if (Mage::getSingleton('magebridge/core')->getMetaData('protocol') == 'https') {
            return $data;
        }

        // Disable encryption if configured
        if ((bool)Mage::getStoreConfig('magebridge/joomla/encryption') == false) {
            return $data;
        }

        // Generate a random key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->getEncryptionKey(), null, $iv);

        $encoded = self::base64_encode($encrypted);
        $encodedIv = self::base64_encode($iv);
        $encodedSum = $encoded . '|=|' . $encodedIv;

        return $encodedSum;
    }

    /*
     * Decrypt data after encryption
     *
     * @param string $data
     * @return mixed
     */
    public function decrypt($data)
    {
        // Don't do anything with empty data
        if (empty($data) || (is_string($data) == false && is_numeric($data) == false)) {
            return null;
        }

        // Detect data that is not encrypted
        $decoded = urldecode($data);
        if (strstr($decoded, '|=|') == false) {
            return $data;
        }

        $decoded = str_replace(' ', '+', $decoded);

        // Continue with decryption
        $array = explode('|=|', $decoded);
        if (!isset($array[0]) || !isset($array[1])) {
            return null;
        }

        $encrypted = self::base64_decode($array[0]);
        $iv = self::base64_decode($array[1]);
        $result = openssl_decrypt($encrypted, 'aes-256-cbc', $this->getEncryptionKey(), null, $iv);

        if ($result) {
            return $result;
        }

        return $data;
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
