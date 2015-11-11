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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Helper for encoding and encrypting
 */
class MageBridgeAclHelper 
{
	/**
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
		if (empty($view)) $view = JFactory::getApplication()->input->getCmd('view');

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

		return true;
	}

	/**
	 * Determine whether the current user is only allowed demo-access or not
	 *
	 * @param mixed $view
	 * @param bool $redirect
	 * @return string
	 */
	public static function isDemo()
	{
		$user = JFactory::getUser();
		if ($user->authorise('com_magebridge.demo_ro', 'com_magebridge') == true && $user->authorise('com_magebridge.demo_rw', 'com_magebridge') == false) {
			return true;
		} 
		return false;
	}
}
