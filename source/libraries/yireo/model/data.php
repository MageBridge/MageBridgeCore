<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(dirname(__FILE__)).'/loader.php';

require_once 'trait/debuggable.php';

/**
 * Yireo Data Model
 *
 * @package Yireo
 */
class YireoDataModel extends YireoCommonModel
{
	/**
	 * Trait to implement debugging behaviour
	 */
	use YireoModelTraitDebuggable;

	/**
	 * @var mixed
	 */
	protected $data;
	
	/*
	 * Boolean to skip table-detection
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('skip_table') instead
	 */
	protected $skip_table = false;

	/**
	 * Constructor
	 *
	 * @param array $config
	 *
	 * @return mixed
	 */
	public function __construct($config = array())
	{
		// Call the parent constructor
		$rt = parent::__construct($config);

		// Set the database variables
		if ($this->getConfig('table_prefix_auto') == true)
		{
			$this->_tbl_prefix = $this->getConfig('component') . 'Table';
		}

		// @todo: Set this in a metadata array
		$tableAlias       = $this->getConfig('table_alias');
		$this->_tbl_alias = $tableAlias;
		$this->table      = $this->getTable($tableAlias);

		if ($this->table)
		{
			$this->_tbl_name = $this->table->getTableName();
			$this->_tbl_key  = $this->table->getKeyName();
		}

		$this->_entity    = $tableAlias;
		$this->_form_name = $tableAlias;

		return $rt;
	}

	/**
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function setData($name, $value = null)
	{
		if (is_array($name) && empty($value))
		{
			$this->data = $name;

			return;
		}

		$this->data[$name] = $value;
	}

	/**
	 * Method to get data
	 *
	 * @param bool $forceNew
	 *
	 * @return array
	 */
	public function getData($forceNew = false)
	{
		return $this->data;
	}
	
	/**
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public function getDataByName($name = null)
	{
		if (empty($this->data[$name]))
		{
			return false;
		}

		return $this->data[$name];
	}


	/**
	 * Method to fetch database-results
	 *
	 * @param string $query
	 * @param string $type : object|objectList|result
	 *
	 * @return mixed
	 */
	public function getDbResult($query, $type = 'object')
	{
		if ($this->_cache == true)
		{
			$cache = JFactory::getCache('lib_yireo_model');
			$rs = $cache->call(array($this, '_getDbResult'), $query, $type);
		}
		else
		{
			$rs = $this->_getDbResult($query, $type);
		}

		return $rs;
	}

	/**
	 * Method to fetch database-results
	 *
	 * @param string $query
	 * @param string $type : object|objectList|result
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function _getDbResult($query, $type = 'object')
	{
		// Set the query in the database-object
		$this->_db->setQuery($query);

		// Print the query if debugging is enabled
		if (method_exists($this, 'allowDebug') && $this->allowDebug())
		{
			$this->app->enqueueMessage($this->getDbDebug(), 'debug');
		}

		// Fetch the database-result
		if ($type == 'objectList')
		{
			$rs = $this->_db->loadObjectList();
		}
		elseif ($type == 'result')
		{
			$rs = $this->_db->loadResult();
		}
		else
		{
			$rs = $this->_db->loadObject();
		}

		// Return the result
		return $rs;
	}

	/**
	 * Throw a database exception
	 */
	protected function throwDbException()
	{
		$db = JFactory::getDbo();
		
		throw new JDatabaseExceptionUnsupported($db->getErrorMsg());
	}
}
