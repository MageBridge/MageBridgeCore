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

// Load the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * Function to convert a system URL to a SEF URL
 */
function MagebridgeBuildRoute(&$query)
{
	// If there's only an Itemid (and an option), skip because this Menu-Item is fine already
	if (isset($query['Itemid']) && count($query) <= 2 && MageBridgeUrlHelper::enforceRootMenu() == false) {
		return array();
	}

	// Initialize some parts
	$segments = array();
	$Itemid = isset($query['Itemid']) ? $query['Itemid'] : 0;
	$orig_Itemid = $Itemid;

	// Get the menu items for this component
	$items = MageBridgeUrlHelper::getMenuItems();
	$current_item = MageBridgeUrlHelper::getItem($Itemid);

	// Strip the slug
	if (!empty($query['request']) && preg_match('/^([0-9]+)\:(.*)/', $query['request'], $match)) {
		$query['id'] = $match[1];
		$query['request'] = $match[2];
	}

	// Try to match the current query with a Menu-Item 
	if (!empty($items)) {

		foreach ($items as $item) {

			// Match a specific combination of view-layout-request
			if (!empty($item->query['request']) && !empty($query['request'])
				&& isset($item->query['view']) && isset($query['view']) && $item->query['view'] == $query['view']
				&& isset($item->query['layout']) && isset($query['layout']) && $item->query['layout'] == $query['layout']) {

				// Match a specific combination of view-layout-request (string)
				if ($item->query['request'] == $query['request']) {
					$query = array('option' => 'com_magebridge', 'Itemid' => $item->id);
					return array();

				// Match a specific combination of view-layout-request (ID)
				} else if (isset($query['id']) && $item->query['request'] == $query['id']) {
					$query = array('option' => 'com_magebridge', 'Itemid' => $item->id);
					return array();
				}

			// Match a specific combination of view-layout
			} else if (empty($query['request']) 
				&& isset($item->query['view']) && isset($query['view']) && $item->query['view'] == $query['view']
				&& isset($item->query['layout']) && isset($query['layout']) && $item->query['layout'] == $query['layout']) {
				$query = array('option' => 'com_magebridge', 'Itemid' => $item->id);
				return array();
			}
		}
	}

	// Fetch the Root-Item
	$query_option = (isset($query['option'])) ? $query['option'] : null;
	$query_view = (isset($query['view'])) ? $query['view'] : null;
	if($query_option == 'com_magebridge' && $query_view == 'root' && !empty($query['Itemid'])) {
		$root_item = false;
		$root_item_id = false;
	} else {
		$root_item = MageBridgeUrlHelper::getRootItem();
		$root_item_id = ($root_item && $root_item->id > 0) ? $root_item->id : false;
	}

	// Set a default empty view
	if (!isset($query['view'])) {
		$query['view'] = null;
	}

	// Reset fake views (used by JCE editor)
	if (in_array($query['view'], array('product', 'category'))) {
		$query['view'] = 'root';
	}

	// If there is a root-item (and therefor "use_rootmenu" is enabled), see if we need to replace the current URL with the root-items URL
	if ($root_item_id > 0) {

		// If there is a root-view or when "enforce_rootmenu" is enabled, reset the Itemid to the Root Menu-Item
		if ($query['view'] == 'root' || MageBridgeUrlHelper::enforceRootMenu()) {
			$query['Itemid'] = $root_item_id;
		}

		// Build the Magento request based upon the current Menu-Item
		if (!empty($current_item)) {

			// Get data from the current Menu-Item
			$cparams = YireoHelper::toRegistry($current_item->params);
			$cquery = $current_item->query;

			// Complete the Magento request if it is still empty
			if (empty($query['request']) && $query['Itemid'] == $root_item_id) {

				// Determine the request if set in the $query['link']
				if (empty($cquery['request'])) {
					parse_str(preg_replace('/^index.php\?/', '', $current_item->link), $link);
					if (!empty($link['request'])) {
						$cquery['request'] = $link['request']; 
					}
				}

				// Use the MVC-layout to determine the request
				if (!empty($query['layout'])) {
					$query['request'] = MageBridgeUrlHelper::getLayoutUrl($query['layout']);

				// Use the MVC-layout plus the current request to determine the request (f.i. configured Menu-Items)
				} else if (!empty($cquery['layout'])) {
					$query['request'] = MageBridgeUrlHelper::getLayoutUrl($cquery['layout'], $cquery['request']);

				// Use the Menu-Item request as Magento request
				} else if (!empty($cquery['request'])) {
					$query['request'] = $cquery['request'];

				// Use the Menu-Item parameter as Magento request
				} else if ($cparams->get('request') != '') {
					$query['request'] = $cparams->get('request');

				// Obsolete?
				//} else if ($current_item->id != $root_item_id) {
				//	$query['request'] = $current_item->route;
				}
			}

			if (isset($query['request']) && is_numeric($query['request']) && !empty($query['layout'])) {
				$query['request'] = MageBridgeUrlHelper::getLayoutUrl($query['layout'], $query['request']);
			}

			// Enforce the Itemid of the MageBridge Root upon the current route
			if (MageBridgeUrlHelper::enforceRootMenu() && !in_array($root_item_id, $current_item->tree)) {
				$query['Itemid'] = $root_item_id;
				$query['view'] = 'root';

			// If the request is not empty, set the route to the MageBridge Root
			} else if (!empty($query['request'])) {
				$query['Itemid'] = $root_item_id;
			}
		
		// If there is no current item, assume to apply the Itemid of the MageBridge Root 
		} else {
			$query['Itemid'] = $root_item_id;
		}
	}

	// Add the request as only segment
	if (!empty($query['request']) && !empty($query['Itemid']) && $query['Itemid'] == $root_item_id) {
		$segments[] = $query['request'];
	} else if (!empty($query['request']) && (empty($root_item_id) && $query['view'] == 'root')) {
		$segments[] = $query['request'];
	}

	// Unset an Itemid that does not make sense
	if (isset($query['Itemid']) && $query['Itemid'] == 0) unset($query['Itemid']);

	// Unset all unneeded query-parts because they should be now either segmented or referenced from the Itemid
	$unset_elements = array('view', 'task', 'request', 'layout', 'format', 'SID', 'language', 'id');
	foreach ($unset_elements as $u) {
		unset($query[$u]);
	}

	// Return the segments
	return $segments;
}

