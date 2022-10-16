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
 * Form Field-class for selecting Magento store-groups
 */
class MagebridgeFormFieldStoregroup extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento storegroup';

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

        // Are the API widgets enabled?
        if ($this->getConfig('api_widgets') == true) {
            $cache   = JFactory::getCache('com_magebridge.admin');
            $options = $cache->call(['JFormFieldStoregroup', 'getResult']);

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                foreach ($options as $index => $option) {
                    $option['label'] = $option['label'] . ' (' . $option['value'] . ') ';
                    $options[$index] = $option;
                }

                array_unshift($options, ['value' => '', 'label' => '']);

                return JHtml::_('select.genericlist', $options, $fieldName, null, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "storegroup"', $options);
        }

        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }

    /**
     * Helper-method to get a list of groups from the API
     *
     * @return array
     */
    public function getResult()
    {
        // Register this request
        $this->register->add('api', 'magebridge_storegroups.list');

        // Send the request to the bridge
        $this->bridge->build();
        $result = $this->bridge->getAPI('magebridge_storegroups.list');

        return $result;
    }
}
