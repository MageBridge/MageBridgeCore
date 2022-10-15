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
 * Yireo Model Data Query
 *
 * @package Yireo
 */
class YireoModelDataQuerytext
{
	/**
	 * Trait to implement ID behaviour
	 */
	use YireoModelTraitIdentifiable;

	/**
	 * Trait to implement ID behaviour
	 */
	use YireoModelTraitConfigurable;

	/**
	 * @var YireoTable
	 */
	protected $table;

	/**
	 * @var string
	 */
	protected $tableAlias;

	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * @var YireoModel
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $queryText;

	/**
	 * @var array
	 */
	protected $where = array();

	/**
	 * @var array
	 */
	protected $extra = array();

	/**
	 * @var array
	 */
	protected $orderby = array();

	/**
	 * @var array
	 */
	protected $groupby = array();

	/**
	 * @var array
	 */
	protected $skipFrontendFields = array(
		'locked',
		'published',
		'published_up',
		'published_down',
		'checked_out',
		'checked_out_time'
	);

	/**
	 * YireoModelDataQuery constructor.
	 */
	public function __construct($table, $tableAlias)
	{
		$this->table      = $table;
		$this->tableAlias = $tableAlias;
		$this->app        = JFactory::getApplication();
		$this->db         = JFactory::getDbo();
	}

	/**
	 * Method to build the query
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	public function build($query = '')
	{
		// Get the WHERE clauses for the query
		$where = $this->buildWhere();

		// Get the ORDER BY clauses for the query
		$orderby = $this->buildQueryOrderBy();

		// Get the GROUP BY clauses for the query
		$groupby = $this->buildQueryGroupBy();

		// Get the extra segments for the query
		$extra = $this->buildExtra();

		$limitString = $this->buildLimit();

		// Build the default query if not set
		if (empty($query))
		{
			// Build the fields-string to avoid a *
			$fieldsStrings = $this->getSelectFields();
			$fieldsString  = implode(', ', $fieldsStrings);
			$query         = $this->getSelectQuery($fieldsString);
		}

		$query = $this->replaceAccess($query);
		$query = $this->replaceEditor($query);

		// Return the query including WHERE and LIMIT
		$query = $query . $extra . $where . $orderby . $groupby . $limitString;
		$query = $this->replaceTags($query);

		return $query;
	}

	/**
	 *
	 */
	protected function getSelectFields()
	{
		$availableFields        = $this->table->getDatabaseFields();
		$selectFields = array();

		foreach ($availableFields as $availableField)
		{
			if ($this->app->isSite() && in_array($availableField, $this->skipFrontendFields))
			{
				continue;
			}

			$selectFields[] = '`{tableAlias}`.`' . $availableField . '`';
		}

		// Append extra fields
		$extraFields = $this->getConfig('extra_fields');

		if (!empty($extraFields))
		{
			foreach ($extraFields as $extraField)
			{
				$selectFields[] = $extraField;
			}
		}

		return $selectFields;
	}

	/**
	 *
	 */
	protected function getSelectQuery($fieldsString)
	{
		if ($this->getConfig('checkout') == true && $this->app->isAdmin())
		{
			$query = "SELECT " . $fieldsString . ", `editor`.`name` AS `editor` FROM `{table}` AS `{tableAlias}`\n";
			$query .= " LEFT JOIN `#__users` AS `editor` ON `{tableAlias}`.`checked_out` = `editor`.`id`\n";
		}
		else
		{
			$query = "SELECT " . $fieldsString . " FROM `{table}` AS `{tableAlias}`\n";
		}

		return $query;
	}

	/**
	 * @param $query
	 *
	 * @return mixed|string
	 */
	protected function replaceAccess($query)
	{
		if (strstr($query, '{access}'))
		{
			$query = str_replace('{access}', '`viewlevel`.`title` AS `accesslevel`', $query);
			$query .= " LEFT JOIN `#__viewlevels` AS `viewlevel` ON `viewlevel`.`id`=`" . $this->tableAlias . "`.`access`\n";
		}

		return $query;
	}

	/**
	 * @param $query
	 *
	 * @return string
	 */
	protected function replaceEditor($query)
	{
		// Add-in editor-details
		if (strstr($query, '{editor}'))
		{
			$query = str_replace('{editor}', '`user`.`name` AS `editor`', $query);
			$query .= " LEFT JOIN `#__users` AS `user` ON `user`.`id`=`" . $this->tableAlias . "`.`checked_out`\n";
		}

		return $query;
	}

	/**
	 * @param $query
	 *
	 * @return string
	 */
	protected function replaceTags($query)
	{
		$query = str_replace('{table}', $this->table->getTableName(), $query);
		$query = str_replace('{tableAlias}', $this->tableAlias, $query);
		$query = str_replace('{primary}', $this->table->getKeyName(), $query);

		return $query;
	}

