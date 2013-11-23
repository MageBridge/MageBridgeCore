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
 * MageBridge Product-connector for AEC Membership
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAcctexp extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_acctexp');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        $query = "SELECT `name`, `id` FROM `#__acctexp_plans`";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        if (!empty($options)) {
            return JHTML::_('select.genericlist', $options, 'acctexp_plan', null, 'id', 'name', $value);
        } else {
            return JText::_('No plans found');
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
        if (!empty($post['acctexp_plan'])) {
            return $post['acctexp_plan'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param string $plan_id
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($plan_id = null, $user = null, $status = null)
    {
        $db = JFactory::getDBO();

        // See if the user is already there
        $query = 'SELECT * FROM `#__acctexp_subscr` WHERE `userid`='.(int)$user->id;
        $db->setQuery($query);
        $row = $db->loadObject();

        // Expiry = 1 year
        // @todo: Make this configurable 
        $expiration = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s', mktime()) . ' + 1 year'));

        if (empty($row)) {
            $values = array(
                'userid' => (int)$user->id,
                'primary' => 1,
                'type' => 'none',
                'status' => 'Active',
                'signup_date' => JFactory::getDate()->toMySQL(),
                'lastpay_date' => JFactory::getDate()->toMySQL(),
                'plan' => (int)$plan_id,
                'expiration' => $expiration,
            );
            $query = 'INSERT INTO `#__acctexp_subscr` SET '.MageBridgeHelper::arrayToSql($values);
        } else {
            $query = 'UPDATE `#__acctexp_subscr` SET `plan`="'.(int)$plan_id.'" WHERE `userid`='.(int)$user->id;
        }
        
        $db->setQuery($query);
        $db->query();

        // Fully apply the plan
	    include_once JPATH_ROOT.'/components/com_acctexp/acctexp.class.php';
        if(class_exists('metaUser')) {
	        $metaUser = new metaUser( $user->id );
	        $plan = new SubscriptionPlan($db);
        	$plan->load( $plan_id );
        	$metaUser->establishFocus( $plan );
	        $metaUser->focusSubscription->applyUsage( $plan_id, 'none', 1 );
        }

        return true;
    }

    /*
     * Method to execute when this connector is reversed
     * 
     * @param string $plan_id
     * @param JUser $user
     * @return bool
     */
    public function onReverse($plan_id = null, $user = null)
    {
        $db = JFactory::getDBO();
        $query = 'UPDATE `#__acctexp_subscr` SET `plan`="", `previous_plan`='.(int)$plan_id.' WHERE `userid`='.(int)$user->id;
        $db->setQuery($query);
        $db->query();

        return true;
    }
}

