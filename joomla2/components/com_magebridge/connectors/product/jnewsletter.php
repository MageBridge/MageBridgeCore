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
 * MageBridge Product-connector for jNewsletter
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJNewsletter extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_jnewsletter');
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

        // See if the user is already there
        $query = 'SELECT * FROM `#__jnews_listssubscribers` WHERE `list_id`='.(int)$list_id.' AND `subscriber_id`='.(int)$user->id;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Add the customer email to the subscribers list
        if (empty($rows)) {
            // @todo: Use unsubdate feature of jNews for automatic reversal
            $query = 'INSERT INTO `#__jnews_listssubscribers` SET `list_id`='.(int)$list_id.', `subscriber_id`='.(int)$user->id.', `subdate`=NOW()';
            $db->setQuery($query);
            $db->query();
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

