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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class
 */
class MagebridgeTableStore extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__magebridge_stores', 'id', $db);
	}

	/**
	 * Bind method
	 *
	 * @param array  $array
	 * @param string $ignore
	 *
	 * @return mixed
	 * @see JTable:bind
	 */
	public function bind($array, $ignore = '')
	{
		// Convert the actions array to a flat string
		if (key_exists('actions', $array) && is_array($array['actions']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['actions']);
			$array['actions'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
