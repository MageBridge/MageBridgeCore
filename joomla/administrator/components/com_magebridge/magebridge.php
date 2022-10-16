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

// Load the libraries
require_once JPATH_SITE . '/components/com_magebridge/libraries/factory.php';
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';
require_once JPATH_COMPONENT . '/helpers/acl.php';

$app = JFactory::getApplication();

// If no view has been set, try the default
if ($app->input->getCmd('view') == '') {
    $app->input->set('view', 'home');
}

// Handle the SSO redirect
if ($app->input->getInt('sso') == 1) {
    $app->input->set('task', 'ssoCheck');
}

// Make sure the user is authorised to view this page
if (MageBridgeAclHelper::isAuthorized() == false) {
    return false;
}

// Initialize debugging
MageBridgeModelDebug::init();

// Require the current controller
$view = $app->input->getCmd('view');
$controllerFile = JPATH_COMPONENT . '/controllers/' . $view . '.php';

if (is_file($controllerFile)) {
    require_once $controllerFile;
    $controllerName = 'MageBridgeController' . ucfirst($view);
    $controller = new $controllerName();
} else {
    $controller = new MageBridgeController();
}

$task = $app->input->getCmd('task');

// Perform the requested task
$controller->execute($task);
$controller->redirect();
