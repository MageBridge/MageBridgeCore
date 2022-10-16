<?php
/**
 * Joomla! module MageBridge: Newsletter block
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

// Read the parameters
$layout = $params->get('layout', 'default');

// Call the helper
require_once(dirname(__FILE__) . '/helper.php');
$block = ModMageBridgeNewsletterHelper::build($params);

// Get the current user
$user = JFactory::getUser();

// Set the form URL
$form_url = MageBridgeUrlHelper::route('newsletter/subscriber/new');
$redirect_url = MageBridgeUrlHelper::route(MageBridgeUrlHelper::getRequest());
$redirect_url = MageBridgeEncryptionHelper::base64_encode($redirect_url);

// Require form validation
JHtml::_('behavior.formvalidation');

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_newsletter', $layout));
