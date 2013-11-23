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
 * MageBridge Product-connector for Acajoom
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAcajoom extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_acajoom');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `list_name` AS `title`, `id` AS `value` FROM `#__acajoom_lists`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'acajoom_list', null, 'value', 'title', $value);
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
        if (!empty($post['acajoom_list'])) {
            return $post['acajoom_list'];
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
    public function onPurchase($list_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT id FROM `#__acajoom_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
        $db->setQuery($query);
        $subscriber_id = $db->loadResult();

        // Add the customer email to the subscribers list
        if (empty($subscriber_id)) {

            $values = array(
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'confirmed' => 1,
            );

            $query = 'INSERT INTO `#__acajoom_subscribers` SET '.MageBridgeHelper::arrayToSql($values).', `sdate`=NOW()';
            $db->setQuery($query);
            $db->query();
            $subscriber_id = $db->insertid();
        }

        if ($subscriber_id > 0) {

            // See if the user is already there
            $query = 'SELECT * FROM `#__acajoom_queue` WHERE `subscriber_id`='.(int)$subscriber_id.' AND `list_id`='.(int)$list_id.' LIMIT 1';
            $db->setQuery($query);
            $row = $db->loadObject();

            if (empty($row)) {

                $values = array(
                    'subscriber_id' => (int)$subscriber_id,
                    'list_id' => (int)$list_id,
                    'type' => 1,
                );

                $query = 'INSERT INTO `#__acajoom_queue` SET '.MageBridgeHelper::arrayToSql($values);
                $db->setQuery($query);
                $db->query();
            }
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $list_id
     * @param JUser $user
     * @return bool
     */
    public function onReverse($list_id = null, $user = null)
    {
        $db = JFactory::getDBO();

        // See if the user is there
        $query = 'SELECT id FROM `#__acajoom_subscribers` WHERE `email`='.$db->Quote($user->email).' LIMIT 1';
        $db->setQuery($query);
        $subscriber_id = $db->loadResult();

        if ($subscriber_id > 0) {
            $query = 'DELETE FROM `#__acajoom_queue` WHERE `subscriber_id`='.(int)$subscriber_id.' AND `list_id`='.(int)$list_id;
            $db->setQuery($query);
            $db->query();
        }

        return true;
    }
}