/**
 * Function to convert a SEF URL back to a system URL
 */
function MagebridgeParseRoute($segments)
{
	$vars = array();

	// Strange bug: The first segment autoreplaces the first dash with a semi-column 
	if (!empty($segments)) {
		foreach ($segments as $index => $segment) {
			$segments[$index] = preg_replace('/^([a-zA-Z0-9]+)\:/', '\1-', $segment);
		}
	}

	// Skip to the API if this is detected
	if (isset($segments[1]) && $segments[1] == 'jsonrpc') {
		$vars['view'] = 'jsonrpc';
		$vars['task'] = $segments[2];
		return $vars;
	}

	// Get the active menu item
	$menu = JFactory::getApplication()->getMenu('site');
	$current_item = $menu->getActive();
	$items = MageBridgeUrlHelper::getMenuItems();

	// Fetch the Root-Item
	$root_item = MageBridgeUrlHelper::getRootItem();
	$root_item_id = ($root_item && $root_item->id > 0) ? $root_item->id : false;

	// Fix the segments when Root Menu-Item is enforced
	if(MageBridgeUrlHelper::enforceRootMenu()) {
		$current_item = $root_item;
		$current_path = JURI::getInstance()->toString(array('path'));
		$current_segments = explode('/', preg_replace('/^\//', '', $current_path));
		$root_path = JRoute::_($root_item->link.'&Itemid='.$root_item->id);
		$root_segments = explode('/', preg_replace('/^\//', '', $root_path));

		$segments = array();
		foreach($current_segments as $current_index => $current_segment) {
			if(isset($root_segments[$current_index]) && $root_segments[$current_index] == $current_segment) continue;
			if(empty($current_segment)) continue;
			$segments[] = $current_segment;
		}
	}

	// Parse the segments
	if (!empty($segments)) {

		$request = implode('/', $segments);
		$request = preg_replace('/^component\/magebridge\//', '', $request);
		$vars['request'] = $request;

	} else {
		$vars['request'] = null;
	}

	// A hack to set the active Menu-Item
	if (!empty($vars['request']) && !empty($items)) {
		foreach ($items as $item) {

			$preg_route = '/^'.str_replace('/', '\/', $item->route).'/';
			if (!empty($item->route) && preg_match($preg_route, $vars['request'])) {
				$menu->setActive($item->id);
				break;
			}			

			if (isset($item->query['request']) && $item->query['request'] == $vars['request']) {
				$menu->setActive($item->id);
				break;
			}
		}
	}

	// Set the view based on the current item
	if (empty($vars['request']) && !empty( $current_item->query['view'] )) {
		$vars['view'] = $current_item->query['view'];
	}

	// Set the default
	if (!isset($vars['view'])) {
		$vars['view'] = 'root';

		// Override the Itemid if the root is available
		if ($root_item_id) $vars['Itemid'] = $root_item_id;

		// Add the current pathing
		if ($current_item && $current_item->id != $root_item_id && in_array($root_item_id, $current_item->tree) && MageBridgeUrlHelper::enableRootMenu()) {
			$path = str_replace($root_item->route.'/', '', $current_item->route);
			if (!empty($vars['request'])) $vars['request'] = $path.'/'.$vars['request'];
		}
	}

	// Re-spoof the current Itemid
	if (isset($vars['Itemid']) && $vars['Itemid'] > 0) {
		JFactory::getApplication()->input->set('Itemid', $vars['Itemid']);
	}
	
	return $vars;
}
