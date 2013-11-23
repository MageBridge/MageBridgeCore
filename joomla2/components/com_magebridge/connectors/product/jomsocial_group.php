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
 * MageBridge Product-connector for JomSocial Groups
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJomsocialGroup extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_community');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name` AS `title`, `id` AS `value` FROM `#__community_groups` WHERE `published`=1";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'jomsocial_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     * 
     * @param array $post
     * @return string
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['jomsocial_group'])) {
            return $post['jomsocial_group'];
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
        // Get the current group-configuration
        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM `#__community_groups_members` WHERE `groupid`=".(int)$groupid." AND `memberid`=".(int)$user->id);
        $rows = $db->loadObjectList();

        if (empty($rows)) {
            $query = "INSERT INTO `#__community_groups_members` SET `groupid`=".(int)$groupid.", `memberid`=".(int)$user->id.", approved=1, permissions=0";
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }

    /*
     * Method to execute when this connection is reversed
     *
     * @param int $groupid
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onReverse($groupid = null, $user = null)
    {
        // Get the current group-configuration
        $db = JFactory::getDBO();
        $db->setQuery("DELETE FROM `#__community_groups_members` WHERE `groupid`=".(int)$groupid." AND `memberid`=".(int)$user->id);
        $rows = $db->loadObjectList();

        if (empty($rows)) {
            $query = "INSERT INTO `#__community_groups_members` SET `groupid`=".(int)$groupid.", `memberid`=".(int)$user->id.", approved=1, permissions=0";
            $db->setQuery($query);
            $db->query();
        } else {
            $query = "UPDATE `#__community_groups_members` SET approved=1, permissions=0 WHERE `groupid`=".(int)$groupid." AND `memberid`=".(int)$user->id;
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }
}
