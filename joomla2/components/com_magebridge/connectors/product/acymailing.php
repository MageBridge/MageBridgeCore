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
 * MageBridge Product-connector for Acymailing
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAcymailing extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_acymailing');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name` AS `title`, `listid` AS `value` FROM `#__acymailing_list`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        return JHTML::_('select.genericlist', $options, 'acymailing_list', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['acymailing_list'])) {
            return $post['acymailing_list'];
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
    public function onPurchase($list_id = null, $user = null, $status = null)
    {
        if (!include_once(JPATH_ADMINISTRATOR.'/components/com_acymailing/helpers/helper.php')){
            return false;
        }

        // See if the user exists in the database
        $acyUser = null;
        $acyUser->email = $user->email;
        $acyUser->name = $user->name;
        $acyUser->userid = $user->id;

        $subscriberClass = acymailing::get('class.subscriber');
        $subscriberClass->checkVisitor = false;
        $subid = $subscriberClass->save($acyUser);

        if (empty($subid)) return false;
        if (empty($list_id)) return true;

        $newSubscription = array();

        $newList = null;
        $newList['status'] = 1;
        $newSubscription[intval($list_id)] = $newList;

        $subscriberClass->saveSubscription($subid,$newSubscription);

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
        if (empty($list_id) || empty($user->id)) return false;

        if (!include_once(JPATH_ADMINISTRATOR.'/components/com_acymailing/helpers/helper.php')){
            return false;
        }

        $subscriberClass = acymailing::get('class.subscriber');
        $subid = $subscriberClass->get($user->id);

        $newSubscription = array();

        $newList = null;
        $newList['status'] = 0;
        $newSubscription[intval($list_id)] = $newList;

        $subscriberClass->saveSubscription($subid,$newSubscription);

        return true;
    }
}

