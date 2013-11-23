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
 * MageBridge Store-connector class
 *
 * @package MageBridge
 */
class MageBridgeConnectorStore extends MageBridgeConnector
{
    /*
     * Singleton variable
     */
    private static $_instance = null;

    /*
     * Associated array of options 
     */
    private $options = array();

    /*
     * Singleton method
     *
     * @param null
     * @return MageBridgeConnectorStore
     */
    public static function getInstance()
    {
        static $instance;

        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
     * Method to return options
     *
     * @param null
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /*
     * Method to get the current store 
     *
     * @param null
     * @return array
     */
    public function getStore()
    {
        // If the database configuration specified no stores, skip this step
        if (MagebridgeModelConfig::load('load_stores') == 0) {
            return null;
        }

        // Get the conditions
        $db = JFactory::getDBO();
        $db->setQuery("SELECT * FROM #__magebridge_stores WHERE `published`=1 ORDER BY `ordering`");
        $conditions = $db->loadObjectList();
        if (empty($conditions)) {
            return null;
        }

        // Get the connectors
        $connectors = $this->getConnectors();

        // Try to match a condition with one of the connectors
        foreach ($conditions as $condition) {
            foreach ($connectors as $connector) {
                if ($condition->connector == $connector->name) {
                    if ($connector->checkCondition($condition->connector_value) == TRUE) {
                        $type = ($condition->type == 'storeview') ? 'store' : 'group';
                        return array(
                            'type' => $type,
                            'name' => $condition->name,
                        );
                    }
                }
            }
        }

        return null;
    }

    /*
     * Method to check whether the given condition is true
     *
     * @param mixed $condition
     * @return bool
     */
    public function checkCondition($condition = null)
    {
        return false;
    }

    /*
     * Overload methods to add an argument to it
     */
    public function getConnectors($type = null) { return parent::_getConnectors('store'); }
    public function getConnector($name) { return parent::_getConnector('store', $name); }
    public function getConnectorObject($name) { return parent::_getConnectorObject('store', $name); }
    public function getPath($file) { return parent::_getPath('store', $file); }
    public function getParams($type = null) { return parent::_getParams('store'); }
}
