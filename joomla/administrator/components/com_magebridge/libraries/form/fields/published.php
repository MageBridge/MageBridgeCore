<?php
/*
 * Joomla! field
 *
 * @author Yireo (info@yireo.com)
 * @package Yireo Library
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// @bug: jimport() fails here
include_once JPATH_LIBRARIES.'/joomla/form/fields/radio.php';

/*
 * Form Field-class for showing a yes/no field
 */
class YireoFormFieldPublished extends JFormFieldRadio
{
    /*
     * Form field type
     */
    public $type = 'Published';

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
        
        if (in_array($this->fieldname, array('published', 'enabled', 'state'))) {
            $classes[] = 'jpublished';
        }

        $this->class = implode(' ', $classes);

        return parent::getInput();
    }
    
	protected function getOptions()
	{
        $options = array(
            JHtml::_('select.option', '0', JText::_('JUNPUBLISHED')),
            JHtml::_('select.option', '1', JText::_('JPUBLISHED')),
        );
        return $options;
    }
}
