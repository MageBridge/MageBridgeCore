<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class MageBridgeEncryptionHelper - Helper for encoding and encrypting
 *
 * @since 1.0
 */
class MageBridgeEncryptionHelper
{
    /**
     * Simple Base64 encoding
     *
     * @param mixed $string
     *
     * @return string
     * @since 1.0
     */
    public static function base64_encode($string = null)
    {
        return strtr(base64_encode($string), '+/=', '-_,');
    }

    /**
     * Simple Base64 decoding
     *
     * @param mixed $string
     *
     * @return string
     * @since 1.0
     */
    public static function base64_decode($string = null)
    {
        if (!is_string($string)) {
            return null;
        }

        return base64_decode(strtr($string, '-_,', '+/='));
    }

    /**
     * @return mixed
     * @since 1.0
     */
    public static function getEncryptionKey()
    {
        $key = MageBridgeModelConfig::load('encryption_key');

        if (empty($key)) {
            $key = MageBridgeModelConfig::load('supportkey');
        }

        return $key;
    }

    /**
     * Return an encryption key
     *
     * @param string $string
     *
     * @return string
     * @since 1.0
     */
    public static function getSaltedKey($string)
    {
        $key = self::getEncryptionKey();
        $salted = md5($key . $string);

        return $salted;
    }

    /**
     * Encrypt data for security
     *
     * @param mixed $data
     *
     * @return string
     * @since 1.0
     */
    public static function encrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);

        if (empty($data)) {
            return null;
        }

        // Check if encryption was turned off
        if (MageBridgeModelConfig::load('encryption') == 0) {
            return $data;
        }

        // Check if SSL is already in use, so encryption is not needed
        if (MageBridgeModelConfig::load('protocol') == 'https') {
            return $data;
        }

        // Generate a random key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', self::getEncryptionKey(), null, $iv);

        $encoded = MageBridgeEncryptionHelper::base64_encode($encrypted);
        $encodedIv = MageBridgeEncryptionHelper::base64_encode($iv);
        $encodedSum = $encoded . '|=|' . $encodedIv;

        return $encodedSum;
    }

    /**
     * Decrypt data after encryption
     *
     * @param string $data
     *
     * @return mixed
     * @since 1.0
     */
    public static function decrypt($data)
    {
        // Don't do anything with empty data
        $data = trim($data);

        if (empty($data) || (is_string($data) == false && is_numeric($data) == false)) {
            return null;
        }

        // Detect data that is not encrypted
        $decoded = urldecode($data);

        if (strstr($decoded, '|=|') == false) {
            return $data;
        }

        $array = explode('|=|', $decoded);
        $encrypted = self::base64_decode($array[0]);
        $iv = self::base64_decode($array[1]);

        $result = openssl_decrypt($encrypted, 'aes-256-cbc', self::getEncryptionKey(), null, $iv);

        if ($result) {
            return $result;
        }

        return $data;
    }
}
