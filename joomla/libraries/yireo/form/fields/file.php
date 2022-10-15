<?php
/**
 * Joomla! Form Field - Components
 *
 * @author    Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/libraries/joomla/form/fields/file.php';

/**
 * Form Field-class for selecting a component
 */
class YireoFormFieldFile extends JFormFieldFile
{
	/*
	 * Form field type
	 */
	public $type = 'File';

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$accept = !empty($this->accept) ? ' accept="' . $this->accept . '"' : '';
		$size = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled = $this->disabled ? ' disabled' : '';
		$required = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$multiple = $this->multiple ? ' multiple' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		$html = array();
		$html[] = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"' . $accept . ' value="' . $this->value . '"' . $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />';
		$html[] = '<br/><p></p><strong>' . JText::_('Current') . ': </strong><span class="current">' . $this->value . '</span></p>';

		return implode('', $html);
	}
}