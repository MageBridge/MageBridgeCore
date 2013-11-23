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
 * MageBridge Product-connector for Eventlist
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductEventlist extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_eventlist');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `title`, `id` AS `value` FROM `#__eventlist_events`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'eventlist_event_id', null, 'value', 'title', $value);
        } else {
            return JText::_('No lists found');
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
        if (!empty($post['eventlist_event_id'])) {
            return $post['eventlist_event_id'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param string $list_id
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($event_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT id FROM `#__eventlist_register` WHERE `event`='.(int)$event_id.' AND `uid`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        // Add the customer email to the subscribers list
        if (empty($row)) {

            $values = array(
                'event' => (int)$event_id,
                'uid' => (int)$user->id,
                'uip' => '127.0.0.1',
            );

            $query = 'INSERT INTO `#__eventlist_register` SET '.MageBridgeHelper::arrayToSql($values).', `uregdate`=NOW()';
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $list_id
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onReverse($event_id = null, $user = null)
    {
        $db = JFactory::getDBO();

        // Remove the user from the registration
        $query = 'DELETE FROM `#__eventlist_register` WHERE `event`='.(int)$event_id.' AND `uid`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $db->query();
    }
}

