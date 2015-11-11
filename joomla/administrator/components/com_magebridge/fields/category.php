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
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Form Field-class for choosing a specific Magento category in a modal box
 */

class MagebridgeFormFieldCategory extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'MageBridge Category';

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
		$id = preg_replace('/([^0-9a-zA-Z]+)/', '_', $name);

		// Are the API widgets enabled?
		if (MagebridgeModelConfig::load('api_widgets') == true)
		{

			// Load the javascript
			JHTML::script('media/com_magebridge/js/backend-elements.js');
			JHTML::_('behavior.modal', 'a.modal');

			$returnType = (string) $this->element['return'];
			$allowRoot = (string) $this->element['allow_root'];

			$title = $value;
			$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
			$link = 'index.php?option=com_magebridge&amp;view=element&amp;tmpl=component&amp;ajax=1';
			$link .= '&amp;type=category&amp;object=' . $id . '&amp;return=' . $returnType;
			$link .= '&amp;allow_root=' . $allowRoot . '&amp;current=' . $value;

			$html = array();
			if (YireoHelper::isJoomla25())
			{
				$html[] = '<div style="float: left;">';
				$html[] = '<input type="text" id="' . $id . '" name="' . $name . '" value="' . $title . '" />';
				$html[] = '</div>';
				$html[] = '<div class="button2-left"><div class="blank">';
				$html[] = '<a class="modal btn" title="' . JText::_('JSELECT') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x:800, y:450}}">' . JText::_('JSELECT') . '</a>';
				$html[] = '</div></div>' . "\n";
			}
			else
			{
				$html[] = '<span class="input-append">';
				$html[] = '<input type="text" class="input-medium" id="' . $id . '" name="' . $name . '" value="' . $title . '" size="35" />';
				$html[] = '<a class="modal btn" role="button" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';
				$html[] = '</span>';
			}

			$html = implode("\n", $html);

			return $html;
		}

		return '<input type="text" name="' . $name . '" value="' . $value . '" />';
	}
}
