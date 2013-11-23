<?php
/**
 * Joomla! module MageBridge: Catalog Menu
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
require_once dirname(__FILE__).'/helper.php';

// Read the parameters
$root = $params->get('root', 0);
$levels = $params->get('levels', 2);

// Call the helper
$catalog_tree = modMageBridgeMenuHelper::build($params);

// Determine the appropriate settings
$catalog_tree = modMageBridgeMenuHelper::setRoot($catalog_tree, $root);
if (!empty($catalog_tree[0]['level'])) {
    $levels = $catalog_tree[0]['level'] + $levels;
}

$catalog_tree = modMageBridgeMenuHelper::parseTree($catalog_tree, $levels );
require(JModuleHelper::getLayoutPath('mod_magebridge_menu'));
