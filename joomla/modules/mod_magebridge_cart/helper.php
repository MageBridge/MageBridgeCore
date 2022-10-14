<?php
/**
 * Joomla! module MageBridge: Shopping Cart
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

class ModMageBridgeCartHelper
{
    /**
     * Method to be called once the MageBridge is loaded
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function register($params = null)
    {
        // Initialize the register
        $register = [];

        $layout = $params->get('layout');
        $layout = preg_replace('/^([^\:]+):/', '', $layout);

        if ($layout == 'native') {
            $register[] = ['api', 'magebridge_session.checkout'];
        } else {
            $register[] = ['block', 'cart_sidebar'];
        }

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

        $layout = $params->get('layout', 'default');
        $layout = preg_replace('/^([^\:]+):/', '', $layout);

        if ($layout) {
            return $bridge->getAPI('magebridge_session.checkout');
        } else {
            $block_name = $params->get('block_name', 'cart_sidebar');

            return $bridge->getBlock($block_name);
        }
    }
}
