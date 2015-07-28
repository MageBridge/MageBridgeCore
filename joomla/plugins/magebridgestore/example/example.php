<?php
/**
 * MageBridge Store plugin - Example
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
 * MageBridge Store Plugin - Example
 */
class plgMageBridgeStoreExample extends MageBridgePluginStore
{
	/**
	 * Event "onMageBridgeValidate"
	 * 
	 * @access public
	 * @param array $actions
	 * @param object $condition
	 * @return bool
	 */
	public function onMageBridgeValidate($actions = null, $condition = null)
	{
		// Make sure this plugin is enabled
		if ($this->isEnabled() == false) {
			return false;
		}

		// Make sure to check upon the $actions array to see if it contains the data you need (for instance, defined in form.xml)
		if(!isset($actions['example'])) {
			return false;
		}

		// Return true if this action is correct
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

