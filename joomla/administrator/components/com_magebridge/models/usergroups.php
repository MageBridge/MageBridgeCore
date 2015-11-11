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

/**
 * MageBridge Usergroups model
 */
class MagebridgeModelUsergroups extends YireoModel
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
		$this->_checkout = false;
		$this->_search = array('description');

		parent::__construct('usergroup');
	}
}