	/**
	 * Method to build the query WHERE segment
	 *
	 * @return string
	 */
	protected function buildWhere()
	{
		// Automatically add the WHERE-statement for a single ID-based query
		if (!empty($this->id))
		{
			$this->addWhere('`{tableAlias}`.`{primary}`=' . (int) $this->id);
		}

		// Automatically add a WHERE-statement if the state-filter is used
		$state = $this->getConfig('filter_state');

		if ($state == 'U' || $state == 'P')
		{
			$state      = ($state == 'U') ? 0 : 1;
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->db->quoteName($this->tableAlias) . '.`' . $stateField . '` = ' . $state);
			}
		}

		// Automatically add a WHERE-statement if only published items should appear on the frontend
		if ($this->app->isSite())
		{
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->tableAlias . '.' . $stateField . ' = 1');
			}
		}

		// Automatically add a WHERE-statement if the search-filter is used
		$search       = $this->getConfig('filter_search');
		$searchFields = $this->getConfig('search_fields');

		if (!empty($searchFields) && !empty($search))
		{
			$where_search = array();

			foreach ($searchFields as $field)
			{
				$field = $this->db->quoteName($field);

				if (strstr($field, '.') == false)
				{
					$field = $this->db->quoteName($this->tableAlias) . "." . $field;
				}

				$where_search[] = "$field LIKE '%$search%'";
			}
		}

		if (!empty($where_search))
		{
			$this->where[] = '(' . implode(' OR ', $where_search) . ')';
		}

		if (count($this->where))
		{
			return ' WHERE ' . implode(' AND ', $this->where) . "\n";
		}

		return '';
	}

	/**
	 * Method to build the query ORDER BY segment
	 *
	 * @return string
	 */
	protected function buildQueryOrderBy()
	{
		$orderby = $this->getConfig('orderby');

		if (empty($orderby) || !is_array($orderby))
		{
			return null;
		}

		$this->orderby = array_unique($orderby);

		if (count($this->orderby))
		{
			foreach ($this->orderby as $index => $orderby)
			{
				$orderby = trim($orderby);

				if (empty($orderby))
				{
					unset($this->orderby[$index]);
				}
			}

			if (!empty($this->orderby))
			{
				return ' ORDER BY ' . implode(', ', $this->orderby) . "\n";
			}
		}

		return null;
	}

	/**
	 * Method to build the query GROUP BY segment
	 *
	 * @return string
	 */
	protected function buildQueryGroupBy()
	{
		$this->groupby = array_unique($this->groupby);

		if (count($this->groupby))
		{
			foreach ($this->groupby as $index => $groupby)
			{
				$groupby = trim($groupby);

				if (empty($groupby))
				{
					unset($this->groupby[$index]);
				}
			}

			if (!empty($this->groupby))
			{
				return ' GROUP BY ' . implode(', ', $this->groupby) . "\n";
			}
		}

		return null;
	}

	/**
	 * @return string
	 */
	protected function buildLimit()
	{
		if (!empty($this->id))
		{
			return ' LIMIT 1';
		}

		// Get the LIMIT segments for the query
		$limitStart = $this->getConfig('limit.start');
		$limitCount = $this->getConfig('limit.count');

		if (empty($limitCount) || empty($limitStart))
		{
			return '';
		}

		$limitString = ' LIMIT ' . $limitStart . ',' . $limitCount;

		return $limitString;
	}

	/**
	 * Method to build an extra query segment
	 *
	 * @return string
	 */
	protected function buildExtra()
	{
		if (count($this->extra) > 0)
		{
			return ' ' . implode(' ', $this->extra) . "\n";
		}

		return '';
	}

	/**
	 * Method to add a new WHERE argument
	 *
	 * @param mixed  $where WHERE statement in the form of an array ($name, $value) or string
	 * @param string $type  Type of WHERE statement. Either "is" or "like".
	 *
	 * @return $this
	 */
	public function addWhere($where, $type = 'is')
	{
		if ($this->getConfig('allow_filter', true) == false)
		{
			return $this;
		}

		if (is_array($where) && count($where) == 2)
		{
			if ($type == 'like')
			{
				$where = $this->db->quoteName($where[0]) . ' LIKE ' . $this->db->quote($where[1]);
			}
			else
			{
				$where = $this->db->quoteName($where[0]) . ' = ' . $this->db->quote($where[1]);
			}
		}

		if (is_string($where) && !in_array($where, $this->where))
		{
			$this->where[] = $where;
		}

		return $this;
	}


	/**
	 * Method to add a new ORDER BY argument
	 *
	 * @param string $orderby
	 *
	 * @return $this
	 */
	public function addOrderby($orderby = null)
	{
		$orderby = trim($orderby);

		if (empty($orderby))
		{
			return $this;
		}

		if ($orderby == '{tableAlias}.')
		{
			return $this;
		}

		if (is_string($orderby) && !isset($this->orderby[$orderby]))
		{
			if (strstr($orderby, '.') == false && preg_match('/^RAND/', $orderby) == false)
			{
				$orderby = '{tableAlias}.' . $orderby;
			}

			if (strstr($orderby, 'accesslevel'))
			{
				$orderby = str_replace('{tableAlias}.', '', $orderby);
			}

			$this->orderby[] = $orderby;
		}

		return $this;
	}

	/**
	 * Method to add a new GROUP BY argument
	 *
	 * @param string $groupby
	 *
	 * @return $this
	 */
	public function addGroupby($groupby = null)
	{
		$groupby = trim($groupby);

		if (empty($groupby))
		{
			return $this;
		}

		if ($groupby == '{tableAlias}.')
		{
			return $this;
		}

		if (is_string($groupby) && !isset($this->groupby[$groupby]))
		{
			if (strstr($groupby, '.') == false)
			{
				$groupby = '{tableAlias}.' . $groupby;
			}

			$this->groupby[] = $groupby;
		}

		return $this;
	}

	/**
	 * Method to add an extra query argument
	 *
	 * @param string $extra
	 *
	 * @return $this
	 */
	public function addExtra($extra = null)
	{
		if (is_string($extra))
		{
			$this->extra[] = $extra;
		}

		return $this;
	}
	
	/**
	 * @param $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
		
		return $this;
	}
}