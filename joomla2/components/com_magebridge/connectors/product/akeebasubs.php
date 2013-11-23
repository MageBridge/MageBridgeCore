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
 * MageBridge Product-connector for Akeeba Subscriptions
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAkeebasubs extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_akeebasubs');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `title`, `akeebasubs_level_id` AS `value` FROM `#__akeebasubs_levels`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'akeebasubs_level', null, 'value', 'title', $value);
        } else {
            return JText::_('No levels found');
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
        if (!empty($post['akeebasubs_level'])) {
            return $post['akeebasubs_level'];
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
    public function onPurchase($level_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT * FROM `#__akeebasubs_subscriptions` WHERE `user_id`='.(int)$user->id.' AND `akeebasubs_level_id`='.(int)$level_id.' LIMIT 1';
        $db->setQuery($query);
        $row = $db->loadObject();

        if (empty($row)) {
            $values = array(
                'user_id' => (int)$user->id,
                'akeebasubs_level_id' => (int)$level_id,
                'enabled' => 1,
                //'publish_up' => '',
                //'publish_down' => '',
                'processor' => 'none',
                'processor_key' => 'magento',
                'state' => 'X',
            );

            $query = 'INSERT INTO `#__akeebasubs_subscriptions` SET '.MageBridgeHelper::arrayToSql($values);
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
     * @return bool
     */
    public function onReverse($level_id = null, $user = null)
    {
        $db = JFactory::getDBO();

        $query = 'DELETE FROM `#__akeebasubs_subscriptions` WHERE `user_id`='.(int)$user->id.' AND `akeebasubs_level_id`='.(int)$level_id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

