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
 * MageBridge Product-connector for RSEventsPro
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductRSEventsPro extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if($this->checkComponent('com_rseventspro')) {
            return true;
        }
        return false;
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name` AS `title`, `id` AS `value` FROM `#__rseventspro_events`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'rseventpros_event', null, 'value', 'title', $value);
        } else {
            return JText::_('No events found');
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
        if (!empty($post['rseventpros_event'])) {
            return $post['rseventpros_event'];
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
    public function onPurchase($event_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // Check for a valid user
        $query = 'SELECT `id` FROM `#__rseventspro_users` WHERE `ide`='.(int)$event_id.' AND `idu`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $user_event_id = $db->loadResult();

        // Create the user if needed
        if(empty($user_id)) {
            $query_values = array();
            $query_values[] = '`idu`='.(int)$user->id;
            $query_values[] = '`ide`='.(int)$event_id;
            $query_values[] = '`name`='.(int)$user->name;
            $query_values[] = '`email`='.(int)$user->email;
            $query_values[] = '`date`='.date('Y-m-d H:i:s');
            $query_values[] = '`state`=1';
            $query = 'INSERT INTO `#__rseventspro_users` SET '.implode(',', $query_values);
            $db->setQuery($query);
            $db->query();
            $user_event_id = $db->insertId();
        }

        // See if the user is already subscribed
        $query = 'SELECT * FROM `#__rseventspro_user_tickets` WHERE `ide`='.(int)$user_event_id;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Add the customer email to the subscribers list
        if (empty($rows)) {

            $values = array(
                'ids' => (int)$user_event_id,
                'quantity' => 1,
            );
    
            $query_values = array();
            foreach ($values as $name => $value) {
                $query_values[] = "`$name`=".$db->Quote($value);
            }

            $query = 'INSERT INTO `#__rseventspro_user_tickets` SET '.implode(',', $query_values);
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
    public function onReverse($event_id = null, $user = null)
    {
        $db = JFactory::getDBO();

        // Check for a valid user
        $query = 'SELECT `id` FROM `#__rseventspro_users` WHERE `ide`='.(int)$event_id.' AND `idu`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $user_event_id = $db->loadResult();

        // Delete the user-record
        $query = 'DELETE FROM `#__rseventspro_users` WHERE `ide`='.(int)$event_id.' AND `idu`='.(int)$user->id;
        $db->setQuery($query);
        $db->query();

        // Delete the ticket-record
        $query = 'DELETE FROM `#__rseventspro_user_tickets` WHERE `id`='.(int)$user_event_id;
        $db->setQuery($query);
        $db->query();
        return true;
    }
}

