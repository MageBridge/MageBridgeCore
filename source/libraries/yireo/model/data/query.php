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
class YireoModelDataQuery
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
	 * @var JDatabaseQuery
	 */
	protected $query;

	/**
	 * @var YireoModel
	 */
	protected $model;

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
	 * YireoModelDataQuery constructor
	 *
	 * @param $table      YireoTable
	 * @param $tableAlias string
	 */
	public function __construct($table, $tableAlias)
	{
		$this->table      = $table;
		$this->tableAlias = $tableAlias;
		$this->app        = JFactory::getApplication();
		$this->db         = JFactory::getDbo();
		$this->query      = $this->db->getQuery(true);
	}

	/**
	 * Method to build the query
	 *
	 * @param null|JDatabaseQuery $query
	 *
	 * @return JDatabaseQuery
	 */
	public function build($query = null)
	{
		$this->buildBasicQuery();

		// Build the default query if not set
		if (!empty($query))
		{
			$this->query = $query;
		}

		// Get the WHERE clauses for the query
		$this->buildWhere();

		// Get the ORDER BY clauses for the query
		$this->buildQueryOrderBy();

		// Get the GROUP BY clauses for the query
		$this->buildQueryGroupBy();

		// Add limits
		$this->buildLimit();

		$this->addAccess();

		if (!empty($this->model) && method_exists($this->model, 'onBuildQuery'))
		{
			$this->query = $this->model->onBuildQuery($this->query);
		}

		return $this->query;
	}

	/**
	 *
	 */
	protected function getSelectFields()
	{
		$availableFields = $this->table->getDatabaseFields();
		$selectFields    = array();

		foreach ($availableFields as $availableField)
		{
			if ($this->app->isSite() && in_array($availableField, $this->skipFrontendFields))
			{
				continue;
			}

			$selectFields[] = $this->tableAlias . '.' . $availableField;
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
	 * @return $this
	 */
	protected function buildBasicQuery()
	{
		$db = $this->db;

		$this->query->from($db->quoteName($this->table->getTableName(), $this->tableAlias));
		$this->query->select($db->quoteName($this->getSelectFields()));

		if ($this->getConfig('checkout') == true && $this->table->hasField('checked_out') && $this->app->isAdmin())
		{
			$this->query->select($db->quoteName('editor.name', 'editor'));

			$editorTable      = $db->quoteName('#__users', 'editor');
			$editorTableField = $db->quoteName('editor.id');
			$checkedOutField  = $db->quoteName($this->tableAlias . '.checked_out');

			$this->query->leftJoin($editorTable . ' ON ' . $editorTableField . '=' . $checkedOutField);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function addAccess()
	{
		if ($this->table->hasField('access'))
		{
			$db = $this->db;
			$this->query->select($db->quoteName('viewlevel.title', 'accesslevel'));

			$viewlevelTable      = $db->quoteName('#__viewlevels', 'viewlevel');
			$viewlevelTableField = $db->quoteName('viewlevel.id');
			$accessField         = $db->quoteName($this->tableAlias . '.access');

			$this->query->leftJoin($viewlevelTable . ' ON ' . $viewlevelTableField . '=' . $accessField);
		}

		return $this;
	}

	/**
	 * Method to build the query WHERE segment
	 *
	 * @return $this
	 */
	protected function buildWhere()
	{
		// Automatically add the WHERE-statement for a single ID-based query
		if (!empty($this->id))
		{
			$this->addWhere($this->db->quoteName($this->tableAlias . '.' . $this->table->getKeyName()) . '=' . (int) $this->id);
		}

		// Automatically add a WHERE-statement if the state-filter is used
		$state = $this->getConfig('filter_state');

		if ($state == 'U' || $state == 'P')
		{
			$state      = ($state == 'U') ? 0 : 1;
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->db->quoteName($this->tableAlias) . '.`' . $stateField . '` = ' . $this->db->quote($state));
			}
		}

		// Automatically add a WHERE-statement if only published items should appear on the frontend
		if ($this->app->isSite())
		{
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->db->quoteName($this->tableAlias . '.' . $stateField) . ' = 1');
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
			foreach ($this->where as $where)
			{
				$this->query->where($where);
			}
		}

		return $this;
	}

	/**
	 * Method to build the query ORDER BY segment
	 *
	 * @return $this
	 */
	protected function buildQueryOrderBy()
	{
		$orderBy = $this->getConfig('orderby');

		if (empty($orderBy) || !is_array($orderBy))
		{
			return $this;
		}

		$this->orderby = array_unique($orderBy);

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
				$this->query->order(implode(', ', $this->orderby));
			}
		}

		return $this;
	}

	/**
	 * Method to build the query GROUP BY segment
	 *
	 * @return string
	 */
	protected function buildQueryGroupBy()
	{
		$groupby = $this->getConfig('groupby');

		if (empty($groupby) || !is_array($groupby))
		{
			return $this;
		}

		$this->groupby = array_unique($groupby);

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
				$this->query->group(implode(', ', $this->groupby));
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function buildLimit()
	{
		if (!empty($this->id))
		{
			return $this->query->setLimit(1);
		}

		// Get the LIMIT segments for the query
		$limitStart = $this->getConfig('limit.start');
		$limitCount = $this->getConfig('limit.count');

		if (empty($limitCount) && empty($limitStart))
		{
			return $this;
		}

		$this->query->setLimit($limitCount, $limitStart);

		return $this;
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

		if (is_string($orderby) && !isset($this->orderby[$orderby]))
		{
			if (strstr($orderby, '.') == false && preg_match('/^RAND/', $orderby) == false)
			{
				$orderby = $this->tableAlias . $orderby;
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

		if (is_string($groupby) && !isset($this->groupby[$groupby]))
		{
			if (strstr($groupby, '.') == false)
			{
				$groupby = $this->tableAlias . '.' . $groupby;
			}

			$this->groupby[] = $groupby;
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