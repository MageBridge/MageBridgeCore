<?php
/**
 * Joomla! module MageBridge: CMS Block
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

// Call the helper
require_once (dirname(__FILE__).'/helper.php');
$blockName = $params->get('block');
$block = modMageBridgeCMSHelper::build($params);

// Return false if empty
if (empty($block)) {
    return false;
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_cms'));
