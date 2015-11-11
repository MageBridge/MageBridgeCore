<?php
/**
 * MageBridge Product plugin - Example
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Product Plugin - Example
 */
class plgMageBridgeProductExample extends MageBridgePluginProduct
{
	/**
	 * Event "onMageBridgeProductPurchase"
	 * 
	 * @access public
	 * @param array $actions
	 * @param object $user Joomla! user object
	 * @param tinyint $status Status of the current order
	 * @param string $sku Magento SKU
	 * @return bool
	 */
	public function onMageBridgeProductPurchase($actions = null, $user = null, $status = null, $sku = null)
	{
		// Make sure this plugin is enabled
		if ($this->isEnabled() == false) {
			return false;
		}

		// Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
		if(!isset($actions['example'])) {
			return false;
		}

		// Do your stuff after a product has been purchased

		return true;
	}

	/**
	 * Method to execute when this purchase is reversed
	 * 
	 * @param array $actions
	 * @param JUser $user
	 * @param string $sku Magento SKU
	 * @return bool
	 */
	public function onMageBridgeProductReverse($actions = null, $user = null, $sku = null)
	{
		// Make sure this plugin is enabled
		if ($this->isEnabled() == false) {
			return false;
		}

		// Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
		if(!isset($actions['example'])) {
			return false;
		}

		// Do your stuff after a product purchase has been reversed

		return true;
	}

	/**
	 * Method to check whether this plugin is enabled or not
	 *
	 * @param null
	 * @return bool
	 */
	public function isEnabled()
	{
		// Check for the existance of a specific component
		return $this->checkComponent('com_example');
	}
}

