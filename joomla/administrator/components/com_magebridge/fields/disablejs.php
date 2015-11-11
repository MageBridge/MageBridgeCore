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
 * Form Field-class 
 */
class MagebridgeFormFieldDisablejs extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'disable_js';

	/**
	 * Method to get the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$options = array(
			array( 'value' => 0, 'text' => JText::_('JNO')),
			array( 'value' => 1, 'text' => JText::_('JYES')),
			array( 'value' => 2, 'text' => JText::_('JONLY')),
			array( 'value' => 3, 'text' => JText::_('JALL_EXCEPT')),
		);

		foreach ($options as $index => $option) {
			$options[$index] = JArrayHelper::toObject($option);
		}

		$current = MagebridgeModelConfig::load('disable_js_all');
		if ($current == 1 || $current == 0) { 
			$disabled = 'disabled="disabled"';
		} else {
			$disabled = null;
		}

		$html = null;
		$html .= JHTML::_('select.radiolist', $options, 'disable_js_all', 'class="btn-group"', 'value', 'text', $current);
		$html .= '<br/><br/>';
		$html .= '<textarea type="text" id="disable_js_custom" name="disable_js_custom" '.$disabled
			. 'rows="5" cols="40" maxlength="255">'
			. MagebridgeModelConfig::load('disable_js_custom')
			. '</textarea>';
		return $html;
	}
}
