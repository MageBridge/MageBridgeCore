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
 * MageBridge Product-connector for MkPostman
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductMkPostman extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_mkpostman');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `title`, `id` AS `value` FROM `#__mkpostman_lists`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'mkpostman_list', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['mkpostman_list'])) {
            return $post['mkpostman_list'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param string $value
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($list_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT * FROM `#__mkpostman_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        // Add the customer email to the subscribers table
        if (empty($row)) {
            $now = JFactory::getDate()->toMySQL();
            $fields = array(
                'user_id' => (int)$user->id,
                'name' => $db->Quote($user->name),
                'email' => $db->Quote($user->email),
                'status' => 1,
                'registration_date' => $db->Quote($now),
                'confirmation_date' => $db->Quote($now),
            );

            $query = 'INSERT INTO `#__mkpostman_subscribers` SET '.MageBridgeHelper::arrayToSql($fields);
            $db->setQuery($query);
            $db->query();

            // See if the user is already there
            $query = 'SELECT * FROM `#__mkpostman_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
            $db->setQuery($query);
            $row = $db->loadObject();
        }

        // Continue to add the subscriber to the actual list
        if (!empty($row->id)) {
            $subscriber_id = $row->id;
            $query = 'SELECT * FROM `#__mkpostman_subscribers_lists` WHERE `subscriber_id`='.(int)$subscriber_id.' AND `list_id`='.(int)$list_id;
            $db->setQuery($query);
            $row = $db->loadObject();

            if (empty($row)) {
                $query = 'INSERT INTO `#__mkpostman_subscribers_lists` SET `subscriber_id`='.(int)$subscriber_id.', `list_id`='.(int)$list_id;
                $db->setQuery($query);
                $db->query();
            }
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $value
     * @param JUser $user
     * @return bool
     */
    public function onReverse($list_id = null, $user = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT * FROM `#__mkpostman_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        // If the user is there, we can use its ID
        if (!empty($row->id)) {
            $query = 'DELETE FROM `#__mkpostman_subscribers_lists` WHERE `subscriber_id`='.(int)$row->id.' AND `list_id`='.(int)$list_id;
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }
}

