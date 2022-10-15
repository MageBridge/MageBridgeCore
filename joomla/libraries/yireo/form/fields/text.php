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
include_once JPATH_LIBRARIES . '/joomla/form/fields/text.php';

/*
 * Form Field-class for showing a yes/no field
 */
class YireoFormFieldText extends JFormFieldText
{
	/**
	 * @param SimpleXMLElement $element
	 * @param mixed            $value
	 * @param null             $group
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$rt = parent::setup($element, $value, $group);

		$label = (string) $this->element['label'];

		if (empty($label))
		{
			$option = JFactory::getApplication()->input->getCmd('option');
			$prefix = $option;

			if ($option == 'com_plugins')
			{
				$prefix = $this->form->getData()
					->get('name');
			}

			$label = strtoupper($prefix . '_' . $this->fieldname);
		}

		$this->element['label'] = $label;
		$this->element['description'] = $label . '_DESC';
		$this->description = $label . '_DESC';

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
			'inputbox',);

		$this->class = $this->class . ' ' . implode(' ', $classes);

		return parent::getInput();
	}
}
