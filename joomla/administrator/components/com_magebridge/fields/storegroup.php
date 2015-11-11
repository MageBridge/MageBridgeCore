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

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class for selecting Magento store-groups
 */
class MagebridgeFormFieldStoregroup extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento storegroup';

	/**
	 * Method to construct the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$name = $this->name;
		$fieldName = $name;
		$value = $this->value;

		// Are the API widgets enabled?
		if (MagebridgeModelConfig::load('api_widgets') == true) {

			$cache = JFactory::getCache('com_magebridge.admin');
			$options = $cache->call( array( 'JFormFieldStoregroup', 'getResult' ));

			// Parse the result into an HTML form-field
			if (!empty($options) && is_array($options)) {
				foreach ($options as $index => $option) {
					$option['label'] = $option['label'] . ' ('.$option['value'].') ';
					$options[$index] = $option;
				}

				array_unshift( $options, array( 'value' => '', 'label' => ''));
				return JHTML::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
			} else {
				MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "storegroup": '.var_export($options, true));
			}
		}
		return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
	}

	/**
	 * Helper-method to get a list of groups from the API
	 *
	 * @param null
	 * @return array
	 */
	public function getResult()
	{
		// Register this request
		$register = MageBridgeModelRegister::getInstance();
		$register->add('api', 'magebridge_storegroups.list');

		// Send the request to the bridge
		$bridge = MageBridgeModelBridge::getInstance();
		$bridge->build();
		$result = $bridge->getAPI('magebridge_storegroups.list');
		return $result;
	}
}
