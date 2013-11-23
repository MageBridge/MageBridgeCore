<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Import the parent class
jimport('joomla.html.parameter.element');

/*
 * Element class for selecting Magento websites 
 */
class JElementWebsite extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento website';

    /*
     * Method to construct the HTML of this element
     *
     * @param string $name
     * @param string $value
     * @param object $node
     * @param string $control_name
     * @param string $extra
     * @return string
     */
	public function fetchElement($name, $value = null, $node = null, $control_name = null, $extra = null)
	{
        // Add the control name
        if (!empty($control_name)) $name = $control_name.'['.$name.']';

        // Only build a dropdown when the API-widgets are enabled
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            // Fetch the widget data from the API
            $options = MageBridgeWidgetHelper::getWidgetData('website');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {

                    // Customize the return-value when the XML-attribute "output" is defined
                    if (is_object($node)) {
                        $output = $node->attributes('output');
                        if (!empty($output) && array_key_exists($output, $option)) $option['value'] = $option[$output];
                    }

                    // Customize the label
                    $option['label'] = $option['label'] . ' ('.$option['value'].') ';

                    // Add the option back to the list of options
                    $options[$index] = $option;
                }

                // Return a dropdown list
                array_unshift( $options, array( 'value' => '', 'label' => ''));
                return JHTML::_('select.genericlist', $options, $name, null, 'value', 'label', $value);

            // Fetching data from the bridge failed, so report a warning
            } else {
                MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "website": '.var_export($options, true));
            }
        }

        // Return a simple input-field by default
        return '<input type="text" name="'.$name.'" value="'.$value.'" />';
    }
}
