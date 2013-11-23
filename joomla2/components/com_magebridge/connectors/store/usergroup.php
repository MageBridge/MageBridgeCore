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
 * MageBridge Store-connector for Joomla! User Groups
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreUsergroup extends MageBridgeConnectorStore
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
     * Method to determine if the current usergroup matches the one configured in this connector
     * 
     * @param string $condition
     * @return bool
     */
    public function checkCondition($condition = null)
    {
        $user = JFactory::getUser();
        $condition = (int)$condition;
        if (!$condition > 0) {
            return false;
        }

        if (MageBridgeHelper::isJoomla15()) {
            if ($user->get('gid', 0) == $condition) {
                return true;
            }
        } else {
            if (is_array($user->groups) && in_array($condition, $user->groups)) {
                return true;
            }
        }
        return false;
    }
}
