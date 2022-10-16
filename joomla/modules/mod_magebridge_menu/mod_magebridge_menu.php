<?php
/**
 * Joomla! module MageBridge: Catalog Menu
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';
require_once dirname(__FILE__) . '/helper.php';

// Read the parameters
$root = $params->get('root', 0);
$levels = $params->get('levels', 2);
$startLevel = $params->get('startlevel', 1);

if ($startLevel < 1) {
    $startLevel = 1;
}

$endLevel = $startLevel + $levels - 1;
$layout = $params->get('layout', 'default');

// Call the helper
$catalog_tree = ModMageBridgeMenuHelper::build($params);

// Load the catalog-tree
$rootLevel = (!empty($catalog_tree['level'])) ? $catalog_tree['level'] : 0;
$catalog_tree = ModMageBridgeMenuHelper::setRoot($catalog_tree, $root);
$catalog_tree = ModMageBridgeMenuHelper::parseTree($catalog_tree, $rootLevel + $startLevel, $rootLevel + $endLevel);

// Show the template
require(JModuleHelper::getLayoutPath('mod_magebridge_menu', $layout));
