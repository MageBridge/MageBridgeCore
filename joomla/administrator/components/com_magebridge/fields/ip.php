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
 * Form Field-class for adding an IP
 */
class MagebridgeFormFieldIp extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'IP address';

    /**
     * Method to get the HTML of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $name  = $this->name;
        $value = $this->value;
        $id    = str_replace(']', '', str_replace('[', '_', $name));

        $html = null;
        $html .= '<textarea type="text" id="' . $id . '" name="' . $name . '" ' . 'rows="5" cols="40" maxlength="255">' . $value . '</textarea>';
        $html .= '<button class="btn" onclick="insertIp(\'' . $_SERVER['REMOTE_ADDR'] . '\'); return false;">' . JText::_('COM_MAGEBRIDGE_MODEL_CONFIG_FIELD_DEBUG_IP') . '</button>';
        $html .= '<script>function insertIp(ip) {' . ' jQuery(\'#' . $id . '\').val(ip);' . '}</script>';

        return $html;
    }
}
