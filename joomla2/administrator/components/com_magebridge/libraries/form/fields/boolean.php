<?php
/*
 * Joomla! field
 *
 * @author Yireo (info@yireo.com)
 * @package Yireo Library
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// @bug: jimport() fails here
include_once JPATH_LIBRARIES.'/joomla/form/fields/radio.php';

/*
 * Form Field-class for showing a yes/no field
 */
class JFormFieldBoolean extends JFormFieldRadio
{
    /*
     * Form field type
     */
    public $type = 'Boolean';

    /*
     * Method to construct the HTML of this element
     *
     * @param null
     * @return string
     */
	protected function getInput()
	{
        $this->class = 'radio btn-group btn-group-yesno';
        return parent::getInput();
    }
    
	protected function getOptions()
	{
        $options = array(
            JHtml::_('select.option', '0', JText::_('JNO')),
            JHtml::_('select.option', '1', JText::_('JYES')),
        );
        return $options;
    }
}
