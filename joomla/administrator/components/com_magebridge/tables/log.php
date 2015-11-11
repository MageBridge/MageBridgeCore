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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* MageBridge Table class
*
* @package MageBridge
*/
class TableLog extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 * @return null
	 */
	public function __construct(& $db) 
	{
		parent::__construct('#__magebridge_log', 'id', $db);
	}

	/**
	 * Helper-method to get the default ORDER BY value (depending on the present fields)
	 *
	 * @access public
	 * @param null
	 * @return array
	 */
	public function getDefaultOrderBy()
	{
		return false;
	}
}

