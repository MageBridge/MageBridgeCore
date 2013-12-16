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
 * Element-class for selecting Magento CSS-stylesheets
 */
class JElementStylesheets extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento stylesheets';

    /*
     * Method to get the output of this element
     *
     * @return string
     */
	public function fetchElement()
	{
        $options = null;
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            $cache = JFactory::getCache('com_magebridge.admin');
            $options = $cache->call( array( 'JElementStylesheets', 'getResult' ));

            if (empty($options) && !is_array($options)) {
                MageBridgeModelDebug::getInstance()->trace('Unable to obtain MageBridge API Widget "stylesheets"', $options);
            }
        }
    		
        MageBridgeTemplateHelper::load('jquery');
        JHTML::script('backend-customoptions.js', 'media/com_magebridge/js/');

        $html = '';
        $html .= JElementStylesheets::getRadioHTML();
        $html .= '<p/>';
        $html .= JElementStylesheets::getSelectHTML($options);
        return $html;
    }

    /*
     * Method to get the HTML of the disable_css_mage element
     *
     * @param string $name
     * @param array $options
     * @param array $current_options
     * @return string
     */
    public function getRadioHTML()
    {
        $name = 'disable_css_all';
        $value = MagebridgeModelConfig::load('disable_css_all');
        $options = array(
            array('value' => '0', 'label' => JText::_('No')),
            array('value' => '1', 'label' => JText::_('Yes')),
            array('value' => '2', 'label' => JText::_('Only')),
            array('value' => '3', 'label' => JText::_('All except')),
        );

        foreach ($options as $index => $option) {
            $options[$index] = JArrayHelper::toObject($option);
        }

        return JHTML::_('select.radiolist', $options, $name, null, 'value', 'label', $value);
    }

    /*
     * Method to get the HTML of the disable_css_all element
     *
     * @param string $name
     * @param array $options
     * @param array $current_options
     * @return string
     */
    public function getSelectHTML($options)
    {
        $name = 'disable_css_mage';
        $value = MageBridgeHelper::getDisableCss();

        $current = MagebridgeModelConfig::load('disable_css_all');
        if ($current == 1 || $current == 0) { 
            $disabled = ' disabled="disabled"';
        } else {
            $disabled = null;
        }

        if (!empty($options) && is_array($options)) {
            array_unshift( $options, array( 'value' => '', 'label' => '- '.JText::_('None').' -'));
            $size = count($options);
            if ($size > 10) $size = 10;
            return JHTML::_('select.genericlist', $options, $name.'[]', 'multiple size="'.$size.'"'.$disabled, 'value', 'label', $value);
        }

        return '<input type="text" name="'.$name.'" value="'.implode(',', $value).'" />';
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

        $stylesheets = array();
        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                if (strstr($item['type'], 'css')) {
                    $stylesheets[] = array(
                        'value' => $item['name'],
                        'label' => $item['name'],
                    );
                }
            }
        }
        return $stylesheets;
    }
}
