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
class MagebridgeFormFieldStoreview extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento storeview';

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

		if (MagebridgeModelConfig::load('api_widgets') == true) {

			$cache = JFactory::getCache('com_magebridge.admin');
			$options = $cache->call( array( 'JElementStoreview', 'getResult' ));

			// Parse the result into an HTML form-field
			if (!empty($options) && is_array($options)) {

				foreach ($options as $index => $option) {
					if (!isset($option[$return])) $return = 'value';
					$option['label'] = $option['label'] . ' ('.$option[$return].') ';
					$option['value'] = $option[$return];
					$options[$index] = $option;
				}

				array_unshift( $options, array( 'value' => '', 'label' => ''));
				return JHTML::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
			} else {
				MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "storeview": '.var_export($options, true));
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
		$register->add('api', 'magebridge_storeviews.list');

		// Send the request to the bridge
		$bridge = MageBridgeModelBridge::getInstance();
		$bridge->build();
		$result = $bridge->getAPI('magebridge_storeviews.list');
		return $result;
	}
}
