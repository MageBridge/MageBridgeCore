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
class TableProduct extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 * @return null
	 */
	public function __construct(& $db) 
	{
		$this->_required = array('sku');
		parent::__construct('#__magebridge_products', 'id', $db);
	}

	/**
	 * Bind method
	 *
	 * @access public
	 * @subpackage Yireo
	 * @param array $array
	 * @param string $ignore
	 * @return null
	 * @see JTable:bind
	 */
	public function bind($array, $ignore = '')
	{
		// Convert the actions array to a flat string
		if (key_exists( 'actions', $array ) && is_array( $array['actions'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['actions']);
			$array['actions'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
