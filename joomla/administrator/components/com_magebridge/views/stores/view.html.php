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
defined('_JEXEC') or die();

/**
 * HTML View class
 */
class MageBridgeViewStores extends YireoViewList
{
	/**
	 * Method to prepare the content for display
	 */
	public function display($tpl = null)
	{
		// Fetch the items
		$this->fetchItems();

		// Prepare the items for display
		if (!empty($this->items)) {
			foreach ($this->items as $index => $item) {
				$item->custom_edit_link = 'index.php?option=com_magebridge&view=store&task=edit&cid[]='.$item->id;
				$this->items[$index] = $item;
			}
		}

		// Append the default Store Relation
		$default = $this->getDefault();
		if(!empty($default)) {
			if(empty($this->items)) {
				$this->items = array($default);
			} else {
				array_unshift($this->items, $default);
			}
		}

		parent::display($tpl);
	}

	public function getDefault()
	{
		// Construct the default object
		$default = (object)null;
		$default->id = null;
		$default->name = null;
		$default->title = null;
		$default->type = null;
		$default->connector = null;
		$default->connector_value = null;
		$default->hasState = false;
		$default->hasOrdering = false;
		$default->label = JText::_('JDEFAULT');
		$default->custom_edit_link = 'index.php?option=com_magebridge&view=store&task=default';

		// Load the configuration values
		$storegroup = MageBridgeModelConfig::load('storegroup');
		$storeview = MageBridgeModelConfig::load('storeview');

		if (!empty($storeview)) {
			$default->name = $storeview;
			$default->type = 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_TYPE_VALUE_VIEW';
		} else if (!empty($storegroup)) {
			$default->name = $storegroup;
			$default->type = 'COM_MAGEBRIDGE_VIEW_STORE_FIELD_TYPE_VALUE_GROUP';
		} else {
			$default->name = JText::_('JNONE');
			$default->type = JText::_('JNONE');
			$default->title = JText::_('JNONE');
		}

		// Loop through the API-result just to get the title
		$options = MageBridgeWidgetHelper::getWidgetData('store');
		if (!empty($options)) {
			foreach ($options as $index => $group) {
				if ($default->type == 'Store Group') {
					if ($default->name == $group['value']) {
						$default->title = $group['label'];
						return $default;
					}
				} else {
					foreach ($group['childs'] as $view) {
						if ($default->name == $view['value']) {
							$default->title = $view['label'];
							return $default;
						}
					}
				}
			}
		}

		return $default;
	}
}
