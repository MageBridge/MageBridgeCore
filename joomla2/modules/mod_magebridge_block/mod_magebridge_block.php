<?php
/**
 * Joomla! module MageBridge: Block
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com/
 */
        
// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Read the parameters
$layout = $params->get('layout', 'default');

// Call the helper
require_once (dirname(__FILE__).'/helper.php');
$blockName = modMageBridgeBlockHelper::blockName($params);

// Build the block
if ($layout == 'ajax') {
    modMageBridgeBlockHelper::ajaxbuild($params);
} else {
    $block = modMageBridgeBlockHelper::build($params);
    if (empty($block)) return false;
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_block', $layout));
