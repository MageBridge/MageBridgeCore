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
class MageBridgeViewUsergroups extends YireoViewList
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// Fetch the items
		$this->fetchItems();

		// Prepare the items for display
		if (!empty($this->items)) {
			foreach ($this->items as $index => $item) {
				$item->magento_group_label = $this->getCustomergroupLabel($item->magento_group);
				$item->joomla_group_label = $this->getUsergroupLabel($item->joomla_group);
				$this->items[$index] = $item;
			}
		}

		parent::display($tpl);
	}

	public function getCustomergroupLabel($magento_group)
	{
		$customergroups = MageBridgeWidgetHelper::getWidgetData('customergroup');
		if(!empty($customergroups)) {
			foreach($customergroups as $customergroup) {
				if($customergroup['customer_group_id'] == $magento_group) {
					return $customergroup['customer_group_code'];
				}
			}
		}
	}

	public function getUsergroupLabel($joomla_group)
	{
		$usergroups = MageBridgeFormHelper::getUsergroupOptions();
		if(!empty($usergroups)) {
			foreach($usergroups as $usergroup) {
				if($usergroup->value == $joomla_group) {
					return $usergroup->text;
				}
			}
		}
	}
}
