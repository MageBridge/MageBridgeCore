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

/*
 * Abstract field-class
 */
class YireoFormFieldAbstract extends JFormField
{
    /*
     * Method to get the template associated with this form-field
     *
     * @param null
     * @return string
     */
    protected function getTemplate($layoutName, $variables)
    {
        // Load the path-handler
        jimport('joomla.filesystem.path');

        // Determine the layout-name
        $overrideName = $this->getAttribute('template');
        if (!empty($overrideName)) $layoutName = $overrideName;
        if (!preg_match('/\.php$/', $layoutName)) $layoutName .= '.php';

        // Load the template script (and allow for overrides)
        $layoutFile = dirname(__FILE__).'/tmpl/'.$layoutName;
        $templateDir = JPATH_THEMES.'/'.JFactory::getApplication()->getTemplate();
        $templateOverride = $templateDir.'/html/form/fields/'.$layoutName;
        if (is_file($templateOverride) && is_readable($templateOverride)) {
            $layoutFile = $templateOverride;
        }

        if (is_file($layoutFile) == false || is_readable($layoutFile) == false) {
            return null;
        }

        // Redefine the variables
        foreach($variables as $name => $value) {
            $$name = $value;
        }

        // Read the template
        ob_start();
        include $layoutFile;
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /*
     * Method to add CSS to this field
     *
     * @param null
     * @return string
     */
    protected function addStylesheet($stylesheet)
    {
        $document = JFactory::getDocument();
        $document->addStylesheet($stylesheet);
    }

    /*
     * Method to add JavaScript to this field
     *
     * @param null
     * @return string
     */
    protected function addScript($script)
    {
        $document = JFactory::getDocument();
        $document->addScript($script);
    }

    /*
     * Method to get the HTML of this element
     *
     * @param null
     * @return string
     */
    protected function getInput()
    {
    }

    /*
     * Method to turn an associative array into an HTML-attribute-string
     *
     * @param null
     * @return string
     */
    protected function getAttributeString($array)
    {
        $strings = array();
        if (!empty($array)) {
            foreach($array as $name => $value) {
                if (is_bool($value)) $value = (int)$value;
                if (empty($value)) continue;
                $strings[] = $name.'="'.$value.'"';
            }
        }
        return implode(' ', $strings);
    }

    /*
     * Method to get the value of a certain attribute
     *
     * @param null
     * @return string
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->element[$name])) {
            return $this->element[$name];
        }
        return null;
    }

    /*
     * Method to get the HTML ID from the HTML name
     *
     * @param null
     * @return string
     */
    public function getHtmlId($name)
    {
        $id = $name;
        if (preg_match('/([a-zA-Z0-9\-\_]+)\[([a-zA-Z0-9\-\_]+)\]/', $id, $match)) {
            $id = $match[1].'_'.$match[2].'_';
        }
        return $id;
    }
}
