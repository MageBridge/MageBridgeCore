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
class MageBridgeViewProducts extends YireoViewList
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null, $prepare = true)
	{
		// Automatically fetch items, total and pagination - and assign them to the template
		$this->fetchItems();

		// Prepare the items for display
		if (!empty($this->items)) {
			foreach ($this->items as $index => $item) {
				$item->edit_link = 'index.php?option=com_magebridge&view=product&task=edit&cid[]='.$item->id;
				$this->items[$index] = $item;
			}
		}

		parent::display($tpl);
	}
}
