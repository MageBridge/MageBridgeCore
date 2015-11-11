<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright Yireo.com 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * MageBridge Logs model
 */
class MagebridgeModelLogs extends YireoModel
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function __construct()
	{
		$this->_checkout = false;
		$this->_search = array('message', 'session', 'http_agent');

		parent::__construct('log');

		$origin = $this->getFilter('origin');
		if (!empty($origin)) $this->addWhere($this->_tbl_alias.'.`origin` = '.$this->_db->Quote($origin));

		$remote_addr = $this->getFilter('remote_addr');
		if (!empty($remote_addr)) $this->addWhere($this->_tbl_alias.'.`remote_addr` = '.$this->_db->Quote($remote_addr));

		$type = $this->getFilter('type');
		if (!empty($type)) $this->addWhere($this->_tbl_alias.'.`type` = '.$this->_db->Quote($type));
	}
}
