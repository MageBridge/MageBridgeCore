<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Import the parent class
jimport('joomla.html.parameter.element');

/*
 * Element-class for the path to the Magento Admin Panel
 */
class JElementBackend extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento backend';

    /*
     * Method to get the HTML of this element
     *
     * @param string $name
     * @param string $value
     * @param object $node
     * @param string $control_name
     * @return string
     */
	public function fetchElement($name, $value, &$node, $control_name)
	{
        // Add the control name
        if (!empty($control_name)) $name = $control_name.'['.$name.']';

        // Are the API widgets enabled?
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            $bridge = MageBridgeModelBridge::getInstance();
            $path = $bridge->getMageConfig('backend/path');
            if (!empty($path)) {
                $html = '<input type="text" value="'.$path.'" disabled="disabled" />';
                $html .= '<input type="hidden" name="'.$name.'" value="'.$path.'" />';
                return $html;
            } else {
                MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "backend"' );
            }
        }
        return '<input type="text" name="'.$name.'" value="'.$value.'" />';
    }
}
