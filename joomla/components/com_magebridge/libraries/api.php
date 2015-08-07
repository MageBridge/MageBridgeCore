<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Main bridge class
 */
class MageBridgeApi
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		MageBridgeModelDebug::getDebugOrigin(MageBridgeModelDebug::MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC);
		$this->debug = MageBridgeModelDebug::getInstance();
		$this->app = JFactory::getApplication();
	}

	/**
	 * Test method
	 *
	 * @return string
	 */
	public function test()
	{
		$this->debug->notice( 'JSON-RPC test');

		return 'OK received from Joomla!';
	}

	/**
	 * Login method
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function login($params = array())
	{
		$credentials = array(
			'username' => $params[0],
			'password' => $params[1],);

		$rt = $this->app->login($credentials);

		if ($rt == true)
		{
			return array('email' => $params[0]);
		}

		return false;
	}

	/**
	 * Event method
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function event($params = array())
	{
		// Parse the parameters
		$event = (isset($params[0]) && is_string($params[0])) ? $params[0] : null;
		$arguments = (isset($params[1]) && is_array($params[1])) ? $params[1] : array();

		// Check if this call is valid
		if (empty($event))
		{
			return false;
		}

		// Start debugging
		$this->debug->trace('JSON-RPC: firing mageEvent ', $event);
		//$this->debug->trace( 'JSON-RPC: plugin arguments', $arguments );

		// Initialize the plugin-group "magento"
		JPluginHelper::importPlugin('magento');
		$application = JFactory::getApplication();

		// Trigger the event and return the result
		$result = $application->triggerEvent($event, array($arguments));

		if (!empty($result[0]))
		{
			return $result[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Logs a MageBridge message on the Joomla! side
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function log($params = array())
	{
		// Parse the parameters
		$type = (isset($params['type'])) ? $params['type'] : MAGEBRIDGE_DEBUG_NOTICE;
		$message = (isset($params['message'])) ? $params['message'] : null;
		$section = (isset($params['section'])) ? $params['section'] : null;
		$time = (isset($params['time'])) ? $params['time'] : null;
		$origin = MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO;

		// Log this message
		return (bool) $this->debug->add($type, $message, $section, $origin, $time);
	}

	/**
	 * Output modules on a certain position
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public function position($params = array())
	{
		if (empty($params) || empty($params[0]))
		{
			$this->debug->error('JSON-RPC: position-method called without parameters');

			return null;
		}

		$position = $params[0];
		$style = (isset($params[1])) ? $params[1] : null;

		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules($position);

		$outputHtml = null;
		$attributes = array('style' => $style);

		if (!empty($modules))
		{
			foreach ($modules as $module)
			{
				$moduleHtml = JModuleHelper::renderModule($module, $attributes);
				$moduleHtml = preg_replace('/href=\"\/([^\"]{0,})\"/', 'href="' . JURI::root() . '\1"', $moduleHtml);
				$outputHtml .= $moduleHtml;
			}
		}

		return $outputHtml;
	}

	/**
	 * Method to get a list of all users
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function getUsers($params = array())
	{
		// System variables
		$db = JFactory::getDBO();

		// Construct the query
		$query = 'SELECT * FROM #__users';

		if (isset($params['search']))
		{
			$query .= ' WHERE username LIKE ' . $db->Quote($params['search']);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $index => $row)
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_magebridge/libraries/helper.php';

			$params = YireoHelper::toRegistry($row->params);
			$row->params = $params->toArray();
			$rows[$index] = $row;
		}

		return $rows;
	}
}
