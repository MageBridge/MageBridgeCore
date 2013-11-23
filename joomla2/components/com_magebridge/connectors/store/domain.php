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
 * MageBridge Store-connector for displaying specific stores under specific domainnames
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreDomain extends MageBridgeConnectorStore
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
        $html = '<p>'.JText::_('Enter the domain name. For instance: www.example.com').'</p>';
        $html .= '<input type="text" name="domain_name" value="'.$value.'" />';
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
        if (isset($post['domain_name'])) {
            return $post['domain_name'];
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
        if (!empty($condition) && $condition == $_SERVER['HTTP_HOST']) {
            return true;
        }

        return false;
    }
}
