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

class YireoFormFieldPublished extends JFormFieldRadio
{
	/*
	 * Form field type
	 */
	public $type = 'Published';

	/**
	 * @param SimpleXMLElement $element
	 * @param mixed            $value
	 * @param null             $group
	 *
	 * @return bool
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$rt = parent::setup($element, $value, $group);

		$this->element['label'] = 'JPUBLISHED';
		$this->element['required'] = 1;
		$this->required = 1;

		return $rt;
	}

	/*
	 * Method to construct the HTML of this element
	 *
	 * @return string
	 */
	protected function getInput()
	{
		$classes = array(
			'radio',
			'btn-group',
			'btn-group-yesno');

		if (in_array($this->fieldname, array('published', 'enabled', 'state')))
		{
			$classes[] = 'jpublished';
		}

		$this->class = implode(' ', $classes);

		return parent::getInput();
	}

	/**
	 * @return array
	 */
	protected function getOptions()
	{
		$options = array(
			JHtml::_('select.option', '0', JText::_('JUNPUBLISHED')),
			JHtml::_('select.option', '1', JText::_('JPUBLISHED')),);

		return $options;
	}
}
