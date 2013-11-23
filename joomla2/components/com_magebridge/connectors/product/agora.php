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
 * MageBridge Product-connector for Agora
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAgora extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_agora');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name` AS `title`, `id` AS `value` FROM `#__agora_group`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'agora_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['agora_group'])) {
            return $post['agora_group'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param int $groupid
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($groupid = null, $user = null, $status = null)
    {
        // Sanity checks
        if ((int)$user->id == 0 || (int)$groupid == 0) {
            return false;
        }

        // Get the Agora user
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__agora_users` WHERE `jos_id`=".(int)$user->id;
        $db->setQuery($query);
        $row = $db->loadObject();

        // If the Agora user does not exist yet, create it
        if (empty($row)) {
            $query = "INSERT INTO `#__agora_users` SET `jos_id`=".(int)$user->id.", `username`=".$db->Quote($user->username).", `email`=".$db->Quote($user->email);
            $db->setQuery($query);
            $db->query();

            $query = "SELECT * FROM `#__agora_users` WHERE `jos_id`=".(int)$user->id;
            $db->setQuery($query);
            $row = $db->loadObject();
        }

        // Check whether the Agora user is linked already to the Agora group
        $userid = $row->id;
        if ($userid > 0) {
            $query = "SELECT * FROM `#__agora_user_group` WHERE `user_id`=".(int)$userid." AND `group_id`=".(int)$groupid;
            $db->setQuery($query);
            $row = $db->loadObject();

            if (empty($row)) {
                $query = "INSERT INTO `#__agora_user_group` SET `user_id`=".(int)$userid.", `group_id`=".(int)$groupid;
                $db->setQuery($query);
                $db->query();
            }
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param int $groupid
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onReverse($groupid = null, $user = null)
    {
        // Sanity checks
        if ((int)$user->id == 0 || (int)$groupid == 0) {
            return false;
        }

        // Delete this user from the group
        $db = JFactory::getDBO();
        $query = "DELETE FROM FROM `#__agora_user_group` WHERE `group_id`=".(int)$groupid." AND `user_id` IN (SELECT id FROM `#__agora_users WHERE `jos_id`=".(int)$user->id.")";
        $db->setQuery($query);
        $db->query();

        return true;
    }
}
