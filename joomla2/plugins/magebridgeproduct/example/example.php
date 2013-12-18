<?php
/**
 * MageBridge Product plugin - Example
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Product Plugin - Example
 */
class plgMageBridgeProductExample extends MageBridgePlugin
{
    /*
     * Method to get some HTML-form
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

    /**
     * Event "onPurchase"
     * 
     * @access public
     * @param object $user Joomla! user object
     * @param tinyint $status Status of the current order
     * @return bool
     */
    public function onPurchase($user, $status)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Do your stuff after a product has been purchased

        return true;
    }

    /**
     * Event "onReverse"
     * 
     * @access public
     * @param object $user Joomla! user object
     * @return bool
     */
    public function onReverse($user)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Do your stuff to undo the actions done before

        return true;
    }

    /*
     * Method to check whether this plugin is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        // Check for the existance of a specific component
        return $this->checkComponent('com_example');
    }
}

