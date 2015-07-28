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
 * Bridge helper 
 */
class MageBridgeBridgeHelper
{
	/**
	 * Method to return all cookies that are allowed to pass between Joomla! and Magento
	 *
	 * @access public
	 * @static
	 * @param null
	 * @return array
	 */
	static public function getBridgableCookies()
	{
		// When bridging all cookies, simply collect all names and use them
		$allCookies = MagebridgeModelConfig::load('bridge_cookie_all');
		if($allCookies == 1 && !empty($_COOKIE)) {
			$cookies = array();
			foreach($_COOKIE as $cookieName => $cookieValue) {
				if(preg_match('/^__ut/', $cookieName)) continue;
				if(preg_match('/^PHPSESSID/', $cookieName)) continue;
				$cookies[] = $cookieName;
			}
			return $cookies;
		}

		// Otherwise define a default list of cookies
		$application = JFactory::getApplication();
		if ($application->isSite() == 1 ) {
			$cookies = array('frontend', 'frontend_cid', 'user_allowed_save_cookie', 'persistent_shopping_cart');
		} else {
			$cookies = array('admin');
		}

		// Add the custom cookies to the default list
		$extraCookies = MagebridgeModelConfig::load('bridge_cookie_custom');
		if(!empty($extraCookies)) {
			$extraCookies = explode(',', $extraCookies);
			foreach($extraCookies as $extraCookie) {
				$extraCookie = trim($extraCookie);
				if(!empty($extraCookie)) {
					$cookies[] = $extraCookie;
				}
			}
		}

		return $cookies;
	}
}
