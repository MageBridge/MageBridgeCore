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
 * MageBridge Product-connector for RSFiles
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductRSFiles extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_rsfiles');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `GroupName` AS `title`, `IdGroup` AS `value` FROM `#__rsfiles_groups` ORDER BY `name`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();

        return JHTML::_('select.genericlist', $options, 'rsfiles_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (isset($post['rsfiles_group'])) {
            return $post['rsfiles_group'];
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

        // Get the RSFiles group
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__rsfiles_group_details` WHERE `IdGroup`=".(int)$groupid;
        $db->setQuery($query);
        $row = $db->loadObject();

        if (empty($row)) {
            $query = "INSERT INTO `#__rsfiles_group_details` SET `IdGroup`=".(int)$groupid.", `IdUsers`=".(int)$user->id;

        } else {

            // Construct the new users-list
            if (empty($row->IdUsers)) {
                $users = $user->id;
            } else {
                $user_ids = explode(',', $row->IdUsers);
                $user_ids[] = $user->id;
                $users = implode(',', $user_ids);
            }

            $query = "UPDATE `#__rsfiles_group_details` SET `IdUsers`=".$users." WHERE `IdGroup`=".(int)$groupid;
        }

        $db->setQuery($query);
        $db->query();

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
        $query = "DELETE FROM FROM `#__flexiaccess_members` WHERE `group_id`=".(int)$groupid." AND `member_id`=".(int)$user->id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}
