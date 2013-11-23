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
 * MageBridge Store-connector for determining the store by a GET-variable
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreGet extends MageBridgeConnectorStore
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
     * Method to check whether this connector is visible or not
     * 
     * @param null
     * @return bool
     */
    public function isVisible()
    {
        return false;
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
     * @return mixed
     */
    public function getFormPost($post = array())
    {
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
        return false;
    }
}
