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
 * MageBridge Product-connector for Communicator
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductCommunicator extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_communicator');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        return null;
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
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
        $query = 'SELECT * FROM `#__communicator_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        // Add the customer email to the subscribers table
        if (empty($row)) {
            $now = JFactory::getDate()->toMySQL();
            $fields = array(
                'user_id' => (int)$user->id,
                'subscriber_name' => $db->Quote($user->name),
                'subscriber_email' => $db->Quote($user->email),
                'confirmed' => 1,
                'subscribe_date' => $db->Quote($now),
            );

            $query = 'INSERT INTO `#__communicator_subscribers` SET '.MageBridgeHelper::arrayToSql($fields);
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

        $query = 'DELETE FROM `#__communicator_subscribers` WHERE `subscriber_email`='.$db->Quote($user->email);
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

