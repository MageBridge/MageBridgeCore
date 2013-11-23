<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Product-connector for jNews
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJNews extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_jnews');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `list_name` AS `title`, `id` AS `value` FROM `#__jnews_lists`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'jnews_list', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['jnews_list'])) {
            return $post['jnews_list'];
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

        // See if the user is already listed in the subscribers-table
        $query = 'SELECT * FROM `#__jnews_subscribers` WHERE `user_id`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        // Add the customer to the subscribers-table
        $subscriber_id = 0;
        if (empty($rows)) {
            $fields = array(
                '`user_id`='.(int)$user->id,
                '`name`='.$db->Quote($user->name),
                '`email`='.$db->Quote($user->email),
                '`receive_html`=1',
                '`confirmed`=1',
                '`subscribe_date`='.time(),
            );
            $query = 'INSERT INTO `#__jnews_subscribers` SET '.implode(', ', $fields);
            $db->setQuery($query);
            $db->query();

            // See if the user is already listed in the subscribers-table
            $query = 'SELECT * FROM `#__jnews_subscribers` WHERE `user_id`='.(int)$user->id.' LIMIT 1';
            $db->setQuery($query);
            $row = $db->loadObject();
            if (!empty($row->id)) {
                $subscriber_id = $row->id;
            }

        } else {
            $subscriber_id = $row->id;
        }

        if ($subscriber_id > 0) {

            // See if this subscriber is already
            $query = 'SELECT * FROM `#__jnews_listssubscribers` WHERE `list_id`='.(int)$list_id.' AND `subscriber_id`='.(int)$subscriber_id.' LIMIT 1';
            $db->setQuery($query);
            $row = $db->loadObject();

            // Add the customer email to the subscribers list
            if (empty($row)) {
                // @todo: Use unsubdate feature of jNews for automatic reversal
                $fields = array(
                    '`list_id`='.(int)$list_id,
                    '`subscriber_id`='.(int)$subscriber_id,
                    '`subdate`=NOW()',
                );
                $query = 'INSERT INTO `#__jnews_listssubscribers` SET '.implode(', ', $fields);
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
        $query = 'DELETE FROM `#__jnews_listssubscribers` WHERE `list_id`='.(int)$list_id.' AND `subscriber_id`='.(int)$user->id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

