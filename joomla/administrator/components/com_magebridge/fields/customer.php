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
 * Form Field-class for choosing a specific Magento customer in a modal box
 */
class MagebridgeFormFieldCustomer extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento customer';

	/**
	 * Method to get the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$name = $this->name;
		$fieldName = $name;
		$value = $this->value;

		// Are the API widgets enabled?
		if (MagebridgeModelConfig::load('api_widgets') == true) {

			// Load the javascript
			JHTML::script('media/com_magebridge/js/backend-elements.js');
			JHTML::_('behavior.modal', 'a.modal');
	
			$returnType = (string)$this->element['return'];

			$title = $value;
			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
			$link = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;type=customer&amp;object='.$name.'&amp;return='.$returnType.'&amp;current='.$value;

			$html = '<div style="float: left;">';
			$html .= '<input type="text" id="'.$name.'_name" value="'.$title.'" disabled="disabled" />';
			$html .= '</div>';
			$html .= '<div class="button2-left"><div class="blank">';
			$html .= '<a class="modal btn" title="'.JText::_('Select a Customer').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x:800, y:450}}">'.JText::_('Select').'</a>';
			$html .= '</div></div>'."\n";
			$html .= '<input type="hidden" id="'.$name.'_id" name="'.$fieldName.'" value="'.$value.'" />';

			return $html;
		}

		return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
	}
}
