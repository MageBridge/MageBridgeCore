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
 * MageBridge Product-connector for FLEXIaccess
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductFLEXIaccess extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_flexiaccess');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name` AS `title`, `id` AS `value` FROM `#__flexiaccess_groups` ORDER BY `name`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();

        $option = (object)null;
        $option->value = 0;
        $option->title = '[Create new FLEXIaccess group]';
        array_unshift($options, $option);

        return JHTML::_('select.genericlist', $options, 'flexiaccess_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (isset($post['flexiaccess_group'])) {

            // Choose an existing group
            if ($post['flexiaccess_group'] > 0) {
                return $post['flexiaccess_group'];
            }

            // Create a new group
            $db = JFactory::getDBO();
            $label = $db->Quote($post['label']);
            $query = "INSERT INTO `#__flexiaccess_groups` SET `name`=".$label.", `type`=1, `level`=2";
            $db->setQuery($query);
            $db->query();

            $query = "SELECT * FROM `#__flexiaccess_groups` WHERE `name`=".$label." AND `type`=1 AND `level`=2";
            $db->setQuery($query);
            $row = $db->loadObject();
            return $row->id;

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

        // Get the FLEXIaccess group
        $db = JFactory::getDBO();
        $query = "SELECT * FROM `#__flexiaccess_members` WHERE `group_id`=".(int)$groupid." AND `member_id`=".(int)$user->id;
        $db->setQuery($query);
        $row = $db->loadObject();

        if (empty($row)) {
            $query = "INSERT INTO `#__flexiaccess_members` SET `group_id`=".(int)$groupid.", `member_id`=".(int)$user->id;
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
