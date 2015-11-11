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
 * Main bridge class
 */
class MageBridgeModelBridgeApi extends MageBridgeModelBridgeSegment
{
	/**
	 * Singleton 
	 *
	 * @param string $name
	 * @return object
	 */
	public static function getInstance($name = null)
	{
		return parent::getInstance('MageBridgeModelBridgeApi');
	}

	/**
	 * Load the data from the bridge
	 */
	public function getResponseData($resource = null, $arguments = null, $id = null)
	{
		return MageBridgeModelRegister::getInstance()->getData('api', $resource, $arguments, $id);
	}
}
