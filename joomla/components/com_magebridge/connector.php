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
 * MageBridge Connector class
 *
 * @package MageBridge
 */
class MageBridgeConnector
{
	/**
	 * List of product-connectors
	 */
	protected $connectors = array();

	/**
	 * Name of connector
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * MageBridgeConnector constructor.
	 */
	public function __construct()
	{
		$this->app = JFactory::getApplication();
		$this->db  = JFactory::getDbo();
	}

	/**
	 * Method to check whether this connector is enabled or not
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Method to check whether this connector is visible or not
	 *
	 * @deprecated
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function isVisible()
	{
		return true;
	}

	/**
	 * Get a list of all connectors
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	protected function _getConnectors($type = null)
	{
		return array();
	}

	/**
	 * Get a specific connector
	 *
	 * @param string $type
	 * @param string $name
	 *
	 * @return object
	 */
	protected function _getConnector($type = null, $name = null)
	{
		return (object) null;
	}

	/**
	 * Method to get a specific connector-object
	 *
	 * @param string $type
	 * @param string $connector
	 *
	 * @return object|false
	 */
	protected function _getConnectorObject($type = null, $connector = null)
	{
		if (empty($connector) || empty($connector->filename))
		{
			return false;
		}

		$file = self::_getPath($type, $connector->filename);

		if ($file == false)
		{
			return false;
		}

		require_once $file;
		$class = 'MageBridgeConnector' . ucfirst($type) . ucfirst($connector->name);

		if (!class_exists($class))
		{
			return false;
		}

		$object = new $class();

		if (empty($object))
		{
			return false;
		}

		$vars = get_object_vars($connector);

		if (!empty($vars))
		{
			foreach ($vars as $name => $value)
			{
				$object->$name = $value;
			}
		}

		return $object;
	}

	/**
	 * Get the connector-parameters
	 *
	 * @param string $type
	 *
	 * @return \Joomla\Registry\Registry
	 */
	protected function _getParams($type)
	{
		static $params = null;

		if (!empty($params))
		{
			return $params;
		}

		$file = self::_getPath($type, $this->name . '.xml');

		if (isset($this->params) && !empty($this->params))
		{
			$params = YireoHelper::toRegistry($this->params, $file);

			return $params;
		}

		if ($file == true)
		{
			$params = YireoHelper::toRegistry(null, $file);

			return $params;
		}

		$params = YireoHelper::toRegistry();

		return $params;
	}

	/**
	 * Get the right path to a file
	 *
	 * @param string $type
	 * @param string $filename
	 *
	 * @return string
	 */
	protected function _getPath($type, $filename)
	{
		$path = JPATH_SITE . '/components/com_magebridge/connectors/' . $type . '/' . $filename;

		if (file_exists($path) && is_file($path))
		{
			return $path;
		}

		return false;
	}

	/**
	 * Method to check whether a specific component is there
	 *
	 * @param string $component
	 *
	 * @return bool
	 */
	protected function checkComponent($component)
	{
		jimport('joomla.application.component.helper');

		if (is_dir(JPATH_ADMINISTRATOR . '/components/' . $component) && JComponentHelper::isEnabled($component) == true)
		{
			return true;
		}

		return false;
	}
}
