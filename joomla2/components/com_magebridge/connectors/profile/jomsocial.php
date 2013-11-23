<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Profile-connector for JomSocial Groups
 *
 * @package MageBridge
 */
class MageBridgeConnectorProfileJomsocial extends MageBridgeConnectorProfile
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        jimport('joomla.application.component.helper');
        if (is_dir(JPATH_ADMINISTRATOR.'/components/com_community')
            && JComponentHelper::isEnabled('com_community') == true) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Method to execute when the profile is saved from Magento
     *
     * @param JUser $user
     * @param array $customer
     * @param array $address
     * @return bool
     */
    public function onSave($user = null, $customer = null, $address = null)
    {
        // Preliminary checks
        if ($user == null || $customer == null) {
            return false;
        }

        // Get system variables
        $db = JFactory::getDBO();

        // Get the user-fields from the database
        $db->setQuery('SELECT id,fieldcode FROM #__community_fields WHERE fieldcode != ""');
        $fields = $db->loadObjectList();
        if (empty($fields)) {
            return false;
        }

        // Add the customer values
        $field_values = array();
        if (!empty($customer)) {
            foreach ($customer as $name => $value) {
                $newname = $this->convertField($name, self::CONVERT_TO_JOOMLA);
                foreach ($fields as $field) {
                    if ($field->fieldcode == $newname) {
                        $field_values[$field->id] = $value;
                        break;
                    }
                }
            }
        }

        // Save the user
        include_once(JPATH_ROOT.'/components/com_community/libraries/core.php');
        include_once(JPATH_ROOT.'/components/com_community/models/profile.php');
        $model = CFactory::getModel('profile');
        $model->saveProfile($user->id, $field_values );

        return true;
    }

    /*
     * Method to modify the user array
     *
     * @param int $user_id
     * @param array $user
     * @return array
     */
    public function modifyFields($user_id = 0, $user = array())
    {
        // Get the JomSocial user
        require_once JPATH_SITE.'/components/com_community/libraries/core.php';
        $jsuser  = CFactory::getUser($user_id);

        // Initialize the Magento field-list with basic JomSocial fields
        $user['display_name'] = $jsuser->getDisplayName();
        $user['avatar'] = $jsuser->getAvatar();
        $user['thumb'] = $jsuser->getThumbAvatar();

        // Get the custom JomSocial fields
        $db = JFactory::getDBO();
        $query = "SELECT f.published, v.field_id, f.fieldcode, v.value FROM #__community_fields AS f "
            . " LEFT JOIN #__community_fields_values AS v ON f.id = v.field_id "
            . " WHERE f.published=1";
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Exit if this is not a valid result
        if (empty($rows)) {
            return $user;
        }

        // Parse the custom fields to add them to the Magento field-list
        foreach ($rows as $row) {
            if (isset($row->fieldcode) && !empty($row->fieldcode)) {
                $name = $this->convertField($row->fieldcode, self::CONVERT_TO_MAGENTO);
                if (!empty($name)) {
                    $user[$name] = $row->value;
                }
            }
        }

        return $user;
    }
}
