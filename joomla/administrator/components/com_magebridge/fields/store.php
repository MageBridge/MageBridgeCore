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
 * Form Field-class for selecting Magento stores (with a hierarchy)
 */
class MagebridgeFormFieldStore extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento store';

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

        // Check whether the API widgets are enabled
        if (MageBridgeModelConfig::load('api_widgets') == true) {
            $rows = MageBridgeWidgetHelper::getWidgetData('store');

            // Parse the result into an HTML form-field
            $options = [];
            if (!empty($rows) && is_array($rows)) {
                foreach ($rows as $index => $group) {
                    $options[] = [
                        'value' => 'g:' . $group['value'] . ':' . $group['label'],
                        'label' => $group['label'] . ' (' . $group['value'] . ') ',
                    ];

                    if (preg_match('/^g\:' . $group['value'] . '/', $value)) {
                        $value = 'g:' . $group['value'] . ':' . $group['label'];
                    }

                    if (!empty($group['childs'])) {
                        foreach ($group['childs'] as $child) {
                            $options[] = [
                                'value' => 'v:' . $child['value'] . ':' . $child['label'],
                                'label' => '-- ' . $child['label'] . ' (' . $child['value'] . ') ',
                            ];

                            if (preg_match('/^v\:' . $child['value'] . '/', $value)) {
                                $value = 'v:' . $child['value'] . ':' . $child['label'];
                            }
                        }
                    }
                }

                array_unshift($options, ['value' => '', 'label' => '-- Select --']);
                $extra = null;

                return JHtml::_('select.genericlist', $options, $fieldName, $extra, 'value', 'label', $value);
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "store"', $options);
        }

        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }
}
