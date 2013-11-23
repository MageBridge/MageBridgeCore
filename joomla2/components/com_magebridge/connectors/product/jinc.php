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
 * MageBridge Product-connector for JINC
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJinc extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_jinc');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `grp_name` AS `title`, `grp_id` AS `value` FROM `#__jinc_group`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'jinc_group', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['jinc_group'])) {
            return $post['jinc_group'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param string $group_id
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($group_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT mem_id FROM `#__jinc_membership` WHERE `mem_user_id`='.$user->id.' AND `mem_grp_id`='.(int)$group_id;
        $db->setQuery($query);
        $membership_id = $db->loadResult();

        // Add the customer email to the membership list
        if (empty($membership_id)) {
            $query = 'INSERT INTO `#__jinc_membership` SET `mem_user_id`='.$user->id.' AND `mem_grp_id`='.(int)$group_id;
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $group_id
     * @param JUser $user
     * @return bool
     */
    public function onReverse($group_id = null, $user = null)
    {
        $db = JFactory::getDBO();
        $query = 'DELETE FROM `#__jinc_membership` WHERE `mem_user_id`='.(int)$user->id.' AND `mem_grp_id`='.(int)$group_id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}
