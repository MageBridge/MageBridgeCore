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
 * Form Field-class for selecting Magento JavaScript scripts
 */
class MagebridgeFormFieldScripts extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento scripts';

	/**
	 * Method to get the HTML of this element
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
			$options = $cache->call(array('MagebridgeFormFieldScripts', 'getResult'));

			// Parse the result into an HTML form-field
			if (!empty($options) && is_array($options)) {
				$current_options = MageBridgeHelper::getDisableJs();
				$size = (count($options) > 10) ? 10 : count($options);
				array_unshift( $options, array( 'value' => '', 'label' => '- '.JText::_('None').' -'));
				return JHTML::_('select.genericlist', $options, $name.'[]', 'multiple="multiple" size="'.$size.'"', 'value', 'label', $current_options);

			} else {
				MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "scripts": '.var_export($options, true));
			}
		}
		return '<input type="text" name="'.$name.'" value="'.$value.'" />';
	}

	/**
	 * Method to get a list of scripts from the API
	 *
	 * @param null
	 * @return array
	 */
	static public function getResult()
	{
		$bridge = MageBridgeModelBridge::getInstance();
		$headers = $bridge->getHeaders();
		if (empty($headers)) {
			// Send the request to the bridge
			$register = MageBridgeModelRegister::getInstance();
			$register->add('headers');

			$bridge->build();
		
			$headers = $bridge->getHeaders();
		}

		$scripts = array();
		if (!empty($headers['items'])) {
			foreach ($headers['items'] as $item) {
				if (strstr($item['type'], 'js')) {
					$scripts[] = array(
						'value' => $item['name'],
						'label' => $item['name'],
					);
				}
			}
		}
		return $scripts;
	}
}
