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
* MageBridge Table class
*
* @package MageBridge
*/
class TableConfig extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 * @return null
	 */
	public function __construct(& $db) {
		parent::__construct('#__magebridge_config', 'id', $db);
	}
}

