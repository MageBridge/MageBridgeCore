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
include_once JPATH_LIBRARIES.'/joomla/form/fields/text.php';

/*
 * Form Field-class for showing a yes/no field
 */
class YireoFormFieldText extends JFormFieldText
{
	public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $rt = parent::setup($element, $value, $group);

        $this->element['description'] = $this->element['label'].'_DESC';
        $this->description = $this->element['label'].'_DESC';

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
            'inputbox', 
        );
        
        $this->class = $this->class . ' ' . implode(' ', $classes);

        return parent::getInput();
    }
}
