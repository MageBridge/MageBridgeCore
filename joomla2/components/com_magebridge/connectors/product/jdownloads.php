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
 * MageBridge Product-connector for jDownloads Groups
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJDownloadsGroup extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_jdownloads');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `groups_name` AS `title`, `id` AS `value` FROM `#__jdownloads_groups`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'jdownloads_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['jdownloads_group'])) {
            return $post['jdownloads_group'];
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
        // Get the group
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__jdownloads_groups` WHERE `id`=".(int)$groupid;
        $db->setQuery($query);
        $row = $db->loadObject();

        if (!empty($row)) {

            // Construct the new members-list
            if (empty($row->groups_members)) {
                $members = $user->id;
            } else {
                $user_ids = explode(',', $row->groups_members);
                $user_ids[] = $user->id;
                $members = implode(',', $user_ids);
            }

            // Update the new members-list within the database
            $members = $db->Quote($members);
            $query = "UPDATE `#__jdownloads_groups` SET `groups_members`=".$members." WHERE `id`=".(int)$groupid;
            $db->setQuery($query);
            $db->query();
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
        // Get the group
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__jdownloads_groups` WHERE `id`=".(int)$groupid;
        $db->setQuery($query);
        $row = $db->loadObject();

        if (!empty($row)) {

            // Construct the new members-list
            if (empty($row->groups_members)) {
                $members = $user->id;
            } else {
                $user_ids = explode(',', $row->groups_members);
                $user_ids = array_diff($user_ids, array($user->id));
                $members = implode(',', $user_ids);
            }

            // Update the new members-list within the database
            $members = $db->Quote($members);
            $query = "UPDATE `#__jdownloads_groups` SET `groups_members`=".$members." WHERE `id`=".(int)$groupid;
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }
}
