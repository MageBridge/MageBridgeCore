<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require all the neccessary libraries
require_once JPATH_COMPONENT . '/libraries/factory.php';
require_once JPATH_COMPONENT . '/helpers/loader.php';

$app   = JFactory::getApplication();
$input = $app->input;

// Handle the SSO redirect
if ($input->getInt('sso') == 1) {
    $input->set('task', 'ssoCheck');
}

// Handle direct proxy requests
if ($input->get('url')) {
    $input->set('task', 'proxy');
}

// Initialize debugging
MageBridgeModelDebug::init();

// Simple security measure
$input->set('task', $input->getCmd('task'));

// Require the controller
$requestedController = $input->getCmd('controller');

if ($requestedController == 'jsonrpc') {
    require_once JPATH_COMPONENT . '/controllers/default.jsonrpc.php';
    $controller = new MageBridgeControllerJsonrpc();
} elseif ($requestedController == 'sso') {
    require_once JPATH_COMPONENT . '/controllers/default.sso.php';
    $controller = new MageBridgeControllerSso();
} else {
    require_once JPATH_COMPONENT . '/controller.php';
    $controller = new MageBridgeController();
}

// Perform the Request task
$controller->execute($input->getCmd('task'));
$controller->redirect();
