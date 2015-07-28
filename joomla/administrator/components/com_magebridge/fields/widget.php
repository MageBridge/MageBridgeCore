<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class for choosing a specific Magento widget in a modal box
 */
class MagebridgeFormFieldWidget extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento widget';

	/**
	 * Method to get the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$name = $this->name;
		$value = $this->value;

		// Are the API widgets enabled?
		if (MagebridgeModelConfig::load('api_widgets') == true) {

			// Load the javascript
			JHTML::script('media/com_magebridge/js/backend-elements.js');
			JHTML::_('behavior.modal', 'a.modal');
	
			$title = $value;
			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
			$link = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;ajax=1&amp;type=widget&amp;object='.$name.'&amp;current='.$value;

			$html = '<div style="float: left;">';
			$html .= '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$title.'" />';
			$html .= '</div>';
			$html .= '<div class="button2-left"><div class="blank">';
			$html .= '<a class="modal btn" title="'.JText::_('Select a Widget').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x:800, y:450}}">'.JText::_('Select').'</a>';
			$html .= '</div></div>'."\n";

			return $html;
		}
		
		return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
	}
}
