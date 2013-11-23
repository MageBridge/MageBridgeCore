<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Controller
 */
class MageBridgeWidgetHelper
{
    /*
     * Wrapper-method to get specific widget-data with caching options
     *
     * @param string $name
     * @return mixed
     */
    static public function getWidgetData($name = null)
    {
        switch($name) {
            case 'website':
                $function = 'getWebsites';
                break;

            case 'store':
                $function = 'getStores';
                break;

            case 'cmspage':
                $function = 'getCmspages';
                break;

            case 'theme':
                $function = 'getThemes';
                break;

            default:
                return null;
        }

        $cache = JFactory::getCache('com_magebridge_admin');
        $cache->setCaching(0);
        $result = $cache->call( array( 'MageBridgeWidgetHelper', $function ));
        return $result;
    }

    /*
     * Get a list of websites from the API
     *
     * @param null
     * @return array
     */
    static public function getWebsites()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $result = $bridge->getAPI('magebridge_websites.list');
        if (empty($result)) {

            // Register this request
            $register = MageBridgeModelRegister::getInstance();
            $register->add('api', 'magebridge_websites.list');

            // Build the bridge
            $bridge->build();

            // Send the request to the bridge
            $result = $bridge->getAPI('magebridge_websites.list');
        }
        return $result;
    }

    /*
     * Get a list of stores from the API
     *
     * @param null
     * @return array
     */
    static public function getStores()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $result = $bridge->getAPI('magebridge_storeviews.hierarchy');
        if (empty($result)) {

            // Register this request
            $register = MageBridgeModelRegister::getInstance();
            $id = $register->add('api', 'magebridge_storeviews.hierarchy');

            // Build the bridge
            $bridge->build();

            // Send the request to the bridge
            $result = $bridge->getAPI('magebridge_storeviews.hierarchy');
        }

        return $result;
    }

    /*
     * Get a list of CMS pages from the API
     *
     * @param null
     * @return array
     */
    static public function getCmspages()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $result = $bridge->getAPI('magebridge_cms.list');
        if (empty($result)) {

            // Register this request
            $register = MageBridgeModelRegister::getInstance();
            $register->add('api', 'magebridge_cms.list');

            // Build the bridge
            $bridge->build();

            // Send the request to the bridge
            $result = $bridge->getAPI('magebridge_cms.list');
        }

        return $result;
    }

    /*
     * Get a list of themes from the API
     *
     * @param null
     * @return array
     */
    static public function getThemes()
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $result = $bridge->getAPI('magebridge_theme.list');
        if (empty($result)) {

            // Register this request
            $register = MageBridgeModelRegister::getInstance();
            $id = $register->add('api', 'magebridge_theme.list');

            // Build the bridge
            $bridge->build();

            // Send the request to the bridge
            $result = $register->getDataById($id);
        }

        return $result;
    }
}
