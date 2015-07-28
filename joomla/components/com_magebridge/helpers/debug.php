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
 * Helper for dealing with debugging
 */
class MageBridgeDebugHelper
{
	/**
	 * Helper-method to set the debugging information
	 *
	 * @param null
	 * @return null
	 */
	static public function addDebug()
	{
		// Do not add debugging information when posting or redirecting
		if(strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
			return false;
		}

		// Only continue when debugging is enabled
		if (MageBridgeModelDebug::isDebug() && MagebridgeModelConfig::load('debug_bar')) {

			// Load variables
			$debug = MageBridgeModelDebug::getInstance();
			$bridge = MageBridgeModelBridge::getInstance();
			$register = MageBridgeModelRegister::getInstance();
			$original_request = MageBridgeUrlHelper::getOriginalRequest();
			$request = MageBridgeUrlHelper::getRequest();

			// Debug the MageBridge request
			if (MagebridgeModelConfig::load('debug_bar_request')) {
				$url = $bridge->getMagentoUrl().$request;
				if (empty($request)) $request = '[empty]';

				$Itemid = JFactory::getApplication()->input->getInt('Itemid');
				$root_item = MageBridgeUrlHelper::getRootItem();
				$root_item_id = ($root_item) ? $root_item->id : false;
				$menu_message = 'Menu-Item: '.$Itemid;
				if ($root_item_id == $Itemid) $menu_message .= ' (Root Menu-Item)';

				JError::raiseNotice( 'notice', $menu_message);
				JError::raiseNotice( 'notice', JText::sprintf( 'Page request: %s', (!empty($request)) ? $request : '[empty]'));
				JError::raiseNotice( 'notice', JText::sprintf( 'Original request: %s', $bridge->getSessionData('request')));
				JError::raiseNotice( 'notice', JText::sprintf( 'Received request: %s', $bridge->getSessionData('request')));
				JError::raiseNotice( 'notice', JText::sprintf( 'Received referer: %s', $bridge->getSessionData('referer')));
				JError::raiseNotice( 'notice', JText::sprintf( 'Current referer: %s', $bridge->getHttpReferer()));
				JError::raiseNotice( 'notice', JText::sprintf( 'Magento request: <a href="%s" target="_new">%s</a>', $url, $url ));
				JError::raiseNotice( 'notice', JText::sprintf( 'Magento session: %s', $bridge->getMageSession()));

				if (MageBridgeTemplateHelper::isCategoryPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isCategoryPage() == TRUE'));
				if (MageBridgeTemplateHelper::isProductPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isProductPage() == TRUE'));
				if (MageBridgeTemplateHelper::isCatalogPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isCatalogPage() == TRUE'));
				if (MageBridgeTemplateHelper::isCustomerPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isCustomerPage() == TRUE'));
				if (MageBridgeTemplateHelper::isCartPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isCartPage() == TRUE'));
				if (MageBridgeTemplateHelper::isCheckoutPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isCheckoutPage() == TRUE'));
				if (MageBridgeTemplateHelper::isSalesPage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isSalesPage() == TRUE'));
				if (MageBridgeTemplateHelper::isHomePage()) JError::raiseNotice( 'notice', JText::_('MageBridgeTemplateHelper::isHomePage() == TRUE'));
			}

			// Add store information
			if (MagebridgeModelConfig::load('debug_bar_store')) {
				JError::raiseNotice( 'notice', JText::sprintf( 'Magento store loaded: %s (%s)', $bridge->getSessionData('store_name'), $bridge->getSessionData('store_code')));
			}

			// Add category information
			$category_id = $bridge->getSessionData('current_category_id');
			if($category_id > 0) {
				JError::raiseNotice( 'notice', JText::sprintf( 'Magento category: %d', $category_id));
			}

			// Add product information
			$product_id = $bridge->getSessionData('current_product_id');
			if($product_id > 0) {
				JError::raiseNotice( 'notice', JText::sprintf( 'Magento product: %d', $product_id));
			}

			// Add information on bridge-segments
			if (MagebridgeModelConfig::load('debug_bar_parts')) {
				$i = 0;
				$segments = $register->getRegister();
				foreach ($segments as $segment) {
					if (isset($segment['status']) && $segment['status'] == 1) {
						switch ($segment['type']) {
							case 'breadcrumbs': 
							case 'meta': 
							case 'debug': 
							case 'headers': 
							case 'events': 
								JError::raiseNotice('notice', JText::sprintf('Magento [%d]: %s', $i, ucfirst($segment['type'])));
								break;
							case 'api': 
								JError::raiseNotice('notice', JText::sprintf('Magento [%d]: API resource "%s"', $i, $segment['name']));
								break;
							case 'block': 
								JError::raiseNotice('notice', JText::sprintf('Magento [%d]: Block "%s"', $i, $segment['name']));
								break;
							default:
								$name = (isset($segment['name'])) ? $segment['name'] : null;
								$type = (isset($segment['type'])) ? $segment['type'] : null;
								JError::raiseNotice('notice', JText::sprintf('Magento [%d]: type %s, name %s', $i, $type, $name));
								break;
						}
						$i++;
					}
				}
			}
		}
	}
}
