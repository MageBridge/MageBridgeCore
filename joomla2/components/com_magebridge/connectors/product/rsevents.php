<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Product-connector for RSEvents
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductRSEvents extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if($this->checkComponent('com_rsevents')) {
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
        $query = "SELECT `EventName` AS `title`, `IdEvent` AS `value` FROM `#__rsevents_events`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'rsevents_event', null, 'value', 'title', $value);
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
        if (!empty($post['rsevents_event'])) {
            return $post['rsevents_event'];
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
        $query = 'SELECT * FROM `#__rsevents_subscriptions` WHERE `IdUser`='.(int)$user->id.' AND `IdEvent`='.(int)$event_id;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Add the customer email to the subscribers list
        if (empty($rows)) {

            $values = array(
                'IdEvent' => (int)$event_id,
                'IdUser' => (int)$user->id,
                'FirstName' => $user->name,
                'LastName' => '-',
                'Email' => $user->email,
                'SubscriptionState' => 0,
                'SubscriptionTotalFee' => 0,
                'SubscriptionDate' => time(),
                'ValidationDate' => 0,
                'ConfirmationDate' => 0,
                'SubscriptionFormId' => 1,
            );
    
            $query_values = array();
            foreach ($values as $name => $value) {
                $query_values[] = "`$name`=".$db->Quote($value);
            }

            $query = 'INSERT INTO `#__rsevents_subscriptions` SET '.implode(',', $query_values);
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
        $query = 'DELETE FROM `#__rsevents_subscriptions` WHERE `Email`='.$db->Quote($user->email).' AND `IdEvent`='.(int)$value;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

