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
 * Form Field-class for the path to the Magento Admin Panel
 */
class MagebridgeFormFieldBackend extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento backend';

    /**
     * Method to get the HTML of this element
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
            $path   = $this->bridge->getSessionData('backend/path');

            if (!empty($path)) {
                $html = '<input type="text" value="' . $path . '" disabled="disabled" />';
                $html .= '<input type="hidden" name="' . $fieldName . '" value="' . $path . '" />';

                return $html;
            }

            $this->debugger->warning('Unable to obtain MageBridge API Widget "backend"');
        }

        return '<input type="text" name="' . $fieldName . '" value="' . $value . '" />';
    }
}
