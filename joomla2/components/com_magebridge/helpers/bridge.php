<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Bridge helper 
 */
class MageBridgeBridgeHelper
{
    static public function getBridgableCookies()
    {
        $application = JFactory::getApplication();
        if ($application->isSite() == 1 ) {
            $cookies = array('frontend', 'user_allowed_save_cookie', 'persistent_shopping_cart');
        } else {
            $cookies = array('admin');
        }

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
