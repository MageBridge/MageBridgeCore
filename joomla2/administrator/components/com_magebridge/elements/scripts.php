<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
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
 * Element-class for selecting Magento JavaScript scripts
 */
class JElementScripts extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento scripts';

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
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            $cache = JFactory::getCache('com_magebridge.admin');
            $options = $cache->call( array( 'JElementScripts', 'getResult' ));

            // Parse the result into an HTML form-field
            if (!empty($options) && is_array($options)) {
                $current_options = MageBridgeHelper::getDisableJs();
                array_unshift( $options, array( 'value' => 'all', 'label' => '- '.JText::_('All scripts').' -'));
                array_unshift( $options, array( 'value' => '', 'label' => '- '.JText::_('No scripts').' -'));
                $size = count($options);
                if ($size > 10) $size = 10;
                return JHTML::_('select.genericlist', $options, $name.'[]', 'multiple size="'.$size.'"', 'value', 'label', $current_options);
            } else {
                MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "scripts": '.var_export($options, true));
            }
        }
        return '<input type="text" name="'.$name.'" value="'.$value.'" />';
    }

    /*
     * Method to get a list of scripts from the API
     *
     * @param null
     * @return array
     */
    public function getResult()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $headers = $bridge->getHeaders();
        if (empty($headers)) {
            // Send the request to the bridge
            $register = MageBridgeModelRegister::getInstance();
            $register->add('headers');

            $bridge->build();
        
            $headers = $bridge->getHeaders();
        }

        $scripts = array();
        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                if (strstr($item['type'], 'js')) {
                    $scripts[] = array(
                        'value' => $item['name'],
                        'label' => $item['name'],
                    );
                }
            }
        }
        return $scripts;
    }
}
