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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import Joomla! libraries
jimport('joomla.utilities.date');

/**
 * MageBridge Usergroup model
 */
class MagebridgeModelUsergroup extends YireoModel
{
	/**
	 * Constructor method
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function __construct()
	{
		$this->_orderby_title = 'description';
		parent::__construct('usergroup');
	}
}
