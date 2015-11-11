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
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Profile-connector class
 *
 * @package MageBridge
 */
class MageBridgeConnectorProfile extends MageBridgeConnector
{
	/**
	 * Singleton variable
	 */
	private static $_instance = null;

	/**
	 * Constants
	 */
	const CONVERT_TO_JOOMLA = 1;
	const CONVERT_TO_MAGENTO = 2;

	/**
	 * Singleton method
	 *
	 * @param null
	 * @return MageBridgeConnectorProfile
	 */
	public static function getInstance()
	{
		static $instance;

		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Method to do something when changing the profile from Magento
	 *
	 * @param JUser $user
	 * @param array $customer
	 * @param array $address
	 * @return mixed
	 */
	public function onSave($user = null, $customer = null, $address = null)
	{
		// Merge the address data into the customer field
		if (!empty($address)) {
			foreach ($address as $name => $value) {
				$name = 'address_'.$name;
				$customer[$name] = $value;
			}
		}

		// Import the plugins
		JPluginHelper::importPlugin('magebridgeprofile');
		JFactory::getApplication()->triggerEvent('onMageBridgeProfileSave', array($user, $customer));
	}

	/**
	 * Method to execute when the user-data need to be synced
	 * 
	 * @param array $user
	 * @return bool
	 */
	public function modifyUserFields($user)
	{
		if (isset($user['joomla_id'])) {
			$user_id = $user['joomla_id'];
		} else if (isset($user['id'])) {
			$user_id = $user['id'];
		} else {
			$user_id = null;
		}

		if (!$user_id > 0) {
			return $user;
		}

		// Import the plugins
		JPluginHelper::importPlugin('magebridgeprofile');
		JFactory::getApplication()->triggerEvent('onMageBridgeProfileModifyFields', array($user_id, &$user));

		return $user;
	}

	/**
	 * Method to execute when the profile is saved
	 * 
	 * @param int $user_id
	 * @return bool
	 */
	public function synchronize($user_id = 0)
	{
		// Exit if there is no user_id
		if (empty($user_id)) return false;

		// Get a general user-array from Joomla! itself
		$db = JFactory::getDBO();
		$query = "SELECT `name`,`username`,`email` FROM `#__users` WHERE `id`=".(int)$user_id;
		$db->setQuery($query);
		$user = $db->loadAssoc();

		// Exit if this is giving us no result
		if (empty($user)) return false;

		// Sync this user-record with the bridge
		MageBridgeModelDebug::getInstance()->trace( 'Synchronizing user', $user);
		MageBridge::getUser()->synchronize($user);

		$session = JFactory::getSession();
		$session->set('com_magebridge.task_queue', array());

		return true;
	}
}
