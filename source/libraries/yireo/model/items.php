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

// Import the loader
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Model
 * Parent class for models that use the full-blown MVC pattern
 *
 * @package Yireo
 */
class YireoModelItems extends YireoDataModel
{
	/**
	 * Trait to implement checkout behaviour
	 */
	use YireoModelTraitCheckable;

	/**
	 * Trait to implement pagination behaviour
	 */
	use YireoModelTraitPaginable;

	/**
	 * Trait to implement filter behaviour
	 */
	use YireoModelTraitFilterable;

	/**
	 * Trait to implement filter behaviour
	 */
	use YireoModelTraitLimitable;

	/**
	 * @var array
	 */
	protected $queryConfig = array();

	/**
	 * Boolean to allow for caching
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('cache') instead
	 */
	protected $_cache = false;

	/**
	 * Ordering field
	 *
	 * @var string
	 */
	protected $_ordering = null;

	/**
	 * Search columns
	 *
	 * @var array
	 */
	protected $search = array();

	/**
	 * Order-by default-value
	 *
	 * @var string
	 * @deprecated: Use $this->getConfig('orderby_default') instead
	 */
	protected $_orderby_default = null;

	/**
	 * Order-by default-title
	 *
	 * @var string
	 * @deprecated: Use $this->getConfig('orderby_title') instead
	 */
	protected $_orderby_title = null;

	/**
	 * List of fields to autoconvert into column-seperated fields
	 *
	 * @var array
	 */
	protected $_columnFields = array();

	/**
	 * Constructor
	 *
	 * @param mixed $config
	 *
	 * @return mixed
	 */
	public function __construct($config = array())
	{
		// Handle a deprecated constructor call
		if (is_string($config))
		{
			$tableAlias        = $config;
			$this->table_alias = $tableAlias;
			$config            = array('table_alias' => $tableAlias);
		}

		// Call the parent constructor
		$rt = parent::__construct($config);

		$this->setConfig('skip_table', false);
		$this->setConfig('table_prefix_auto', true);
		$this->setConfig('limit_query', true);
		$this->setTablePrefix();
		$this->table = $this->getTable($this->getConfig('table_alias'));

		$this->initOrderBy();
		$this->initPlural();

		// Set the parameters for the frontend
		$this->initParams();

		return $rt;
	}

	/**
	 * Initialize ORDER BY details
	 */
	protected function initOrderBy()
	{
		$orderByDefault = $this->getConfig('orderby_default');

		if (empty($orderByDefault))
		{
			$this->setConfig('orderby_default', $this->table->getDefaultOrderBy());
		}

		$orderByTitle = $this->getConfig('orderby_title');

		if (empty($orderByTitle))
		{
			if ($this->table->hasField('title'))
			{
				$this->setConfig('orderby_title', 'title');
			}

			if ($this->table->hasField('label'))
			{
				$this->setConfig('orderby_title', 'label');
			}

			if ($this->table->hasField('name'))
			{
				$this->setConfig('orderby_title', 'name');
			}
		}
	}

	/**
	 * Inititalize system variables
	 */
	protected function initPlural()
	{
		// Initialize limiting
		$this->initLimit();
		$this->initLimitstart();

		// Initialize ordering
		$orderBys       = array();
		$orderByDefault = $this->getConfig('orderby_default');

		if (!empty($orderByDefault))
		{
			$filter_order     = $this->getFilter('order', $this->getConfig('table_alias') . '.' . $orderByDefault, 'string');
			$filter_order_Dir = $this->getFilter('order_Dir');

			if (!empty($filter_order_Dir))
			{
				$filter_order_Dir = ' ' . strtoupper($filter_order_Dir);
			}

			if (!empty($filter_order))
			{
				$orderBys[] = $filter_order . $filter_order_Dir;
			}

			$orderBys[] = $this->getConfig('table_alias') . '.' . $orderByDefault;
		}

		$this->queryConfig['orderby'] = $orderBys;
	}

