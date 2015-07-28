<?php
/**
 * MageBridge Store plugin - Nooku
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
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! nooku
 *
 * @package MageBridge
 */
class plgMageBridgeStoreNooku extends MageBridgePluginStore
{
	/**
	 * Deprecated variable to migrate from the original connector-architecture to new Store Plugins
	 */
	protected $connector_field = 'nooku_language';

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

		// Make sure to check upon the $actions array to see if it contains what we need
		if(empty($actions['nooku_language'])) {
			return false;
		}

		// Check if the condition applies
		if ($actions['nooku_language'] == JFactory::getApplication()->input->getCmd('lang')) {
			return true;
		}

		// Return false by default
		return false;
	}

	/**
	 * Method to check whether this plugin is enabled or not
	 *
	 * @param null
	 * @return bool
	 */
	public function isEnabled()
	{
		if (is_dir(JPATH_SITE.'/components/com_nooku')) {
			return true;
		} else {
			return false;
		}
	}
}

