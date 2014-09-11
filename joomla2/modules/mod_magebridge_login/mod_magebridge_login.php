<?php
/**
 * Joomla! module MageBridge Login
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */
        
// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Decide whether to show a login-link or logout-link
$user = JFactory::getUser();
$type = (!$user->get('guest')) ? 'logout_link' : 'login_link';

// Read the parameters
$layout = $params->get('layout', 'default');

switch($params->get($type)) {
    case 'current':
        $return_url = JFactory::getURI()->toString();
        break;

    case 'home':
        $default = JFactory::getApplication()->getMenu('site')->getDefault();
        $return_url = JRoute::_('index.php?Itemid='.$default->id);
        break;

    case 'mbhome':
        $return_url = MageBridgeUrlHelper::route('/');
        break;

    case 'mbaccount':
        $return_url = MageBridgeUrlHelper::route('customer/account');
        break;
}

$return_url = base64_encode($return_url);

// Set the greeting name
switch($params->get('greeting_name')) {
    case 'name': 
        $name = (!empty($user->name)) ? $user->name : $user->username;
        break;
    default:
        $name = $user->username;
        break;
}

// Construct the URLs
$account_url = MageBridgeUrlHelper::route('customer/account');
$forgotpassword_url = MageBridgeUrlHelper::route('customer/account/forgotpassword');
$createnew_url = MageBridgeUrlHelper::route('customer/account/create');

// Construct the component variables
if (MageBridgeHelper::isJoomla15()) {
    $component = 'com_user';
    $password_field = 'passwd';
    $task_login = 'login';
    $task_logout = 'logout';
} else {
    $component = 'com_users';
    $password_field = 'password';
    $task_login = 'user.login';
    $task_logout = 'user.logout';
}

// Construct the component URL
$component_url = JRoute::_('index.php');
//$component_url = JRoute::_('index.php?option='.$component);

// Include the template-helper
$magebridge = new MageBridgeTemplateHelper();

require(JModuleHelper::getLayoutPath('mod_magebridge_login', $layout));
