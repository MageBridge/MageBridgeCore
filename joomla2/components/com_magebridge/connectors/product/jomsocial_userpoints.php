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
 * MageBridge Product-connector for JomSocial User Points
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductJomsocialUserpoints extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_community');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        return '<input type="text" name="jomsocial_points" value="'.(int)$value.'" size="4" />';
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['jomsocial_points'])) {
            return (int)$post['jomsocial_points'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     *
     * @param int $points
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($points = null, $user = null, $status = null)
    {
        // @todo: Is this correct? Shouldn't we calculate the difference with existing points?

        // Update the points
        $db = JFactory::getDBO();
        $query = "UPDATE `#__community_users` SET `points`=".(int)$points." WHERE `userid`=".(int)$user->id;
        $db->setQuery($query);
        $db->query();

        return true;
    }

    /*
     * Method to execute when this connection is reversed
     *
     * @param int $points
     * @param JUser $user
     * @return bool
     */
    public function onReverse($points = null, $user = null)
    {
        // @todo: Update the points
    }
}
