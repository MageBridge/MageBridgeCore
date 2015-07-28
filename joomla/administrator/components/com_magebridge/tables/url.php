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
class TableUrl extends YireoTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase $db
	 * @return null
	 */
	public function __construct(& $db) 
	{
		parent::__construct('#__magebridge_urls', 'id', $db);
	}

	/**
	 * Override of check-method
	 *
	 * @param null
	 * @return bool
	 */
	public function check()
	{
		if (empty($this->source) || empty($this->destination)) {
			$this->setError(JText::_('Source and destination must be filled in.'));
			return false;
		}
		return true;
	}
}

