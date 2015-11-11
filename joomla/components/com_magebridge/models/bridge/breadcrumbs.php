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

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Main bridge class
 */
class MageBridgeModelBridgeBreadcrumbs extends MageBridgeModelBridgeSegment
{
	/**
	 * Singleton 
	 *
	 * @param string $name
	 * @return object
	 */
	public static function getInstance($name = null)
	{
		return parent::getInstance('MageBridgeModelBridgeBreadcrumbs');
	}

	/**
	 * Load the data from the bridge
	 */
	public function getResponseData()
	{
		return MageBridgeModelRegister::getInstance()->getData('breadcrumbs');
	}

	/**
	 * Method to set the breadcrumbs
	 */
	public function setBreadcrumbs()
	{
		// Only run this once
		static $set = false;
		if ($set == true) {
			return true;
		} else {
			$set = true;
		}

		// Only run this for root-views
		if (JFactory::getApplication()->input->getCmd('view') != 'root') {
			return true;
		}

		// Get variables
		$application = JFactory::getApplication();
		$pathway = $application->getPathway();
		$data = $this->getResponseData();

		// Define empty data 
		if (!is_array($data)) {
			$data = array();
		}

		// Add the shopping-cart to this pathway
		if (MageBridgeTemplateHelper::isCartPage()) {
			$pathway->addItem(JText::_('COM_MAGEBRIDGE_SHOPPING_CART'), MageBridgeUrlHelper::route('checkout/cart'));

		// Add the checkout to this pathway
		} else if (MageBridgeTemplateHelper::isCheckoutPage()) {
			$pathway->addItem(JText::_('COM_MAGEBRIDGE_SHOPPING_CART'), MageBridgeUrlHelper::route('checkout/cart'));
			$pathway->addItem(JText::_('COM_MAGEBRIDGE_CHECKOUT'), MageBridgeUrlHelper::route('checkout'));
		}

		// Remove the first entry which always the homepage
		@array_shift($data);
		if (empty($data)) {
			return true;
		}

		// Loop through the existing pathway-items and collect them
		$pathway_items = array();
		foreach ($pathway->getPathway() as $pathway_item) {
			if(!preg_match('/^(http|https):/', $pathway_item->link)) {
				$pathway_item->link = preg_replace('/\/$/', '', JURI::root()).JRoute::_($pathway_item->link);
			}
			$pathway_items[] = $pathway_item;
		}

		// Actions when we have a root-item
		$rootItem = MageBridgeUrlHelper::getRootItem();
		if($rootItem != false) {

			// Remove the last entry because it always is inaccurate
			@array_pop($pathway_items);

			// Construct the root-item to this pathway
			$root_pathway_item = (object)null;
			if(isset($rootItem->name)) {
				$root_pathway_item->name = JText::_($rootItem->name);
			} else {
				$root_pathway_item->name = JText::_($rootItem->title);
			}
			$root_pathway_item->link = preg_replace('/\/$/', '', JURI::base()).JRoute::_($rootItem->link);

			// Scan the current items to see whether there is a match or not
			$homeMatch = false;
			foreach($pathway_items as $pathway_item) {
				if($pathway_item->link == $root_pathway_item->link || strstr($pathway_item->link, $root_pathway_item->link)) {
					$homeMatch = true;
					break;
				}
			}

			// If this root item is the homepage, skip it also
			if(!empty($rootItem->home) && $rootItem->home == 1) {
				$homeMatch = true;
			}

			// Only add the root, if there is no match yet
			if(!empty($root_pathway_item) && $homeMatch == false) {
				$pathway_items[] = $root_pathway_item;
			}

		// Actions when we do not have a root-item
		} else {
			
			// Remove the first entry because it always is inaccurate
			@array_shift($data);

		}

		// Loop through the Magento data
		foreach ($data as $item) {

			// Do not add the current link
			//if (MageBridgeUrlHelper::current() == $item['link']) continue;
			if(empty($item['link'])) $item['link'] = JURI::current();

			// Loop through the current pathway-items to prevent double links
			if (!empty($pathway_items)) {
				$match = false;
				foreach ($pathway_items as $pathway_item) {
					if (empty($pathway_item) && !is_object($pathway_item)) continue;
					if ($pathway_item->link == $item['link']) $match = true;
				}
				if ($match == true) continue;
			}

			$pathway_item = (object)null;
			$pathway_item->name = JText::_($item['label']);
			$pathway_item->link = $item['link'];
			$pathway_item->magento = 1;
			$pathway_items[] = $pathway_item;
		}

		$pathway->setPathway($pathway_items);

		return true;
	}
}
