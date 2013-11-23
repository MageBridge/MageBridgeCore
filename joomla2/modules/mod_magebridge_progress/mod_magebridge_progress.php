<?php
/**
 * Joomla! module MageBridge: Progress
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

// Call the helper
require_once (dirname(__FILE__).'/helper.php');
$data = modMageBridgeProgressHelper::build($params);

// Include the layout-file
$layout = $params->get('layout', 'default');
require(JModuleHelper::getLayoutPath('mod_magebridge_progress', $layout));
