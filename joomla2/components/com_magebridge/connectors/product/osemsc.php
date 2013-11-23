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
 * MageBridge Product-connector for Open Source Excellence Membership Control
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductOsemsc extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_ssrrn_msc');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        // Parse the combined value
        $msc = explode(':', $value);
        if (count($msc) == 3) {
            $msc_id = (int)$msc[0];
            $msc_period = (int)$msc[1];
            $msc_periodtype = $msc[2];
        } else {
            $msc_id = 0;
            $msc_period = 1;
            $msc_periodtype = 'month';
        }

        // Initialize the HTML
        $html = '';

        // Construct the membership selection
        $query = 'SELECT `id` AS `value`, `memberlist_name` AS `text` FROM `#__ssrrn_msc`';
        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();
        array_unshift($options, array( 'value' => 0, 'text' => '-- Select --'));
        $html .= JHTML::_('select.genericlist', $options, 'msc_id', null, 'value', 'text', $msc_id);
        $html .= '<br/><br/>';

        // Construct the membership period
        $html .= '<input type="text" size="4" name="msc_period" value="'.(int)$msc_period.'" />';

        // Construct the membership period-type
        $options = array(
            array( 'value' => 'day', 'text' => 'Days'),
            array( 'value' => 'week', 'text' => 'Weeks'),
            array( 'value' => 'month', 'text' => 'Months'),
            array( 'value' => 'year', 'text' => 'Years'),
        );
        $html .= JHTML::_('select.genericlist', $options, 'msc_periodtype', null, 'value', 'text', $msc_periodtype);

        return $html;
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (isset($post['msc_id']) && isset($post['msc_period']) && isset($post['msc_periodtype'])) {
            $value = (int)$post['msc_id'].':'.(int)$post['msc_period'].':'.$post['msc_periodtype'];
            return $value;
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
    public function onPurchase($value = null, $user = null, $status = null)
    {
        // Parse the combined value
        $msc = explode(':', $value);
        if (count($msc) == 3) {
            $msc_id = (int)$msc[0];
            $msc_period = (int)$msc[1];
            $msc_periodtype = $msc[2];
        } else {
            return false;
        }

        // Save the membership
        return $this->saveMembership($user->id, $msc_id, $msc_period, $msc_periodtype);
    }

    /**
     * Method to actually save the membership
     *
     * @param int $user_id
     * @param int $msc_id
     * @param int $msc_period
     * @return bool
     */
    private function saveMembership($user_id = 0, $msc_id = 0, $msc_period = 0, $msc_periodtype)
    {
        if (!$user_id > 0 || !$msc_id > 0 || !$msc_period > 0) {
            return false;
        }

        // Get system variables
        $db = JFactory::getDBO();

        // Import the JDate-library
        jimport('joomla.utilities.date');

        // Load an object of the MSC-group
        $query = 'SELECT * FROM `#__ssrrn_msc` WHERE `id`="'.$msc_id.'" LIMIT 1';
        $db->setQuery($query);
        $msc_group = $db->loadObject();

        // Load an object of the MSC-membership for this user
        $query = 'SELECT * FROM `#__ssrrn_msc_member` '
            . 'WHERE `member_id`="'.$user_id.'" AND `msc_id`="'.$msc_id.'" LIMIT 1';
        $db->setQuery($query);
        $member = $db->loadObject();

        // Initialize the query-segments for building the MySQL query
        $query_segments = array(
            '`msc_id`="'.$msc_id.'"',
            '`member_id`="'.$user_id.'"',
        );

        // New entry
        if (empty($member)) {
            $start_date = new JDate();
            $start_date = (method_exists('JDate', 'toSql')) ? $start_date->toSql() : $start_date->toMySQL();

            $expired_date = new JDate($this->getTimestampAfterX(time(), $msc_period, $msc_periodtype));
            $expired_date = (method_exists('JDate', 'toSql')) ? $expired_date->toSql() : $expired_date->toMySQL();

            $query_segments[] = '`start_date`='.$db->Quote($start_date);
            $query_segments[] = '`expired_date`='.$db->Quote($expired_date);
            $query = 'INSERT INTO `#__ssrrn_msc_member` SET '.implode(', ', $query_segments);

            // Update the table
            $db->setQuery($query);
            $db->query();

            // Use this information to add this user the appropriate group
            if (isset($msc_group->phpbb_group_id)) {
                $this->addUserToPhpbb($msc_group->phpbb_group_id, $user_id);
            }

        // Existing entry
        } else {

            $expired_date = new JDate($this->getTimestampAfterX(time(), $msc_period, $msc_periodtype));
            $expired_date = (method_exists('JDate', 'toSql')) ? $expired_date->toSql() : $expired_date->toMySQL();

            $query_segments[] = '`expired_date`='.$db->Quote($expired_date);
            $query = 'UPDATE `#__ssrrn_msc_member` SET '.implode(', ', $query_segments)
                . ' WHERE `member_id`="'.$user_id.'" AND `msc_id`="'.(int)$msc_id.'"';

            // Update the table
            $db->setQuery($query);
            $db->query();
        }
    }


    /*
     * Use the MSC-membership tool and JFusion to dump this user into the right phpBB-group
     *
     * @param int $forum_group_id
     * @param int $user_id
     * @return bool
     */
    private function addUserToPhpbb($forum_group_id = 0, $user_id = 0)
    {
        // Initial checks
        if (!$forum_group_id > 0 || !$user_id > 0) {
            return false;
        }

        // Setup the phpBB-class
        $phpbb_class = JPATH_ADMINISTRATOR.'/components/com_ssrrn_msc/phpbb/phpbb.php';
        if (!file_exists($phpbb_class)) {
            return false;
        }
        require_once ($phpbb_class);
        $phpbb = new phpbb();

        // Fetch the right php_bbuser_id from JFusion
        $query = 'SELECT `userid` FROM `#__jfusion_users_plugin` WHERE `id`="'.(int)$user_id.'" AND `jname`="phpbb3"';
        $db = JFactory::getDBO();
        $db->setQuery($query);
        $forum_user_id = $db->loadResult();

        // Connect to phpBB
        $check = $phpbb->connect_phpbb();
        if ($check == '0') {
            return false;
        }

        // If we are connected and we have proper IDs
        if ($forum_user_id > 0 && $forum_group_id > 0) {

            try {
                $query = 'SELECT * FROM `#__user_group` WHERE `group_id`="'.$forum_group_id.'" AND `user_id`="'.$forum_user_id.'"';
                $phpbb->setQuery($query);
                $result = $phpbb->loadObjectList();
            } catch(Exception $e) {
            }

            if (empty($result)) {
                try {
                    $query = 'INSERT INTO `#__user_group` SET `group_id`="'.$forum_group_id.'", `user_id`="'.$forum_user_id.'",
                    `user_pending`="0"';
                    $phpbb->setQuery($query);
                    $phpbb->query();
                } catch(Exception $e) {
                }
            }
        }
    }

    /*
     * Calculate the timestamp after X months from $timestamp
     *
     * @param int $date
     * @param int $months
     * @return int
     */
    private function getTimestampAfterX($timestamp = null, $number = 0, $type = 'month')
    {
        switch($type) {
            case 'day':
                $timestamp = $timestamp + (mktime(date('H'), date('i'), date('s'), date('m'), date('d') + $number, date('Y'))) - time();
                break;
            case 'week':
                $timestamp = $timestamp + (mktime(date('H'), date('i'), date('s'), date('m'), date('d') + ($number * 7), date('Y'))) - time();
                break;
            case 'month':
                $timestamp = $timestamp + (mktime(date('H'), date('i'), date('s'), date('m') + $number, date('d'), date('Y'))) - time();
                break;
            case 'year':
                $timestamp = $timestamp + (mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y') + $number)) - time();
                break;
        }
        return $timestamp;
    }
}

