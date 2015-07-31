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
defined('_JEXEC') or die( 'Restricted access' );

// Require all the neccessary libraries
require_once JPATH_COMPONENT.'/libraries/factory.php';
require_once JPATH_COMPONENT.'/helpers/loader.php';
require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/libraries/loader.php';

// Handle the SSO redirect
if (JFactory::getApplication()->input->getInt('sso') == 1) {
	JFactory::getApplication()->input->set('task', 'ssoCheck');
}

// Handle direct proxy requests
if (JFactory::getApplication()->input->get('url')) {
	JFactory::getApplication()->input->set('task', 'proxy');
}

// Initialize debugging
MagebridgeModelDebug::init();
			
// Require the controller
$requestedController = JFactory::getApplication()->input->getCmd('controller');
if ($requestedController == 'jsonrpc') {
	JFactory::getApplication()->input->set('task', JFactory::getApplication()->input->getCmd('task', '', 'get'));
	require_once JPATH_COMPONENT.'/controllers/default.jsonrpc.php';
	$controller = new MageBridgeControllerJsonrpc( );

} elseif ($requestedController == 'sso') {
	JFactory::getApplication()->input->set('task', JFactory::getApplication()->input->getCmd('task', '', 'get'));
	require_once JPATH_COMPONENT.'/controllers/default.sso.php';
	$controller = new MageBridgeControllerSso( );

} else {
	require_once JPATH_COMPONENT.'/controller.php';
	$controller = new MageBridgeController( );
}

// Perform the Request task
$controller->execute( JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();
