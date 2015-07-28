<?php
/**
 * Joomla! component MageBridge
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for usage in Joomla!/MageBridge plugins
 */

class MageBridgePluginHelper
{
	/**
	 * Helper-method to determine if it's possible to run this event
	 *
	 * @param string $event
	 * @param array $options
	 * @return bool
	 */
	static public function allowEvent($event, $options = array())
	{
		static $denied_events = array();

		// Do not run this event if the bridge itself is offline
		if (MageBridge::getBridge()->isOffline())
		{
			MageBridgeModelDebug::getInstance()->notice("Plugin helper detects bridge is offline");

			return false;
		}

		// Do not run this event if the option "disable_bridge" is set to true
		if (isset($options['disable_bridge']) && $options['disable_bridge'] == true)
		{
			MageBridgeModelDebug::getInstance()->notice("Plugin helper detects event '$event' is currently disabled");

			return false;
		}

		// Do not execute additional plugin-events on the success-page (to prevent refreshing)
		$request = MageBridgeUrlHelper::getRequest();

		if (preg_match('/checkout\/onepage\/success/', $request))
		{
			MageBridgeModelDebug::getInstance()->notice("Plugin helper detects checkout/onepage/success page");

			return false;
		}

		// Do not execute this event if we are in XML-RPC or JSON-RPC
		if (MageBridge::isApiPage() == true)
		{
			return false;
		}

		// Check if this event is the list of events already thrown
		if (in_array($event, $denied_events))
		{
			MageBridgeModelDebug::getInstance()->notice("Plugin helper detects event '$event' is already run");

			return false;
		}

		MageBridgeModelDebug::getInstance()->notice("Plugin helper allows event '$event'");
		$denied_events[] = $event;

		return true;
	}
}
