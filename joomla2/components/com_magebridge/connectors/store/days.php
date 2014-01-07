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
 * MageBridge Store-connector for displaying specific stores on certain days
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreDays extends MageBridgeConnectorStore
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        JHTML::_('behavior.calendar');
        if (!empty($value)) {
            $value = explode(' / ', $value);
            $from = $value[0];
            $to = $value[1];
        } else {
            $from = null;
            $to = null;
        }

        $html = null;
        $html .= '<span class="input-span">'.JText::_('From') . '&nbsp;</span>';
        $html .= JHTML::_('calendar', $from, 'days[from]', 'days_from', '%Y-%m-%d');
        $html .= '<span class="input-span">&nbsp;' . JText::_('till') . '&nbsp;</span>';
        $html .= JHTML::_('calendar', $to, 'days[to]', 'days_to', '%Y-%m-%d');
        return $html;
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return mixed
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['days'])) {
            return $post['days']['from'] . ' / ' . $post['days']['to'];
        }
        return null;
    }

    /*
     * Method to check whether the given condition is true
     * 
     * @param string $condition
     * @return bool
     */
    public function checkCondition($condition = null)
    {
        if (!empty($condition)) {
            $value = explode(' / ', $condition);
            $from = explode('-', $value[0]);
            $to = explode('-', $value[1]);
            
            $from_stamp = mktime(0, 0, 0, $from[1], $from[2], $from[0]);
            $to_stamp = mktime(0, 0, 0, $to[1], $to[2], $to[0]);
            if (time() > $from_stamp && time() < $to_stamp) {
                return true;
            }
        }

        return false;
    }
}
