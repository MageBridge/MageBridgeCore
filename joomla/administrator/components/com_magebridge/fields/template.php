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
 * Form Field-class for selecting a Magento theme
 */
class MagebridgeFormFieldTemplate extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Joomla! template';

	/**
	 * Method to get the output of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$name = $this->name;
		$fieldName = $this->fieldname;
		$value = $this->value;

		require_once(JPATH_ADMINISTRATOR.'/components/com_templates/helpers/templates.php');
		$options = TemplatesHelper::getTemplateOptions(0);

		if (!empty($options) && is_array($options)) {
			array_unshift( $options, array( 'value' => '', 'text' => ''));
			return JHTML::_('select.genericlist', $options, $fieldName, null, 'value', 'text', MagebridgeModelConfig::load($fieldName));
		}

		return '<input type="text" name="'.$name.'" value="'.$value.'" />';
	}
}
