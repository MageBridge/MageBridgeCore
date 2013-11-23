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
 * MageBridge Product-connector for Ohanah
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductOhanah extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_ohanah');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `title`, `ohanah_event_id` AS `value` FROM `#__ohanah_events`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'ohanah_event', null, 'value', 'title', $value);
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
        if (!empty($post['ohanah_event'])) {
            return $post['ohanah_event'];
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

        // See if the user is already subscribed
        $query = 'SELECT * FROM `#__ohanah_registrations` WHERE `email`='.$user->email.' AND `ohanah_event_id`='.(int)$event_id;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Add the customer email to the subscribers list
        if (empty($rows)) {

            $values = array(
                'ohanah_event_id' => (int)$event_id,
                'name' => $user->name,
                'email' => $user->email,
                'number_of_tickets' => 1,
                'paid' => 1,
                'checked_in' => 0,
            );
    
            $query_values = array();
            foreach ($values as $name => $value) {
                $query_values[] = "`$name`=".$db->Quote($value);
            }

            $query = 'INSERT INTO `#__ohanah_registrations` SET '.implode(',', $query_values);
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
    public function onReverse($value = null, $user = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'DELETE FROM `#__ohanah_registrations` WHERE `email`='.$db->Quote($user->email).' AND `ohanah_event_id`='.(int)$value;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