	/**
	 * @return \Joomla\Registry\Registry
	 */
	protected function initParams()
	{
		if (!empty($this->params))
		{
			return $this->params;
		}

		if ($this->app->isSite() == false)
		{
			$this->params = JComponentHelper::getParams($this->getConfig('option'));

			return $this->params;
		}

		$this->params = $this->app->getParams($this->getConfig('option'));

		return $this->params;
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
		$this->data = $this->fetchData($forceNew);

		if ($this->getConfig('limit_query') == false && $this->getState('limit') > 0)
		{
			$part = array_slice($this->data, (int) $this->getState('limitstart'), $this->getState('limit'));

			return $part;
		}

		return $this->data;
	}

	/**
	 * @return JDatabaseQuery
	 */
	public function buildQueryObject()
	{
		$this->queryConfig['filter_state']  = $this->getFilter('state');
		$this->queryConfig['filter_search'] = $this->getFilter('search');
		$this->queryConfig['search_fields'] = $this->getConfig('search_fields');
		$this->queryConfig['allow_filter']  = $this->getConfig('allow_filter', true);

		if ($this->getConfig('limit_query') == true)
		{
			$this->queryConfig['limit.start'] = $this->getState('limitstart');
			$this->queryConfig['limit.count'] = $this->getState('limit');
		}

		$this->initQuery();
		$query = $this->query->setConfig($this->queryConfig)
			->setModel($this)
			->build();

		return $query;
	}

	/**
	 * Method to get data
	 *
	 * @param bool $forceNew
	 *
	 * @return array
	 */
	public function fetchData($forceNew = false)
	{
		if (!empty($this->data) && $forceNew === false)
		{
			return $this->data;
		}

		// Load some empty data-set
		$this->getEmpty();

		// Try to load the temporary data from this session
		$this->loadTmpSession();

		$query = $this->buildQueryObject();
		$data  = $this->getDbResult($query, 'objectList');

		if (!empty($data))
		{
			// Prepare these data
			foreach ($data as $index => $item)
			{
				// Frontend permissions
				if ($this->app->isSite() && isset($item->access) && is_numeric($item->access))
				{
					$accessLevels = $this->user->getAuthorisedViewLevels();

					if ($item->access > 0 && !in_array($item->access, $accessLevels))
					{
						unset($data[$index]);
						continue;
					}
				}

				// Backend permissions
				if ($this->app->isAdmin() && (bool) $this->table->hasAssetId() == true)
				{
					// Determine the owner
					$owner = 0;

					if (!empty($item->created_by))
					{
						$owner = (int) $item->created_by;
					}
					elseif (!empty($item->modified_by))
					{
						$owner = (int) $item->modified_by;
					}
					elseif (!empty($item->owned_by))
					{
						$owner = (int) $item->owned_by;
					}

					if ($owner == 0)
					{
						$owner = $this->user->id;
					}

					// Get the ACL rules
					$canEdit    = $this->user->authorise('core.edit', $this->getConfig('option'));
					$canEditOwn = $this->user->authorise('core.edit.own', $this->getConfig('option'));

					// Determine authorisation
					$authorise = false;

					if ($canEdit)
					{
						$authorise = true;
					}
					elseif ($canEditOwn && $owner == $this->user->id)
					{
						$authorise = true;
					}

					// Authorise
					if ($authorise == false)
					{
						unset($data[$index]);
						continue;
					}
				}

				// Prepare the column-fields
				if (!empty($this->_columnFields))
				{
					foreach ($this->_columnFields as $columnField)
					{
						if (!empty($item->$columnField) && !is_array($item->$columnField))
						{
							$item->$columnField = explode('|', $item->$columnField);
						}
					}
				}

				// Prepare the parameters
				if (isset($item->params))
				{
					$item->params = YireoHelper::toParameter($item->params);
				}
				else
				{
					$item->params = YireoHelper::toParameter();
				}

				// Check for publish_up and publish_down
				if ($this->app->isSite())
				{
					$publish_up   = $item->params->get('publish_up');
					$publish_down = $item->params->get('publish_down');

					if (!empty($publish_up) && strtotime($publish_up) > time())
					{
						unset($data[$index]);
						continue;
					}
					else
					{
						if (!empty($publish_down) && strtotime($publish_down) < time())
						{
							unset($data[$index]);
							continue;
						}
					}
				}

				// Allow to modify the data
				if (method_exists($this, 'onDataLoad'))
				{
					$item = $this->onDataLoad($item);
				}

				// Add the metadata
				$item->metadata = $this->getConfig();

				// Set the ID
				$key      = $this->getPrimaryKey();
				$item->id = $item->$key;

				// Fill in non-existing fields
				foreach ($this->getEmptyFields() as $fieldName => $fieldValue)
				{
					if (!isset($item->$fieldName))
					{
						$item->$fieldName = $fieldValue;
					}
				}

				// Re-insert this item
				$data[$index] = $item;
			}

			if ($this->getConfig('limit_query') == false)
			{
				$this->total = count($data);
			}

			$this->data = $data;
		}

		// Allow to modify the data afterwards
		if (method_exists($this, 'onDataLoadAfter'))
		{
			$this->data = $this->onDataLoadAfter($this->data);
		}

		return $this->data;
	}

