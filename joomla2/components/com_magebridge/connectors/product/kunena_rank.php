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
 * MageBridge Product-connector for Kununa Ranks
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductKunenaRank extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_kunena');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `rank_title` AS `title`, `rank_id` AS `value` FROM `#__kunena_ranks`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'kunena_rank', null, 'value', 'title', $value);
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
        if (!empty($post['kunena_rank'])) {
            return $post['kunena_rank'];
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
    public function onPurchase($rank_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT id FROM `#__kunena_users` WHERE `userid`='.(int)$user->id.' LIMIT 1';
        $db->setQuery($query);
        $user_id = $db->loadResult();

        // Add the customer email to the subscribers list
        if ($user_id > 0) {
            $query = 'UPDATE `#__kunena_users` SET `rank`='.(int)$rank_id.' WHERE `userid`='.(int)$user->id;
        } else {
            $query = 'INSERT INTO `#__kunena_users` SET `userid`='.(int)$user->id.', `rank`='.(int)$rank_id;
        }

        $db->setQuery($query);
        $db->query();

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $rank_id
     * @param JUser $user
     * @return bool
     */
    public function onReverse($rank_id = null, $user = null)
    {
        $db = JFactory::getDBO();
        $query = 'UPDATE `#__kunena_users` SET `rank`=0 WHERE `userid`='.(int)$user->id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

