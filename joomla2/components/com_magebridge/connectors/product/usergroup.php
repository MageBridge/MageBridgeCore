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
 * MageBridge Product-connector for Joomla! User Groups
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductUsergroup extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        if (MageBridgeHelper::isJoomla15()) {
            $acl = JFactory::getACL();
            $gtree = $acl->get_group_children_tree( null, 'USERS', false );
            return JHTML::_('select.genericlist', $gtree, 'usergroup_id', null, 'value', 'text', $value);
        } else {
            return JHTML::_('access.usergroup', 'usergroup_id', $value, null, null, null);
        }
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['usergroup_id'])) {
            return $post['usergroup_id'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param int $gid
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($gid = null, $user = null, $status = null)
    {
        // Get common records
        $db = JFactory::getDBO();

        // Joomla! 1.5 method
        if (MageBridgeHelper::isJoomla15()) {

            // Set the user-type
            $query = 'SELECT `name` FROM `#__core_acl_aro_groups` WHERE `id`='.(int)$gid;
            $db->setQuery($query);
            $usertype = $db->loadResult();

            $query = 'UPDATE `#__users` SET `gid`='.(int)$gid.', `usertype`='.$db->Quote($usertype).' WHERE `id`='.(int)$user->id;
            $db->setQuery($query);
            $db->query();

            $query = 'UPDATE `#__core_acl_groups_aro_map` SET `group_id`='.(int)$gid.' WHERE `aro_id` = (SELECT id FROM `#__core_acl_aro` WHERE value = '.(int)$user->id.' LIMIT 1)';
            $db->setQuery($query);
            $db->query();

        // Joomla! 1.6 method
        } else {

            // See if the user is already listed
            $query = 'SELECT user_id FROM `#__user_usergroup_map` WHERE `user_id`='.(int)$user->id.' AND `group_id`='.(int)$gid.' LIMIT 1';
            $db->setQuery($query);
            $result = $db->loadResult();

            // Add the user
            if (empty($result)) {
                $query = 'INSERT INTO `#__user_usergroup_map` SET `user_id`='.(int)$user->id.', `group_id`='.(int)$gid;
                $db->setQuery($query);
                $db->query();
            }
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $gid
     * @param JUser $user
     * @return bool
     */
    public function onReverse($gid = null, $user = null)
    {
        $db = JFactory::getDBO();
        $query = 'DELETE FROM `#__user_usergroup_map` WHERE `user_id`='.(int)$user->id.' AND `group_id`='.(int)$gid;
        $db->setQuery($query);
        $db->query();

        // @todo: Update session of this user

        return true;
    }
}