	/**
	 * Method to store the model
	 *
	 * @param mixed $data
	 *
	 * @return null
	 * @throws BadMethodCallException
	 *
	 */
	public function store($data)
	{
		throw new BadMethodCallException('Unable to store multiple data');
	}

	/**
	 * Method to get the ordering query
	 *
	 * @return string
	 */
	public function getOrderingQuery()
	{
		if (!in_array($this->getConfig('orderby_default'), array('ordering', 'lft')))
		{
			return false;
		}

		/** @var JDatabaseDriver $db */
		$db = $this->db;

		$query = $db->getQuery(true);
		$query->select($db->quoteName($this->getConfig('orderby_default'), 'value'));
		$query->select($db->quoteName($this->getConfig('orderby_title'), 'text'));
		$query->from($db->quoteName($this->table->getTableName()));
		$query->order($db->quoteName($this->getConfig('orderby_default')));

		return $query;
	}

	/**
	 * Method to get empty fields
	 *
	 * @return array
	 */
	protected function getEmptyFields()
	{
		$data = array(
			'published'    => 1,
			'publish_up'   => null,
			'publish_down' => null,
			'state'        => 1,
			'access'       => 1,
			'ordering'     => 0,
			'lft'          => 0,
			'rgt'          => 0,
		);

		return $data;
	}

	/**
	 * Method to initialise the data
	 *
	 * @return bool
	 */
	protected function getEmpty()
	{
		// Define the fields to initialize
		$data = $this->getEmptyFields();

		// Lets load the data if it doesn't already exist
		if (empty($this->data))
		{
			$this->data = array();

			return true;
		}

		return false;
	}

	/**
	 * Check whether this record can be edited
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	protected function canEditState($data)
	{
		// Check the permissions for this edit.state action
		if ($this->getId() > 0)
		{
			return $this->user->authorise('core.edit.state', $this->getConfig('option') . '.' . $this->getConfig('table_alias') . '.' . (int) $this->getId());
		}

		return $this->user->authorise('core.edit.state', $this->getConfig('option'));
	}

	/**
	 * Method to get the default ORDER BY value
	 *
	 * @return string
	 */
	public function getOrderByDefault()
	{
		return $this->getConfig('orderby_default');
	}

	/**
	 * Method to reset all filters
	 *
	 * @return string
	 */
	public function resetFilters()
	{
		$this->search   = null;
		$this->where    = array();
		$this->_orderby = array();

		$this->resetLimits();
	}
}