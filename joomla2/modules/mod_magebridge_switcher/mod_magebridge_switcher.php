<?php
/**
 * Joomla! module MageBridge: Store Switcher
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^_:/', '', $layout);

// Call the helper
require_once (dirname(__FILE__).'/helper.php');

// Fetch the API data
$stores = modMageBridgeSwitcherHelper::build($params);
if (empty($stores)) return false;

// Set extra variables
$redirect_url = JFactory::getURI()->toString();

// Build HTML elements
if ($layout == 'language') {
    $select = modMageBridgeSwitcherHelper::getStoreSelect($stores, $params);
} else {
    $select = modMageBridgeSwitcherHelper::getFullSelect($stores, $params);
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_switcher', $layout));

// End
