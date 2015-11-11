<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
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
	/**
	 * Singleton variable
	 */
	private static $_instance = null;

	/**
	 * Associated array of options 
	 */
	private $options = array();

	/**
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

	/**
	 * Method to return options
	 *
	 * @param null
	 * @return mixed
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Method to get the current store definition
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

		// Import the plugins
		JPluginHelper::importPlugin('magebridgestore');
		$plugins = JPluginHelper::getPlugin('magebridgestore');

		// Try to match a condition with one of the connectors
		foreach ($conditions as $condition) {

			// Extract the parameters and make sure there's something to do
			$actions = YireoHelper::toRegistry($condition->actions)->toArray();

			// Detect the deprecated connector-architecture
			if(!empty($condition->connector) && !empty($condition->connector_value)) {
				JFactory::getApplication()->triggerEvent('onMageBridgeStoreConvertField', array($condition, &$actions));
			}

			// With empty actions, there is nothing to do
			if(empty($actions)) {
				continue;
			}

			// Loop through the plugins and validate the stored actions
			foreach($plugins as $plugin) {
				$className = 'plg' . $plugin->type . $plugin->name;
				if (class_exists($className)) {
					$plugin = new $className($this, (array) $plugin);

					// Validate the stored actions
					if($rt = $plugin->onMageBridgeValidate($actions, $condition)) {

						// Construct the condition parameters
						$name = $condition->name;
						$type = ($condition->type == 'storeview') ? 'store' : 'group';

						// Check for the return value
						if(is_array($rt)) return $rt;

						// Return the store-configuration of this condition
						return array(
							'type' => $type,
							'name' => $name,
						);
					}
				}
			}
		}

		return null;
	}

	/**
	 * Attach an observer object
	 *
	 * @param   object  $observer  An observer object to attach
	 * @return  void
	 */
	public function attach($observer)
	{
		// Dummy method to allow for calling JPluginHelper::getPlugin()
	}

	/**
	 * Method to check whether the given condition is true
	 *
	 * @param mixed $condition
	 * @return bool
	 */
	public function checkCondition($condition = null)
	{
		return false;
	}

	/**
	 * Overload methods to add an argument to it
	 */
	public function getConnectors($type = null) { return parent::_getConnectors('store'); }
	public function getConnector($name) { return parent::_getConnector('store', $name); }
	public function getConnectorObject($name) { return parent::_getConnectorObject('store', $name); }
	public function getPath($file) { return parent::_getPath('store', $file); }
	public function getParams($type = null) { return parent::_getParams('store'); }
}
