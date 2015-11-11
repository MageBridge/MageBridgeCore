<?php
/**
 * Joomla! module MageBridge: Shopping Cart
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Read the parameters
$layout = $params->get('layout', 'default');
$layout = preg_replace('/^([^\:]+):/', '', $layout);
if ($layout == 'block')
{
	$layout = 'default';
}

// Call the helper
require_once(dirname(__FILE__) . '/helper.php');

// Build the block
if ($layout != 'ajax')
{
	$data = ModMageBridgeCartHelper::build($params);

	if ($layout != 'native' && empty($data))
	{
		return false;
	}
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_cart', $layout));
