<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge helper
 */
class MageBridgeBridgeHelper
{
	/**
	 * Method to return all cookies that are allowed to pass between Joomla! and Magento
	 *
	 * @static
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getBridgableCookies()
	{
		// When bridging all cookies, simply collect all names and use them
		$allCookies = MagebridgeModelConfig::load('bridge_cookie_all');

		if ($allCookies == 1 && !empty($_COOKIE))
		{
			$cookies = array();
			foreach ($_COOKIE as $cookieName => $cookieValue)
			{
				if (self::isCookieNameAllowed($cookieName) == false)
				{
					continue;
				}

				$cookies[] = $cookieName;
			}

			return $cookies;
		}

		// Otherwise define a default list of cookies
		$cookies = self::getDefaultCookieNames();

		// Add the custom cookies to the default list
		$extraCookies = self::getCustomCookies();
		$cookies = array_merge($cookies, $extraCookies);

		return $cookies;
	}

	/**
	 * @param $cookieName
	 *
	 * @return bool
	 */
	static public function isCookieNameAllowed($cookieName)
	{
		if (preg_match('/^__ut/', $cookieName))
		{
			return false;
		}

		if (preg_match('/^PHPSESSID/', $cookieName))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return array
	 */
	static public function getCustomCookies()
	{
		$customCookies = MagebridgeModelConfig::load('bridge_cookie_custom');
		$customCookiesArray = array();

		if (!empty($customCookies))
		{
			$customCookies = explode(',', $customCookies);

			foreach ($customCookies as $customCookie)
			{
				$customCookie = trim($customCookie);

				if (!empty($customCookie))
				{
					$customCookiesArray[] = $customCookie;
				}
			}
		}

		return $customCookiesArray;
	}

	/**
	 * @return array
	 */
	static public function getDefaultCookieNames()
	{
		$application = JFactory::getApplication();

		if ($application->isSite() == 1)
		{
			return array('frontend', 'frontend_cid', 'user_allowed_save_cookie', 'persistent_shopping_cart');
		}

		return array('admin');
	}
}
