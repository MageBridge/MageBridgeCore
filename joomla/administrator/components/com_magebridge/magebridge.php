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
defined( '_JEXEC' ) or die( 'Restricted access' );

// Load the libraries
require_once JPATH_SITE.'/components/com_magebridge/libraries/factory.php';
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';
require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/libraries/loader.php';
require_once JPATH_COMPONENT.'/helpers/acl.php';

// If no view has been set, try the default
if (JFactory::getApplication()->input->getCmd('view') == '') {
    JFactory::getApplication()->input->setVar('view', 'home');
}

// Handle the SSO redirect
if (JFactory::getApplication()->input->getInt('sso') == 1) {
    JFactory::getApplication()->input->setVar('task', 'ssoCheck');
}

// Make sure the user is authorised to view this page
if (MageBridgeAclHelper::isAuthorized() == false) {
    return false;
}

// Initialize debugging
MagebridgeModelDebug::init();

// Require the current controller
$view = JFactory::getApplication()->input->getCmd('view');
$controller_file = JPATH_COMPONENT.'/controllers/'.$view.'.php';
if (is_file($controller_file)) {
    require_once $controller_file; 
    $controller_name = 'MageBridgeController'.ucfirst($view);
    $controller = new $controller_name();
} else {
    $controller = new MageBridgeController();
}

// Perform the requested task
$controller->execute(JFactory::getApplication()->input->getCmd('task'));
$controller->redirect();

