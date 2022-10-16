<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Block helper for usage in Joomla!
 */
class MageBridgeStoreHelper
{
    /**
     * Instance variable
     */
    protected static $_instance = null;

    /**
     * Singleton
     *
     * @access public
     *
     * @param null
     *
     * @return MageBridgeStoreHelper
     */
    public static function getInstance()
    {
        static $instance;

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Method to get the current Magento application-type
     *
     * @param null
     *
     * @return null
     */
    public function getAppType()
    {
        if (empty($this->app_type)) {
            $this->setApp();
        }

        return $this->app_type;
    }

    /**
     * Method to get the current Magento application-value
     *
     * @param null
     *
     * @return null
     */
    public function getAppValue()
    {
        if (empty($this->app_value)) {
            $this->setApp();
        }

        return $this->app_value;
    }

    /**
     * Method to get the current Magento application-type
     *
     * @param null
     *
     * @return null
     */
    private function setApp()
    {
        // If the values are already initialized, return them
        if (!empty($this->app_type) && !empty($this->app_value)) {
            return;
        }

        // Initialize system variables
        $application = JFactory::getApplication();

        // Check if the current Menu-Item has something to say about this
        $store = MageBridgeHelper::getParams()
            ->get('store');
        $website = MageBridgeHelper::getParams()
            ->get('website');

        if (!empty($store) && $store = explode(':', $store)) {
            if ($store[0] == 'v') {
                $this->app_type = 'store';
                $this->app_value = $store[1];
                $application->setUserState('magebridge.store.type', $this->app_type);
                $application->setUserState('magebridge.store.name', $this->app_value);

                return;
            }

            if ($store[0] == 'g') {
                $this->app_type = 'group';
                $this->app_value = $store[1];
                $application->setUserState('magebridge.store.type', $this->app_type);
                $application->setUserState('magebridge.store.name', $this->app_value);

                return;
            }
        } elseif (!empty($website)) {
            $this->app_type = 'website';
            $this->app_value = $website;
            $application->setUserState('magebridge.store.type', $this->app_type);
            $application->setUserState('magebridge.store.name', $this->app_value);

            return;
        }

        // Check whether the GET-connector is enabled
        jimport('joomla.plugin.helper');

        if (JPluginHelper::isEnabled('magebridgestore', 'get')) {
            // Check for GET-variables __store
            $store = $application->getUserState('___store');
            if (!empty($store)) {
                $this->app_type = 'store';
                $this->app_value = $store;

                return;
            }

            // Check if the current store is saved with the user session
            $saved_type = $application->getUserState('magebridge.store.type');
            $saved_name = $application->getUserState('magebridge.store.name');

            if (!empty($saved_type) && !empty($saved_name)) {
                $this->app_type = $saved_type;
                $this->app_value = $saved_name;

                return;
            }
        }

        // Determine the current store using MageBridge Store Plugins
        if ($application->isSite()) {
            $store = MageBridgeConnectorStore::getInstance()
                ->getStore();

            if (!empty($store)) {
                $this->app_type = $store['type'];
                $this->app_value = $store['name'];

                return;
            }
        }

        // Load the settings from the database
        $storeview = MageBridgeModelConfig::load('storeview');
        $storegroup = MageBridgeModelConfig::load('storegroup');
        $website = MageBridgeModelConfig::load('website');

        // Never use a Store View or Store Group in the backend
        if ($application->isAdmin()) {
            if (JFactory::getApplication()->input->getCmd('view') == 'root') {
                $this->app_type = 'website';
                $this->app_value = 'admin';
            } else {
                $this->app_type = 'website';
                $this->app_value = $website;
            }

            return;
        }

        // When in the frontend, determine which store-type to use
        if (!empty($storeview)) {
            $this->app_type = 'store';
            $this->app_value = $storeview;
        } else {
            if (!empty($storegroup)) {
                $this->app_type = 'group';
                $this->app_value = $storegroup;
            } else {
                $this->app_type = 'website';
                $this->app_value = $website;
            }
        }

        return;
    }
}
