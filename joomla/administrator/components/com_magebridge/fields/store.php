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
 * Form Field-class for selecting Magento stores (with a hierarchy)
 */
class MagebridgeFormFieldStore extends MagebridgeFormFieldAbstract
{
	/**
	 * Form field type
	 */
	public $type = 'Magento store';

	/**
	 * Method to construct the HTML of this element
	 *
	 * @param null
	 * @return string
	 */
	protected function getInput()
	{
		$name = $this->name;
		$fieldName = $name;
		$value = $this->value;

		// Check whether the API widgets are enabled
		if (MagebridgeModelConfig::load('api_widgets') == true) {

			$rows = MageBridgeWidgetHelper::getWidgetData('store');

			// Parse the result into an HTML form-field
			$options = array();
			if (!empty($rows) && is_array($rows)) {
				foreach ($rows as $index => $group) {

					/**if ($group['website'] != MageBridgeModelConfig::load('website')) {
						continue;
					}*/

					$options[] = array(
						'value' => 'g:'.$group['value'].':'.$group['label'],
						'label' => $group['label'] . ' ('.$group['value'].') '
					);

					if (preg_match('/^g\:'.$group['value'].'/', $value)) {
						$value = 'g:'.$group['value'].':'.$group['label'];
					}

					if (!empty($group['childs'])) {
						foreach ($group['childs'] as $child) {
							$options[] = array(
								'value' => 'v:'.$child['value'].':'.$child['label'],
								'label' => '-- '.$child['label'] . ' ('.$child['value'].') ',
							);
	
							if (preg_match('/^v\:'.$child['value'].'/', $value)) {
								$value = 'v:'.$child['value'].':'.$child['label'];
							}
						}
					}
				}

				array_unshift( $options, array( 'value' => '', 'label' => '-- Select --'));
				$extra = null;
				return JHTML::_('select.genericlist', $options, $fieldName, $extra, 'value', 'label', $value);
			} else {
				MageBridgeModelDebug::getInstance()->warning( 'Unable to obtain MageBridge API Widget "store": '.var_export($options, true));
			}
		}

		return '<input type="text" name="'.$fieldName.'" value="'.$value.'" />';
	}
}
