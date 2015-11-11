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
 * Form Field-class for choosing a specific Magento customer-group in a selection-box
 */
class MagebridgeFormFieldUsergroup extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Joomla! usergroup';

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

		$usergroups = MageBridgeFormHelper::getUsergroupOptions();

		$html = null;
		$multiple = (string)$this->element['multiple'];
		if(!empty($multiple)) {
			$size = count($usergroups);
			$html = 'multiple="multiple" size="'.$size.'"';
		}

		$allownone = (bool)$this->element['allownone'];
		if($allownone) {
			array_unshift($usergroups, array('value' => '', 'text' => ''));
		}

		return JHTML::_('select.genericlist', $usergroups, $fieldName, $html, 'value', 'text', $value);
	}
}
