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
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Profile-connector class
 *
 * @package MageBridge
 */
class MageBridgeConnectorProfile extends MageBridgeConnector
{
    /*
     * Singleton variable
     */
    private static $_instance = null;

    /*
     * Constants
     */
    const CONVERT_TO_JOOMLA = 1;
    const CONVERT_TO_MAGENTO = 2;

    /*
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

    /*
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

        // Run the right method for each connector
        $connectors = $this->getConnectors();
        if (!empty($connectors)) {
            foreach ($connectors as $connector) {
                $connector->onSave($user, $customer);
            }
        }
    }

    /*
     * Method to execute when the user-data need to be synced
     * 
     * @param array $user
     * @return bool
     */
    public function modifyUserFields($user)
    {
        $connectors = $this->getConnectors();
        if (!empty($connectors)) {
            foreach ($connectors as $connector) {
                if (isset($user['joomla_id'])) {
                    $user_id = $user['joomla_id'];
                } else if (isset($user['id'])) {
                    $user_id = $user['id'];
                } else {
                    $user_id = null;
                }

                if ($user_id > 0) {
                    $user = $connector->modifyFields($user_id, $user);
                }
            }
        }

        return $user;
    }

    /*
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

    /*
     * Convert a specific field 
     *
     * @param string $field
     * @param int $type
     * @return string
     */
    public function convertField($field, $type = self::CONVERT_TO_JOOMLA) 
    {
        // Stop if we don't have a proper name set
        if (empty($this->name)) {
            return null;
        }

        // Get the conversion-array
        $conversion = $this->getConversionArray();

        // Loop through the conversion to find the right match
        if (!empty($conversion)) {
            foreach ($conversion as $joomla => $magento) {
                if ($field == $magento && $type == self::CONVERT_TO_JOOMLA) {
                    return $joomla;
                } else if ($field == $joomla && $type == self::CONVERT_TO_MAGENTO) {
                    return $magento;
                }
            }
        }
        return null;
    }

    /*
     * Get the configuration file
     *
     * @param null
     * @return string
     */
    public function getConfigFile()
    {
        // Determine the conversion-file
        $params = $this->getParams();
        $custom = $this->getPath($this->name.'_'.$params->get('config_file', 'default').'.php');
        $default = $this->getPath($this->name . '_default.php');

        if ($custom == true) {
            return $custom;
        } else if ($default == true) {
            return $default;
        } else {
            return false;
        }
    }

    /*
     * Get the conversion-array
     * 
     * @param null
     * @return array
     */
    public function getConversionArray()
    {
        static $conversion = null;
        if (!is_array($conversion)) {

            // Determine the conversion-file
            $config_file = $this->getConfigFile();

            // If the conversion-file can't be read, use an empty conversion array
            if ($config_file == false) {
                $conversion = array();
            } else {
                // Include the conversion-file
                include $config_file;
            }
        }

        return $conversion;
    }

    /*
     * Overload methods to add an argument to it
     */
    public function getConnectors($type = null) { return parent::_getConnectors('profile'); }
    public function getConnector($name) { return parent::_getConnector('profile', $name); }
    public function getConnectorObject($name) { return parent::_getConnectorObject('profile', $name); }
    public function getPath($file) { return parent::_getPath('profile', $file); }
    public function getParams() { return parent::_getParams('profile'); }
}
