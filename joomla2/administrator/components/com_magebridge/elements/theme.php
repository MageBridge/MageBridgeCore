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
 * Element-class for selecting a Magento theme
 */
class JElementTheme extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento theme';

    /*
     * Method to get the output of this element
     *
     * @return string
     */
	public function fetchElement($name, $value, &$node, $control_name)
	{
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            $options = MageBridgeWidgetHelper::getWidgetData('theme');
            if (!empty($options) && is_array($options)) {
                array_unshift( $options, array( 'value' => '', 'label' => '-- Select --'));
                return JHTML::_('select.genericlist', $options, $name, null, 'value', 'label', $value);
            } else {
                MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "theme": '.var_export($options, true));
            }
        }
        return '<input type="text" name="'.$name.'" value="'.$value.'" />';
    }
}
