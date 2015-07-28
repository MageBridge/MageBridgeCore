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
class TableUsergroup extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 * @return null
	 */
	public function __construct(& $db) 
	{
		// List of required fields that can not be left empty 
		$this->required = array('joomla_group', 'magento_group');

		// Call the constructor
		parent::__construct('#__magebridge_usergroups', 'id', $db);
	}
}
