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
 * Element-class for choosing a specific Magento widget in a modal box
 */
class JElementWidget extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'Magento widget';

    /*
     * Method to get the HTML of this element
     *
     * @param string $name
     * @param string $value
     * @param object $node
     * @param string $control_name
     * @return string
     */
	public function fetchElement($name, $value, $node = null, $control_name = null)
	{
        if (!empty($control_name)) {
    	    $fieldName	= $control_name.'['.$name.']';
        } else {
            $fieldName = $name;
        }

        // Are the API widgets enabled?
        if (MagebridgeModelConfig::load('api_widgets') == true) {

            // Load the javascript
            JHTML::script('backend-elements.js', 'media/com_magebridge/js/');
	    	JHTML::_('behavior.modal', 'a.modal');
    
            if (!empty($node) && is_object($node)) {
                $returnType = $node->attributes('return');
            } else if (!empty($node) && is_array($node) && !empty($node['return'])) {
                $returnType = $node['return'];
            } else {
                $returnType = 'sku';
            }

            $title = $value;
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
            $link = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;ajax=1&amp;type=widget&amp;object='.$name.'&amp;return='.$returnType.'&amp;current='.$value;

		    $html = '<div style="float: left;">';
            $html .= '<input type="text" id="'.$name.'" name="'.$fieldName.'" value="'.$title.'" />';
            $html .= '</div>';
		    $html .= '<div class="button2-left"><div class="blank">';
            $html .= '<a class="modal" title="'.JText::_('Select a widget').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x:800, y:450}}">'.JText::_('Select').'</a>';
            $html .= '</div></div>'."\n";

            return $html;
        }
        
        return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
    }
}
