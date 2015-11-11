<?php
/**
 * Joomla! MageBridge - JomSocial plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 *
 * Missing events:
 * - When editing privacy settings
 * - When editing profile
 * - When a member is added to some kind of group
 * - When an user point is provided
 * - When creating a new group
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class plgCommunityMageBridge extends CApplications
{
	var $name = 'Shop';
	var $_name = 'shop';
	var $_user = null;

	/**
	 * Load the parameters
	 * 
	 * @access private
	 * @param null
	 * @return JParameter
	 */
	private function getParams()
	{
		return $this->params;
	}

	/**
	 * Load the parameters of the User-plugin
	 * 
	 * @access private
	 * @param null
	 * @return JParameter
	 */
	private function getUserParams()
	{
		require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';
		$plugin = JPluginHelper::getPlugin('user', 'magebridge');
		$params = YireoHelper::toRegistry($plugin->params);
		return $params;
	}

	/**
	 * Return the MageBridge user-object
	 * 
	 * @access private
	 * @param string $name
	 * @return mixed $value
	 */
	private function getUser()
	{
		return MageBridge::getUser();
	}

	/**
	 * JomSocial event "onUserDetailsUpdate"
	 *
	 * @access public
	 * @param object JUser
	 * @return null
	 */
	public function onUserDetailsUpdate($user = null)
	{
		return; // This is already picked up by the default Joomla! user-events
	}

	/**
	 * JomSocial event "onAfterProfileUpdate"
	 */
	public function onAfterProfileUpdate($user_id, $update_success = false)
	{
		// Don't continue if the profile failed to update
		if ($update_success == false) return;

		// Don't continue if this plugin is set to disable syncing
		if ($this->getParams()->get('enable_tab',1) == 0) return;

		// Fetch the user and sync it
		$user = CFactory::getUser($user_id);
		if (!empty($user)) {
			$this->syncUser($user);
		}
	}

	/**
	 * JomSocial event "onProfileCreate"
	 */
	public function onProfileCreate($user = null)
	{
		// Fetch the user and sync it
		if (!empty($user)) {
			$this->syncUser($user);
		}
	}

	/**
	 * JomSocial event "onSystemStart"
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onSystemStart()
	{
		$params = $this->getParams();
		if ($params->get('enable_tab',1) == 1) {
			$this->showTab();
		}
	}

	/**
	 * Helper-method to show the tab
	 *
	 * @access private
	 * @param null
	 * @return null
	 */
	private function showTab()
	{
		// Require the core JomSocial library
		if (! class_exists('CFactory')) {
			require_once JPATH_BASE.'/components/com_community/libraries/core.php';
		}

		// Import the MageBridge autoloader
		require_once JPATH_BASE.'/components/com_magebridge/helpers/loader.php';

		// Get the variables
		$toolbar = CFactory::getToolbar();
		$user = CFactory::getActiveProfile();
		$username = $user->getDisplayName();

		$tab = $this->getTab();
		if (!empty($tab)) {
			$toolbar->addGroup('MAGEBRIDGE', $tab['name'], $this->getLink($tab['url']));
			foreach ($tab['children'] as $link) {
				$this->addLink($link[0], $link[1], $link[2]);
			}
		}
	}

	/**
	 * Helper method to read the tab-settings from the connectors-configuration file
	 *
	 * @access private
	 * @param null
	 * @return object
	 */
	private function getTab() 
	{
		/**
		// @todo: Remove deprecated code
		$connector = MageBridgeConnectorProfile::getInstance()->getConnector('jomsocial');
		$tab = array();
		if (!empty($connector)) {
			$config_file = $connector->getConfigFile();
			if ($config_file == true) {
				include $config_file;
			}
		}

		return $tab;
		*/
	}

	/**
	 * Helper-method to get a MageBridge link
	 *
	 * @access private
	 * @param string $request
	 * @return string
	 */
	private function getLink($request)
	{
		return 'index.php?option=com_magebridge&view=root&request='.$request;
	}

	/**
	 * Helper-method to add a link to the JomSocial MageBridge-tab
	 *
	 * @access private
	 * @param string $request
	 * @param string $name
	 * @param string $title
	 * @return null
	 */
	private function addLink($request, $name, $title)
	{
		$toolbar = CFactory::getToolbar();
		$toolbar->addItem('MAGEBRIDGE', 'MAGEBRIDGE_'.$name, $title, $this->getLink($request));
	}

	/**
	 * Helper method to sync the user
	 *
	 * @access private
	 * @param object JUser
	 * @return bool
	 */
	private function syncUser($user = null)
	{
		// Check if we can run this event or not
		if (MageBridgePluginHelper::allowEvent('onUserDetailsUpdate') == false) return;

		// Copy the username to the email address
		if (JFactory::getApplication()->isSite() == true && $this->getUserParams()->get('username_from_email', 1) == 1 && $user->username != $user->email) {

			if ($this->getUser()->allowSynchronization($user, 'save') == true) {
				MageBridgeModelDebug::getInstance()->notice( "onUserDetailsUpdate::bind on user ".$user->username );

				// Change the record in the database
				$user->email = $user->username;
				$user->save();
			}
		}

		// Synchronize this user-record with Magento
		if ($this->getUserParams()->get('user_sync', 1) == 1) {

			MageBridgeModelDebug::getInstance()->notice( "onUserDetailsUpdate::usersync on user ".$user->username );

			// Convert this object to an array
			if (!is_array($user)) {
				jimport('joomla.utilities.arrayhelper');
				$user = JArrayHelper::fromObject($user, false);
			}
			
			// Sync this user-record with the bridge
			MageBridge::getUser()->synchronize($user);
		}

		return true;
	}
}
