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
 * Bridge user class
 */
class MageBridgeModelUser
{
	/**
	 * Instance variable
	 */
	protected static $_instance = null;

	/**
	 * Singleton
	 *
	 * @return MageBridgeModelUser $_instance
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
	 * Method to create a new Joomla! user if it does not yet exist
	 *
	 * @param array $user
	 * @param bool $empty_password
	 * @return JUser|null
	 */
	public function create($user, $empty_password = false)
	{
		// Check on the users email
		if(empty($user['email']) || $this->isValidEmail($user['email']) == false) {
			return false;
		}

		// Import needed libraries
		jimport('joomla.utilities.date');
		jimport('joomla.user.helper');
		jimport('joomla.application.component.helper');

		// Import user plugins
		JPluginHelper::importPlugin('user');

		// Get system variables
		$db = JFactory::getDBO();

		// Determine the email address
		$email = $user['email'];
		if(!empty($user['original_data']['email'])) {
			$email = $user['original_data']['email'];
		}

		// Try to fetch the user-record from the database
		$query = 'SELECT `id` FROM #__users WHERE email=' . $db->quote(email);
		$db->setQuery( $query );
		$result = $db->loadResult();

		// If $result is empty, this user (with $user['email']) does not exist yet
		if (empty($result)) {

			// Construct a data-array for this user
			$data = array(
				'name' => $user['name'],
				'username' => $user['username'],
				'email' => $user['email'],
				'guest' => 0,
			);

			// Current date
			$now = new JDate();
			$data['registerDate'] = $now->toSql();

			// Do not use empty passwords in the Joomla! user-record
			if ($empty_password == false) {

				// Generate a new password if a password is not set
				if(!empty($user['password']) && is_string($user['password'])) {
					$password = $user['password'];
				} else {
					$password = JUserHelper::genRandomPassword();
				}

				// Generate the encrypted password
				$salt  = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password, $salt);
				$data['password'] = $crypt.':'.$salt;
				$data['password2'] = $crypt.':'.$salt;

			// Use empty password in the Joomla! user-record
			} else {
				$data['password'] = '';
				$data['password2'] = '';
			}

			// Make sure MageBridge events stop
			$data['disable_events'] = 1;

			// Trigger the before-save event
			MageBridgeModelDebug::getInstance()->notice('Firing event onUserBeforeSave');
			JFactory::getApplication()->triggerEvent('onUserBeforeSave', array($data, true, $data));

			// Get the com_user table-class and use it to store the data to the database
			$table = JTable::getInstance('user', 'JTable');
			$table->bind($data);
			$result = $table->store();

			// Load the user
			$newuser = $this->loadByEmail($user['email']);
			$data['id'] = $newuser->id;

			// Trigger the after-save event
			MageBridgeModelDebug::getInstance()->notice('Firing event onUserAfterSave');
			JFactory::getApplication()->triggerEvent('onUserAfterSave', array($data, true, true, null));

			// Add additional data
			if (isset($table->id) && $table->id > 0) {

				// Check whether the current user is part of any groups
				$db->setQuery('SELECT * FROM `#__user_usergroup_map` WHERE `user_id`='.$table->id);
				$rows = $db->loadObjectList();
				if (empty($rows)) {
					$group_id = MageBridgeUserHelper::getDefaultJoomlaGroupid();
					if (!empty($group_id)) {
						$db->setQuery('INSERT INTO `#__user_usergroup_map` SET `user_id`='.$table->id.', `group_id`='.$group_id);
						$db->execute(); 
					}
				}
			}

			// Get the resulting user
			return self::loadByEmail($user['email']);
		}
		return null;
	}

	/**
	 * Method to synchronize the user account with Magento
	 *
	 * @param array $user
	 * @return array $data Data as returned by Magento
	 */
	public function synchronize($user)
	{
		// Disable at user events
		if(isset($user['disable_events']) && $user['disable_events'] == 1) {
			return null;
		}

		MageBridgeModelDebug::getInstance()->notice( "MageBridgeModelUser::synchronize() on user ".$user['email'] );

		// Use the email if no username is set
		if (empty($user['username'])) {
			$user['username'] = $user['email'];
		}

		// Check on the users email
		if($this->isValidEmail($user['email']) == false) {
			return false;
		}

		// Set the right ID
		$user['joomla_id'] = (isset($user['id'])) ? $user['id'] : 0;

		// Find some logic to divide the "name" into a "firstname" and "lastname"
		$user = MageBridgeUserHelper::convert($user);

		// Only set the password, when the password does not appear to be the encrypted version
		if (empty($user['password_clear'])) {
			if (isset($user['password']) && 
				!preg_match('/^\$/', $user['password']) && 
				!preg_match('/^\{SHA256\}/', $user['password']) && 
				!preg_match('/([a-z0-9]{32}):([a-zA-Z0-9]+)/', $user['password'])) {
				$user['password_clear'] = $user['password'];
			}
		}

		// Try to detect the password in this POST
		if (empty($user['password_clear'])) {
			$fields = array('password_clear', 'password', 'passwd');
			$jform = JFactory::getApplication()->input->get('jform', array(), 'post');
			foreach ($fields as $field) {
				$password = JFactory::getApplication()->input->getString($field, '', 'post');
				if (empty($password) && is_array($jform) && !empty($jform[$field])) {
					$password = $jform[$field];
				}

				if (!empty($password)) {
					$user['password_clear'] = $password;
					break;
				}
			}
		}
		
		// Delete unusable fields
		unset($user['id']);
		unset($user['password']);
		unset($user['params']);
		unset($user['userType']);
		unset($user['sendEmail']);
		unset($user['option']);
		unset($user['task']);

		// Delete unusable empty fields
		foreach ($user as $name => $value) {
			if (empty($value)) unset($user[$name]);
		}

		// Encrypt the user-password for transfer through the MageBridge API
		if (isset($user['password_clear'])) {
			if (empty($user['password_clear']) || !is_string($user['password_clear'])) {
				unset($user['password_clear']);

			} else {
				$user['password_clear'] = MageBridgeEncryptionHelper::encrypt($user['password_clear']);
			}
		}

		// Add the Website ID to this user
		$user['website_id'] = MagebridgeModelConfig::load('website');

		// Add the default customer-group ID to this user (in case we need to create a new user)
		$user['default_customer_group'] = MagebridgeModelConfig::load('customer_group');

		// Add the customer-group ID to this user (based upon groups configured in #__magebridge_usergroups)
		$user['customer_group'] = MageBridgeUserHelper::getMagentoGroupId($user);

		// Make sure events are disabled on the Magento side
		$user['disable_events'] = 1;

		// Add the profile-connector data to this user
		$user = MageBridgeConnectorProfile::getInstance()->modifyUserFields($user);

		// Initalize the needed objects
		$bridge = MageBridgeModelBridge::getInstance();
		$register = MageBridgeModelRegister::getInstance();

		// Build the bridge and fetch the result
		$id = $register->add('api', 'magebridge_user.save', $user);
		$bridge->build();
		$data = $register->getDataById($id);

		return $data;
	}

	/**
	 * Method to delete the customer from Magento
	 *
	 * @param array $user
	 * @return array $data
	 */
	public function delete($user)
	{
		// Add the Website ID to this user
		$user['website_id'] = MagebridgeModelConfig::load('website');

		// Initalize the needed objects
		$bridge = MageBridgeModelBridge::getInstance();
		$register = MageBridgeModelRegister::getInstance();

		// Build the bridge and fetch the result
		$id = $register->add('api', 'magebridge_user.delete', $user);
		$bridge->build();
		$data = $register->getDataById($id);

		return $data;
	}

	/**
	 * Method to login an user into Magento - called from the "User - MageBridge" plugin
	 *
	 * @param string $email
	 * @return array
	 */
	public function login($email = null)
	{
		// Backend access
		if (JFactory::getApplication()->isSite() == false) {

			// Check if authentication is enabled for the backend
			if (MagebridgeModelConfig::load('enable_auth_backend') != 1) {
				return false;
			}

			$application_name = 'admin';

		// Frontend access
		} else {

			// Check if authentication is enabled for the frontend
			if (MagebridgeModelConfig::load('enable_auth_frontend') != 1) {
				return false;
			}

			$application_name = 'site';
		}

		// Encrypt values for transfer through the MageBridge API
		$email = MageBridgeEncryptionHelper::encrypt($email);

		// Construct the API-arguments
		$arguments = array(
			'email' => $email,
			'application' => $application_name,
			'disable_events' => 1,
		);

		// Initalize the needed objects
		$bridge = MageBridgeModelBridge::getInstance();
		$register = MageBridgeModelRegister::getInstance();

		// Build the bridge and fetch the result
		$id = $register->add('api', 'magebridge_user.login', $arguments);
		$bridge->build();
		$data = $register->getDataById($id);

		return $data;
	}

	/**
	 * Method to authenticate an user - called from the "Authentication - MageBridge" plugin
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $application
	 * @return array
	 */
	public function authenticate($username = null, $password = null, $application = 'site')
	{
		// Encrypt values for transfer through the MageBridge API
		$username = MageBridgeEncryptionHelper::encrypt($username);
		$password = MageBridgeEncryptionHelper::encrypt($password);

		// Construct the API-arguments
		$arguments = array(
			'username' => $username,
			'password' => $password,
			'application' => $application,
			'disable_events' => 1,
		);

		// Initalize the needed objects
		$bridge = MageBridgeModelBridge::getInstance();
		$register = MageBridgeModelRegister::getInstance();

		// Build the bridge and fetch the result
		$id = $register->add('authenticate', null, $arguments);
		$bridge->build();
		$data = $register->getDataById($id);

		return $data;
	}

	/**
	 * Method to load an user-record by a specific unique field
	 *
	 * @param string $field
	 * @param string $value
	 * @return bool|JUser 
	 */
	public function loadByField($field = null, $value = null)
	{
		// Abort if the email is not set
		if (empty($field) || empty($value)) {
			return false;
		}

		// Fetch the user-record for this email-address
		$db = JFactory::getDBO();
		$query = "SELECT `id` FROM `#__users` WHERE `".$field."` = ".$db->Quote($value);
		$db->setQuery($query);
		$row = $db->loadObject();

		// If there is no such a row, this user does not exist
		if (empty($row) || !isset($row->id) || !$row->id > 0) {
			return false;
		}

		// Load the user by its user-ID
		$user_id = $row->id;
		$user = JFactory::getUser($user_id);
		if (empty($user->id)) {
			return false;
		}

		return $user;
	}

	/**
	 * Method to load an user-record by its username
	 *
	 * @param string $username
	 * @return bool|JUser 
	 */
	public function loadByUsername($username = null)
	{
		return $this->loadByField('username', $username);
	}

	/**
	 * Method to load an user-record by its email address
	 *
	 * @param string $email
	 * @return bool|JUser 
	 */
	public function loadByEmail($email = null)
	{
		// Check on the email
		if($this->isValidEmail($email) == false) {
			return false;
		}

		return $this->loadByField('email', $email);
	}

	/**
	 * Method to check whether an user should be synchronized or not
	 *
	 * @param JUser $user
	 * @return bool
	 */
	public function allowSynchronization($user = null, $action = null)
	{
		// Check if we have a valid object
		if ($user instanceof JUser)
		{
			// Don't synchronize backend-users
			if (MageBridgeUserHelper::isBackendUser($user))
			{
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to postlogin a Magento customer
	 *
	 * @param string $user_email
	 * @param int $user_id
	 * @param bool $throw_event
	 * @param bool $allow_post
	 * @return bool
	 */
	public function postlogin($user_email = null, $user_id = null, $throw_event = true, $allow_post = false)
	{
		// Check if the arguments are set
		if (empty($user_email) && ($user_id > 0) == false) {
			return false;
		}

		// Bugfix for malformed email
		if(strstr($user_email, '%40')) {
			$user_email = urldecode($user_email);
		}

		// Check on the email
		if($this->isValidEmail($user_email) == false) {
			return false;
		}

		// Check if this is the frontend
		$application = JFactory::getApplication();
		if ($application->isSite() == false) {
			return false;
		}

		// Check if this current request is actually a POST-request
		$post = JFactory::getApplication()->input->post->getArray();
		if (!empty($post) && $allow_post == false) {
			return false;
		}
	
		// Fetch the current user
		$user = JFactory::getUser();
		
		// Set the changed-flag
		$changed = false;

		// Check whether the Joomla! ID is different
		if ($user_id > 0 && $user->id != $user_id) {
			$db = JFactory::getDBO();
			$query = "SELECT id FROM #__users WHERE `id` = ".(int)$user_id;
			$db->setQuery($query);
			$row = $db->loadObject();
			if (!empty($row)) {
				$user = JFactory::getUser($user_id);
				$changed = true;
			}
		}

		// Double-check whether the Joomla! email is different
		if (!empty($user_email) && $user->email != $user_email) {
			$user = $this->loadByEmail($user_email);
			$changed = true;
		}

		// Check whether the Joomla! ID is set, but guest is still 1
		if (!empty($user) && $user->id > 0 && isset($user->guest) && $user->guest == 1) {
			$changed = true;
		}

		// If there is still no valid user, autocreate it
		if (!empty($user_email) && (empty($user) || empty($user->email))) {
			$data = array(
				'name' => $user_email,
				'username' => $user_email,
				'email' => $user_email,
			);
			$user = $this->create($data);
			$changed = true;
		}

		// Do not fire the event when using the onepage-checkout
		if (MageBridgeTemplateHelper::isPage('checkout/onepage') == true && MageBridgeTemplateHelper::isPage('checkout/onepage/success') == false) {
			$throw_event = false;
		} elseif (MageBridgeTemplateHelper::isPage('firecheckout') == true) {
			$throw_event = false;
		}

		// Give a simple log-entry
		if ($changed == true) {
			MageBridgeModelDebug::getInstance()->notice("Postlogin on user = ".$user_email);
		}

		// If there are changes, throw the onLoginUser event
		$throw_event = true;
		if ($throw_event == true && $changed == true && !empty($user)) {

			// Add options for our own user-plugin
			$options = array('disable_bridge' => true, 'action' => 'core.login.site', 'return' => null);
			$options['remember'] = 1;

			// Convert the user-object to an array
			$user = JArrayHelper::fromObject($user);

			// Fire the event
			MageBridgeModelDebug::getInstance()->notice('Firing event onUserLogin');
			JPluginHelper::importPlugin('user');
			JFactory::getApplication()->triggerEvent('onUserLogin', array($user, $options));
		}

		return true;
	}

	public function isValidEmail($email)
	{
		if(JMailHelper::isEmailAddress($email))
		{
			return true;
		}

		return false;
	}
}
