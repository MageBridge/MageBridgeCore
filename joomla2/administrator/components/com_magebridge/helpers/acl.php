<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Initialize the ACLs
MageBridgeAclHelper::init();

/*
 * Helper for encoding and encrypting
 */
class MageBridgeAclHelper 
{
    /*
     * Initialize the helper-class
     *
     * @param mixed $string
     * @return string
     */
    public static function init()
    {
        // Joomla! 1.5 ACLs
        if (MageBridgeHelper::isJoomla15() == true) {
            $auth = JFactory::getACL();
            $auth->addACL('com_magebridge', 'manage', 'users', 'super administrator');
            $auth->addACL('com_magebridge', 'manage', 'users', 'administrator');
        }
    }

    /*
     * Check whether a certain person is authorised
     *
     * @param mixed $view
     * @param bool $redirect
     * @return string
     */
    public static function isAuthorized($view = null, $redirect = true)
    {
        // Initialize system variables
        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        if (empty($view)) $view = JRequest::getCmd('view');

        // Check the ACLs for Joomla! 1.5
        if (MageBridgeHelper::isJoomla15()) {

            // Determine whether the current view is protected
            $allowed_views = array('home', 'products', 'product', 'connectors', 'urls', 'url', 'users', 'check', 'logs');
            if (empty($view) || in_array($view, $allowed_views)) {
                return true;
            }

            // Check the privileges for remaining views
            if (!$user->authorize('com_magebridge', 'manage')) {
                if ($redirect) $application->redirect( 'index.php?option=com_magebridge', JText::_('ALERTNOTAUTH'), 'error' );
                return false;
            }

        // Check the ACLs for Joomla! 1.6 or later
        } else {

            switch($view) {
                case 'config':
                    $authorise = 'com_magebridge.config';
                    break;
                case 'check':
                    $authorise = 'com_magebridge.check';
                    break;
                case 'stores':
                case 'store':
                    $authorise = 'com_magebridge.stores';
                    break;
                case 'products':
                case 'product':
                    $authorise = 'com_magebridge.products';
                    break;
                case 'connectors':
                case 'connector':
                    $authorise = 'com_magebridge.connectors';
                    break;
                case 'urls':
                case 'url':
                    $authorise = 'com_magebridge.urls';
                    break;
                case 'users':
                case 'user':
                    $authorise = 'com_magebridge.users';
                    break;
                case 'usergroups':
                case 'usergroup':
                    $authorise = 'com_magebridge.usergroups';
                    break;
                case 'logs':
                case 'log':
                    $authorise = 'com_magebridge.logs';
                    break;
                case 'update':
                    $authorise = 'com_magebridge.update';
                    break;
                default:
                    $authorise = 'core.manage';
            }

            if ($user->authorise($authorise, 'com_magebridge') == false && $user->authorise('com_magebridge.demo_ro', 'com_magebridge') == false) {
                if ($user->authorise('core.manage', 'com_magebridge')) {
                    if ($redirect) $application->redirect('index.php?option=com_magebridge', JText::_('ALERTNOTAUTH'));
                } else {
                    if ($redirect) $application->redirect('index.php', JText::_('ALERTNOTAUTH'));
                }
                return false;
            }
        }

        return true;
    }

    /*
     * Determine whether the current user is only allowed demo-access or not
     *
     * @param mixed $view
     * @param bool $redirect
     * @return string
     */
    public static function isDemo()
    {
        if (MageBridgeHelper::isJoomla15()) {
            return false;
        }

        $user = JFactory::getUser();
        if ($user->authorise('com_magebridge.demo_ro', 'com_magebridge') == true && $user->authorise('com_magebridge.demo_rw', 'com_magebridge') == false) {
            return true;
        } 
        return false;
    }
}
