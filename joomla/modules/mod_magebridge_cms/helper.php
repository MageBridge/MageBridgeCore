<?php
/**
 * Joomla! module MageBridge: CMS Block
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper-class for the module
 */

class ModMageBridgeCMSHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function register($params = null)
    {
        // Get the block name
        $blockName = $params->get('block');
        $arguments = ['blocktype' => 'cms'];

        // Initialize the register
        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Fetch the content from the bridge
     *
     * @access public
     * @param JRegistry $params
     * @return string
     */
    public static function build($params = null)
    {
        // Get the block name
        $blockName = $params->get('block');
        $arguments = ['blocktype' => 'cms'];

        // Include the MageBridge bridge
        $bridge = MageBridgeModelBridge::getInstance();

        // Load CSS if needed
        if ($params->get('load_css', 1) == 1) {
            $bridge->setHeaders('css');
        }

        // Load JavaScript if needed
        if ($params->get('load_js', 1) == 1) {
            $bridge->setHeaders('js');
        }

        return $bridge->getBlock($blockName, $arguments);
    }
}
