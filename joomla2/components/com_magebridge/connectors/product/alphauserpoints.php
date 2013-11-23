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
 * MageBridge Product-connector for AlphaUserPoints
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductAlphaUserPoints extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return $this->checkComponent('com_alphauserpoints');
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $args
     * @return string
     */
    public function getFormField($args = null)
    {
        // Split up the arguments
        $args = explode('|', $args);
        $rule = $args[0];
        $points = $args[1];
        $sku = $args[2];

        $query = "SELECT `rule_name` AS `title`, `plugin_function` AS `value` FROM `#__alpha_userpoints_rules` WHERE `published`='1'";

        $db = JFactory::getDBO();
        $db->setQuery($query);
        $options = $db->loadObjectList();

        if (!empty($options)) {
            $html = '<span class="sublabel">'.JText::_('Rule').'</span>';
            $html .= JHTML::_('select.genericlist', $options, 'aup_rule', null, 'value', 'title', $rule);
            $html .= '<br/>';
            $html .= '<span class="sublabel">'.JText::_('Points').'</span>';
            $html .= '<input type="text" name="aup_points" value="'.(int)$points.'" size="4" />';
            return $html;
        } else {
            return JText::_('No rules found');
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
        if (!empty($post['aup_rule']) && !empty($post['sku'])) {
            return $post['aup_rule'].'|'.(int)$post['aup_points'].'|'.$post['sku'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     *
     * @param string $args
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($args = null, $user = null, $status = null)
    {
        // Split up the arguments
        $args = explode(';', $args);
        $rule = $args[0];
        $points = $args[1];
        $sku = $args[2];

        $aup = JPATH_SITE.'/components/com_alphauserpoints/helper.php';
        if (file_exists($aup)) {
            require_once($aup);

            $aupid = AlphaUserPointsHelper::getAnyUserReferreID($user->id);
            if ($aupid) AlphaUserPointsHelper::newpoints($rule, $aupid, $sku, null, $points);
        }
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
