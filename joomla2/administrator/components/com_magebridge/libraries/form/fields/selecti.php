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

// Require the parent
require_once dirname(__FILE__).'/abstract.php';

/*
 * Form Field-class for adding a select-box that is automatically filled with data
 */
class JFormFieldSelecti extends YireoFormFieldAbstract
{
    /*
     * Form field type
     */
    public $type = 'Select field';
    
    /*
     * Method to get the HTML of this element
     *
     * @param null
     * @return string
     */
    protected function getInput()
    {
        // Initialize the basic variables
        $name           = $this->name;
        $value          = $this->value;
        $fieldName      = $name;
        $fieldId        = $this->getAttribute('id');
        $type           = $this->getAttribute('type');
        $multiple       = $this->getAttribute('multiple');
        $rel            = $this->getAttribute('rel');
        $show_empty     = (bool)$this->getAttribute('show_empty');
        $show_empty_below = (bool)$this->getAttribute('show_empty_below');
        $empty_label    = $this->getAttribute('empty_label');
        $description    = (!empty($this->element['description'])) ? JText::_($this->element['description']) : null;
        $size           = (!empty($this->element['size'])) ? $this->element['size'] : null;
        $required       = (!empty($this->element['required'])) ? $this->element['required'] : null;
        $inputInfo      = $this->getAttribute('inputinfo');
        $class          = (!empty($this->element['class'])) ? $this->element['class'] : 'input' ;

        // Construct the class
        $class .= $inputInfo ? $this->class . ' has-info' : $this->class;

        // Construct the ID
        if (empty($fieldId)) {
            $fieldId = $this->getHtmlId($fieldName);
        }

        // Construct the options
        $options = $this->getOptions();
        
        // Construct the HTML-arguments
        $htmlArguments = array(
            'rel' => $type,
            'name' => $fieldName,
            'id' => $fieldId,
            'value' => $value,
            'multiple' => $multiple,
            'size' => $size,
            'rel' => $rel,
            'class' => $class,
        );
        $htmlArguments = $this->getAttributeString($htmlArguments);
        
        // Construct the template-variables
        $variables = array(
            'name' => $name, 'fieldName' => $fieldName, 'current_value' => $value, 'arguments' => $htmlArguments, 'options' => $options, 
            'show_empty' => $show_empty, 'show_empty_below' => $show_empty_below, 'empty_label' => $empty_label,
        );

        // Output the template
        $html = $this->getTemplate('selecti', $variables);
        return $html;
    }

    /*
     * Method to get the options for this field
     *
     * @param null
     * @return array
     */
    public function getOptions()
    {
        $source = (string)$this->getAttribute('source');
        if (!empty($source)) {
            if (preg_match('/^([a-zA-Z0-9]+)::([a-zA-Z0-9]+)/', $source, $match)) {
                $class = $match[1];
                $method = $match[2];
                $classInstance = new $class();
                $options = $classInstance->$method();
                return $options;
            }
        }

        $options = array();
        foreach ($this->element->children() as $option) {

            if ($option->getName() != 'option') {
                continue;
            }

            $optionArray = array(
                'value' => (string)$option['value'],
                'label' => (string)$option,
                'disabled' => (string)$option['disabled'],
            );
            $options[] = $optionArray;     
        }

        return $options;
    }
}
