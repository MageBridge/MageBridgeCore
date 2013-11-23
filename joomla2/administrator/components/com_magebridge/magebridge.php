<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load the libraries
require_once JPATH_SITE.'/components/com_magebridge/libraries/factory.php';
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';
require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/libraries/loader.php';
require_once JPATH_COMPONENT.'/helpers/acl.php';

// If no view has been set, try the default
if (JRequest::getCmd('view') == '') {
    JRequest::setVar('view', 'home');
}

// Handle the SSO redirect
if (JRequest::getInt('sso') == 1) {
    JRequest::setVar('task', 'ssoCheck');
}

// Make sure the user is authorised to view this page
if (MageBridgeAclHelper::isAuthorized() == false) {
    return false;
}

// Initialize debugging
MagebridgeModelDebug::init();

// Require the current controller
$view = JRequest::getCmd('view');
$controller_file = JPATH_COMPONENT.'/controllers/'.$view.'.php';
if (is_file($controller_file)) {
    require_once $controller_file; 
    $controller_name = 'MageBridgeController'.ucfirst($view);
    $controller = new $controller_name();
} else {
    $controller = new MageBridgeController();
}

// Perform the requested task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

