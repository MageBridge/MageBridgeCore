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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Widget Helper
 */
class MageBridgeWidgetHelper
{
	/**
	 * Wrapper-method to get specific widget-data with caching options
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	static public function getWidgetData($name = null)
	{
		switch ($name)
		{
			case 'website':
				$function = 'getWebsites';
				break;

			case 'store':
				$function = 'getStores';
				break;

			case 'cmspage':
				$function = 'getCmspages';
				break;

			case 'customergroup':
				$function = 'getCustomergroups';
				break;

			case 'theme':
				$function = 'getThemes';
				break;

			default:
				return null;
		}

		$cache = JFactory::getCache('com_magebridge.admin');
		$cache->setCaching(0);
		$result = $cache->call(array('MageBridgeWidgetHelper', $function));

		return $result;
	}

	/**
	 * Get a list of websites from the API
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getWebsites()
	{
		return self::getApiData('magebridge_websites.list');
	}

	/**
	 * Get a list of stores from the API
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getStores()
	{
		return self::getApiData('magebridge_storeviews.hierarchy');
	}

	/**
	 * Get a list of CMS pages from the API
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getCmspages()
	{
		return self::getApiData('magebridge_cms.list');
	}

	/**
	 * Get a list of Magento customer-groups from the API
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getCustomergroups()
	{
		return self::getApiData('customer_group.list');
	}

	/**
	 * Get a list of themes from the API
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getThemes()
	{
		return self::getApiData('magebridge_theme.list');
	}

	/**
	 * Get an API-result
	 *
	 * @param null
	 *
	 * @return array
	 */
	static public function getApiData($method)
	{
		$bridge = MageBridgeModelBridge::getInstance();
		$result = $bridge->getAPI($method);

		if (empty($result))
		{
			// Register this request
			$register = MageBridgeModelRegister::getInstance();
			$id = $register->add('api', $method);

			// Build the bridge
			$bridge->build();

			// Send the request to the bridge
			$result = $register->getDataById($id);
		}

		return $result;
	}
}
