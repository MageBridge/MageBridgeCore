<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Table - allows models to have tables
 *
 * @package Yireo
 */
trait YireoModelTraitTable
{
	/**
	 * Boolean to skip table-detection
	 *
	 * @var int
	 */
	protected $skip_table = true;

	/**
	 * Database table object
	 *
	 * @var YireoTable
	 */
	protected $table;

	/**
	 * Database table object
	 *
	 * @var YireoTable
	 * @deprecated Use $this->table instead
	 */
	protected $_tbl;

	/**
	 * Database table-name
	 *
	 * @var string
	 * @deprecated Use $this->table->getTableName() instead
	 */
	protected $_tbl_name = '';

	/**
	 * Database table-alias
	 *
	 * @var string
	 */
	protected $table_alias = '';

	/**
	 * Database table-alias
	 *
	 * @var string
	 * @deprecated Use $this->table_alias instead
	 */
	protected $_tbl_alias = '';

	/**
	 * Database primary key
	 *
	 * @var string
	 * @deprecated Use $this->table->getKeyName() instead
	 */
	protected $_tbl_key = '';

	/**
	 * Flag to automatically set the table class prefix
	 *
	 * @var boolean
	 * @deprecated Use $this->getMeta('table_prefix_auto') instead
	 */
	protected $_tbl_prefix_auto = false;

	/**
	 * @return int
	 */
	public function getSkipTable()
	{
		return $this->skip_table;
	}

	/**
	 * @param int $skip_table
	 */
	public function setSkipTable($skip_table)
	{
		$this->skip_table = $skip_table;
	}
	
	/**
	 * @return string
	 */
	public function getTableAlias()
	{
		return $this->table_alias;
	}

	/**
	 * @param string $table_alias
	 */
	public function setTableAlias($table_alias)
	{
		$this->table_alias = $table_alias;
	}

	/**
	 * Method to get the current primary key
	 *
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->table->getKeyName();
	}
}