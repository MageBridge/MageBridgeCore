<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class for choosing a specific Magento CMS-page
 */
class MagebridgeFormFieldCMSPage extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento CMS Page';

    /**
     * Method to construct the HTML of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $name;
        $value     = $this->value;

        // Only build a dropdown when the API-widgets are enabled
        if ($this->getConfig('api_widgets') == true) {
            // Fetch the widget data from the API
            $options = MageBridgeWidgetHelper::getWidgetData('cmspage');

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    // Customize the return-value when the attribute "output" is defined
                    $output = (string) $this->element['output'];

                    if (!empty($output) && array_key_exists($output, $option)) {
                        $option['value'] = $option[$output];
                    }

                    // Customize the label
                    $option['label'] = $option['label'] . ' (' . $option['value'] . ') ';

                    // Add the option back to the list of options
                    $options[$index] = $option;

                    // Support the new format "[0-9]:(.*)"
                    if (preg_match('/([0-9]+)\:/', $value) == false) {
                        $v = explode(':', $option['value']);

                        if ($v[1] == $value) {
                            $value = $option['value'];
                        }
                    }
                }

                // Return a dropdown list
                array_unshift($options, ['value' => '', 'label' => '']);

                return JHtml::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "cmspage"', $options);
        }

        // Return a simple input-field by default
        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }
}
