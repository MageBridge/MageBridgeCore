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
 * Form Field-class for selecting a Magento theme
 */
class MagebridgeFormFieldTheme extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento theme';

    /**
     * Method to get the output of this element
     *
     * @param null
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $fieldName = $this->fieldname;
        $value     = $this->value;

        if ($this->getConfig('api_widgets') == true) {
            $options = MageBridgeWidgetHelper::getWidgetData('theme');
            if (!empty($options) && is_array($options)) {
                array_unshift($options, ['value' => '', 'label' => '']);

                return JHtml::_('select.genericlist', $options, $name, null, 'value', 'label', $this->getConfig($fieldName));
            }
        }

        return '<input type="text" name="' . $name . '" value="' . $value . '" />';
    }
}
