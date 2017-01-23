<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright Yireo.com 2015
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * MageBridge Logs model
 */
class MagebridgeModelLogs extends YireoModelItems
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->setConfig('checkout', false);
		$this->setConfig('search_fields', array('message', 'session', 'http_agent'));

		parent::__construct('log');

	}

	/**
	 * @param JDatabaseQuery $query
	 *
	 * @return JDatabaseQuery
	 */
	public function onBuildQuery($query)
	{
		$origin = $this->getFilter('origin');

		if (!empty($origin))
		{
			$query->where($this->getConfig('table_alias') . '.' . $this->_db->quoteName('origin') . ' = ' . $this->_db->quote($origin));
		}

		$remote_addr = $this->getFilter('remote_addr');

		if (!empty($remote_addr))
		{
			$query->where($this->getConfig('table_alias') . '.' . $this->_db->quoteName('remote_addr') . ' = ' . $this->_db->quote($remote_addr));
		}

		$type = $this->getFilter('type');

		if (!empty($type))
		{
			$query->where($this->getConfig('table_alias') . '.' . $this->_db->quoteName('type') . ' = ' . $this->_db->quote($type));
		}

		return $query;
	}
}
