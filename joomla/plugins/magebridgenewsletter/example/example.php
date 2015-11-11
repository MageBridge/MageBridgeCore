<?php
/**
 * MageBridge Newsletter plugin - Example
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Newsletter Plugin - Example
 */
class plgMageBridgeNewsletterExample extends MageBridgePlugin
{
	/**
	 * Event "onNewsletterSubscribe"
	 * 
	 * @access public
	 * @param object $user Joomla! user object
	 * @param tinyint $state Whether the user is subscribed or not (0 for no, 1 for yes)
	 * @return bool
	 */
	public function onNewsletterSubscribe($user, $state)
	{
		// Make sure this plugin is enabled
		if ($this->isEnabled() == false) {
			return false;
		}

		// Do your stuff to subscribe an user to a specific newsletter

		return true;
	}

	/**
	 * Method to check whether this plugin is enabled or not
	 *
	 * @param null
	 * @return bool
	 */
	protected function isEnabled()
	{
		// Check for the existance of a specific component
		return $this->checkComponent('com_example');
	}
}

