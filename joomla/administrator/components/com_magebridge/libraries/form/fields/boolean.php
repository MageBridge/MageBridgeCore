<?php
/*
 * Joomla! field
 *
 * @author Yireo (info@yireo.com)
 * @package Yireo Library
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// @bug: jimport() fails here
include_once JPATH_LIBRARIES . '/joomla/form/fields/radio.php';

/*
 * Form Field-class for showing a yes/no field
 */

class YireoFormFieldBoolean extends JFormFieldRadio
{
	/*
	 * Form field type
	 */
	public $type = 'Boolean';

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$rt = parent::setup($element, $value, $group);

		$this->element['description'] = $this->element['label'] . '_DESC';
		$this->description = $this->element['label'] . '_DESC';
		$this->global = (isset($this->element['global'])) ? $this->element['global'] : 0;

		return $rt;
	}

	/*
	 * Method to construct the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$classes = array(
			'radio',
			'btn-group',
			'btn-group-yesno'
		);

		if (in_array($this->fieldname, array('published', 'enabled', 'state')))
		{
			$classes[] = 'jpublished';
		}

		$this->class = implode(' ', $classes);

		return parent::getInput();
	}

	protected function getOptions()
	{
		$options = parent::getOptions();

		if ($this->global != 0)
		{
			array_unshift($options, JHtml::_('select.option', $this->global, JText::_('JGLOBAL')));
		}

		array_unshift($options, JHtml::_('select.option', '1', JText::_('JYES')));
		array_unshift($options, JHtml::_('select.option', '0', JText::_('JNO')));


		return $options;
	}
}
