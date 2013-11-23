<?php
/**
 * Joomla! module MageBridge: Widget
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
$widgetName = $params->get('widget');

// Call the helper
require_once (dirname(__FILE__).'/helper.php');

// Build the block
if ($layout == 'ajax') {
    modMageBridgeWidgetHelper::ajaxbuild($params);
} else {
    $widget = modMageBridgeWidgetHelper::build($params);
    if (empty($widget)) return false;
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_widget', $layout));
