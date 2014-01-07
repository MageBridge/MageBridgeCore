<?php
/**
 * Joomla! module MageBridge: Progress Block
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Helper-class for the module
 */
class modMageBridgeProgressHelper
{
    /*
     * Method to be called once the MageBridge is loaded
     *
     * @access public
     * @param JParameter $params
     * @return array
     */
    static public function register($params = null)
    {
        // Initialize the register 
        $register = array();
        $register[] = array('block', 'checkout.progress');

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = array('headers');
        }

        return $register;
    }

    /*
     * Fetch the content from the bridge
     * 
     * @access public
     * @param JParameter $params
     * @return string
     */
    static public function build($params = null)
    {
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

        return $bridge->getBlock('checkout.progress');
    }
}
