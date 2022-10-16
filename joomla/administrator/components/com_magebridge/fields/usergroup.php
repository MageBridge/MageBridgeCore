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
 * Form Field-class for choosing a specific Magento customer-group in a selection-box
 */
class MagebridgeFormFieldUsergroup extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Joomla! usergroup';

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

        $usergroups = MageBridgeFormHelper::getUsergroupOptions();

        $html     = null;
        $multiple = (string) $this->element['multiple'];

        if (!empty($multiple)) {
            $size = count($usergroups);
            $html = 'multiple="multiple" size="' . $size . '"';
        }

        $allownone = (bool) $this->element['allownone'];

        if ($allownone) {
            array_unshift($usergroups, ['value' => '', 'text' => '']);
        }

        return JHtml::_('select.genericlist', $usergroups, $fieldName, $html, 'value', 'text', $value);
    }
}
