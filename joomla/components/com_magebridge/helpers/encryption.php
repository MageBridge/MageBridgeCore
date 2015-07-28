<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
		
/**
 * Helper for encoding and encrypting
 */
class MageBridgeEncryptionHelper 
{
	/**
	 * Simple Base64 encoding 
	 *
	 * @param mixed $string
	 * @return string
	 */
	public static function base64_encode($string = null)
	{
		return strtr(base64_encode($string), '+/=', '-_,');
	}

	/**
	 * Simple Base64 decoding 
	 *
	 * @param mixed $string
	 * @return string
	 */
	public static function base64_decode($string = null)
	{
		if (!is_string($string)) return null;
		return base64_decode(strtr($string, '-_,', '+/='));
	}

	/**
	 * Return an encryption key
	 *
	 * @param string $string
	 * @return string
	 */
	public static function getSaltedKey($string)
	{
		$key = MagebridgeModelConfig::load('encryption_key');
		if(empty($key)) $key = MagebridgeModelConfig::load('supportkey');
		return md5($key.$string);
	}

	/**
	 * Encrypt data for security
	 *
	 * @param mixed $data
	 * @return string
	 */
	public static function encrypt($data)
	{
		// Don't do anything with empty data
		$data = trim($data);
		if (empty($data)) {
			return null;
		}

		// Check if encryption was turned off
		if (MagebridgeModelConfig::load('encryption') == 0) {
			return $data;
		}

		// Check if SSL is already in use, so encryption is not needed
		if (MagebridgeModelConfig::load('protocol') == 'https') {
			return $data;
		}

		// Check for mcrypt
		if (!function_exists('mcrypt_get_iv_size') || !function_exists('mcrypt_cfb')) {
			return $data;
		}

		// Generate a random key
		$random = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
		$key = MageBridgeEncryptionHelper::getSaltedKey($random);

		try {
			$td = mcrypt_module_open(MCRYPT_CAST_256, '', 'ecb', '');
			$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
			mcrypt_generic_init($td, $key, $iv);
			$encrypted = mcrypt_generic($td, $data);
			$encoded = MageBridgeEncryptionHelper::base64_encode($encrypted);

		} catch(Exception $e) {
			Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
			return null;
		}

		return $encoded.'|=|'.$random;
	}

	/**
	 * Decrypt data after encryption
	 *
	 * @param string $data
	 * @return mixed
	 */
	public static function decrypt($data)
	{
		// Don't do anything with empty data
		$data = trim($data);
		if (empty($data) || (is_string($data) == false && is_numeric($data) == false)) {
			return null;
		}

		// Detect data that is not encrypted
		$data = urldecode($data);
		if (strstr($data, '|=|') == false) {
			return $data;
		}

		$array = explode( '|=|', $data);
		$encrypted = MageBridgeEncryptionHelper::base64_decode($array[0], true);
		$key = MageBridgeEncryptionHelper::getSaltedKey($array[1]);

		// PHP 5.5 version
		if(version_compare(PHP_VERSION, '5.5.0') >= 0) {

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

		} else {

			try {
				$iv = substr($key, 0, mcrypt_get_iv_size(MCRYPT_CAST_256,MCRYPT_MODE_CFB));
				$decrypted = @mcrypt_cfb(MCRYPT_CAST_256, $key, $encrypted, MCRYPT_DECRYPT, $iv);
				$decrypted = trim($decrypted);
				return $decrypted;

			} catch(Exception $e) {
				Mage::getSingleton('magebridge/debug')->error("Error while decrypting: ".$e->getMessage());
				return null;
			}
		}
	}
}
