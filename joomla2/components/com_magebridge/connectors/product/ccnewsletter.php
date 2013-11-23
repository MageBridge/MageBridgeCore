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
 * MageBridge Product-connector for ccNewsletter
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductCcNewsletter extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_ccnewsletter');
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
    public function onPurchase($value = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT * FROM `#__ccnewsletter_subscribers` WHERE `email`='.$db->Quote($user->email);
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        // Add the customer email to the subscribers list
        if (empty($rows)) {
            $query = 'INSERT INTO `#__ccnewsletter_subscribers` SET `name`='.$db->Quote($user->name).', `email`='.$db->Quote($user->email).', `sdate`=NOW()';
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
        $query = 'DELETE FROM `#__ccnewsletter_subscribers` WHERE `email`='.$db->Quote($user->email);
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

