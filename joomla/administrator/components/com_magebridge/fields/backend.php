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
 * Form Field-class for the path to the Magento Admin Panel
 */
class MagebridgeFormFieldBackend extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento backend';

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

		// Are the API widgets enabled?
		if (MagebridgeModelConfig::load('api_widgets') == true) {

			$bridge = MageBridgeModelBridge::getInstance();
			$path = $bridge->getSessionData('backend/path');
			if (!empty($path)) {
				$html = '<input type="text" value="'.$path.'" disabled="disabled" />';
				$html .= '<input type="hidden" name="'.$fieldName.'" value="'.$path.'" />';
				return $html;
			} else {
				MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "backend"' );
			}
		}
		return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
	}
}
