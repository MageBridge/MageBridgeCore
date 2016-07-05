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
require_once dirname(__FILE__) . '/loader.php';

require_once 'model/trait/paginable.php';
require_once 'model/trait/checkable.php';
require_once 'model/trait/filterable.php';
require_once 'model/trait/limitable.php';
require_once 'model/trait/debuggable.php';
require_once 'model/trait/table.php';

/**
 * Yireo Model
 * Parent class for models that use the full-blown MVC pattern
 *
 * @package    Yireo
 * @deprecated Use YireoModelItem or YireoModelItems instead
 */
class YireoModel extends YireoCommonModel
{
	/**
	 * Trait to implement debugging behaviour
	 */
	use YireoModelTraitDebuggable;

	/**
	 * Trait to implement table behaviour
	 */
	use YireoModelTraitTable;

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
	 * @var mixed
	 */
	protected $data;

	/**
	 * Indicator if this is a model for multiple or single entries
	 *
	 * @var bool
	 * @deprecated Use YireoModelItem or YireoModelItems instead
	 */
	protected $single = false;

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
	 * Where segments
	 *
	 * @var array
	 */
	protected $where = array();

	/**
	 * Search columns
	 *
	 * @var array
	 * @deprecated Use $this->getConfig('search_fields') instead
	 */
	protected $search = array();

	/**
	 * Search columns
	 *
	 * @var array
	 * @deprecated Use $this->getConfig('search_fields') instead
	 */
	protected $_search = array();

	/**
	 * Order-by segments
	 *
	 * @var array
	 */
	protected $_orderby = array();

	/**
	 * Group-by segments
	 *
	 * @var array
	 */
	protected $_groupby = array();

	/**
	 * Extra query segments
	 *
	 * @var array
	 */
	protected $_extra = array();

	/**
	 * Extra select fields
	 *
	 * @var array
	 */
	protected $_extraFields = array();

	/**
	 * Order-by default-value
	 *
	 * @var string
	 */
	protected $_orderby_default = null;

	/**
	 * Order-by default-title
	 *
	 * @var string
	 */
	protected $_orderby_title = null;

	/**
	 * List of fields to autoconvert into column-seperated fields
	 *
	 * @var array
	 */
	protected $_columnFields = array();

	/**
	 * @var \Joomla\Registry\Registry
	 */
	protected $params;

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
		$this->setTablePrefix();
		$this->table = $this->getTable($this->getConfig('table_alias'));

		if ($this->isSingular())
		{
			$this->initSingle();
		}
		else
		{
			$this->initOrderBy();
			$this->initPlural();
		}

		// Set the parameters for the frontend
		$this->initParams();

		$this->handleModelDeprecated();

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
			$rs    = $cache->call(array($this, '_getDbResult'), $query, $type);
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

	/**
	 * Initialize ORDER BY details
	 */
	protected function initOrderBy()
	{
		if (empty($this->_orderby_default))
		{
			$this->_orderby_default = $this->table->getDefaultOrderBy();
		}

		if (empty($this->_orderby_title))
		{
			if ($this->table->hasField('title'))
			{
				$this->_orderby_title = 'title';
			}

			if ($this->table->hasField('label'))
			{
				$this->_orderby_title = 'label';
			}

			if ($this->table->hasField('name'))
			{
				$this->_orderby_title = 'name';
			}
		}
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
			$this->params = JComponentHelper::getParams($this->_option);

			return $this->params;
		}

		$this->params = $this->app->getParams($this->_option);

		return $this->params;
	}

	/**
	 * Inititalize system variables
	 */
	protected function initSingle()
	{
		$cid = $this->input->get('cid', array(0), '', 'array');

		if (!empty($cid) && count($cid) > 0)
		{
			$this->setId((int) $cid[0]);
		}

		$id = $this->input->getInt('id', 0);

		if (!empty($id) && $id > 0)
		{
			$this->setId((int) $id);
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
		$filter_order     = $this->getFilter('order', '{tableAlias}.' . $this->_orderby_default, 'string');
		$filter_order_Dir = $this->getFilter('order_Dir');

		if (!empty($filter_order_Dir))
		{
			$filter_order_Dir = ' ' . strtoupper($filter_order_Dir);
		}

		$this->addOrderby($filter_order . $filter_order_Dir);
		$this->addOrderby('{tableAlias}.' . $this->_orderby_default);
	}

	/**
	 * Handle deprecated variables
	 */
	protected function handleModelDeprecated()
	{
		$this->_table = $this->table;

		if (!empty($this->_search))
		{
			$this->setConfig('search_fields', $this->_search);
		}

		if (!empty($this->search))
		{
			$this->setConfig('search_fields', $this->search);
		}
	}

	/**
	 * Method to override a default user-state value
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function overrideUserState($key, $value)
	{
		$this->$key = $value;

		return true;
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
		// Load the data if they are not just set or if the force-flag is set
		if ($this->data === null || $forceNew)
		{
			// Load some empty data-set
			$this->getEmpty();

			// Try to load the temporary data from this session
			$this->loadTmpSession();

			// Singular model
			if ($this->isSingular() && $this->getId() > 0)
			{
				$query = $this->buildQuery();
				$data  = $this->getDbResult($query, 'object');

				if (!empty($data))
				{
					// Prepare the column-fields
					if (!empty($this->_columnFields))
					{
						foreach ($this->_columnFields as $columnField)
						{
							if (!empty($data->$columnField) && !is_array($data->$columnField))
							{
								$data->$columnField = explode('|', $data->$columnField);
							}
						}
					}

					// Allow to modify the data
					if (method_exists($this, 'onDataLoad'))
					{
						$data = $this->onDataLoad($data);
					}

					// Set the ID
					$key      = $this->getPrimaryKey();
					$data->id = $data->$key;

					$data->metadata = $this->getConfig();
					$this->data     = $data;

				}
				else
				{
					$data = (object) null;
				}

				// Check to see if the data is published
				$stateField = $this->table->getStateField();

				if ($this->app->isSite() && isset($data->$stateField) && $data->$stateField == 0)
				{
					throw new \Yireo\Exception\Model\NotFound(JText::_('LIB_YIREO_MODEL_NOT_FOUND'));
				}

				// Fill in non-existing fields
				foreach ($this->getEmptyFields() as $fieldName => $fieldValue)
				{
					if (!isset($data->$fieldName))
					{
						$data->$fieldName = $fieldValue;
					}
				}

				// Plural model
			}
			else
			{
				if ($this->isSingular() == false)
				{
					$query = $this->buildQuery();
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
				}
			}

			// Allow to modify the data afterwards
			if (method_exists($this, 'onDataLoadAfter'))
			{
				$this->data = $this->onDataLoadAfter($this->data);
			}
		}

		if ($this->isSingular() == false && $this->getConfig('limit_query') == false && $this->getState('limit') > 0)
		{
			$part = array_slice($this->data, (int) $this->getState('limitstart'), $this->getState('limit'));

			return $part;
		}

		return $this->data;
	}

	/**
	 * Method to store the model
	 *
	 * @param mixed $data
	 *
	 * @return bool
	 */
	public function store($data)
	{
		// Check the integrity of data
		if (empty($data) || !is_array($data))
		{
			$this->saveTmpSession($data);

			return false;
		}

		// Get the user metadata
		jimport('joomla.utilities.date');
		$now = new JDate('now');
		$uid = $this->user->get('id');

		// Convert the JForm array into the default data-set
		$fieldgroups = array('text', 'basic', 'item');

		foreach ($fieldgroups as $fieldgroup)
		{
			if (!empty($data[$fieldgroup]) && is_array($data[$fieldgroup]))
			{
				foreach ($data[$fieldgroup] as $name => $value)
				{
					$data[$name] = $value;
				}

				unset($data[$fieldgroup]);
			}
		}

		// Automatically set some data
		$data['modified']      = $now->toSql();
		$data['modified_date'] = $now->toSql();
		$data['modified_by']   = $uid;

		// Set the creation date if the item is new
		if (empty($data['id']) || $data['id'] == 0)
		{
			$data['created']      = $now->toSql();
			$data['created_date'] = $now->toSql();
			$data['created_by']   = $uid;
		}

		// Autocorrect the publish_up and publish_down dates
		if (isset($data['params']['publish_up']) && isset($data['params']['publish_down']))
		{
			$publish_up   = strtotime($data['params']['publish_up']);
			$publish_down = strtotime($data['params']['publish_down']);

			if ($publish_up >= $publish_down)
			{
				$data['params']['publish_down'] = null;
			}
		}

		// All parameters to override these values
		if (isset($data['params']) && is_array($data['params']))
		{
			if (!empty($data['params']['created']))
			{
				$data['created'] = $data['params']['created'];
			}

			if (!empty($data['params']['created_date']))
			{
				$data['created'] = $data['params']['created_date'];
			}

			if (!empty($data['params']['created_by']))
			{
				$data['created_by'] = $data['params']['created_by'];
			}

			if (!empty($data['params']['modified']))
			{
				$data['modified'] = $data['params']['modified'];
			}

			if (!empty($data['params']['modified_date']))
			{
				$data['modified'] = $data['params']['modified_date'];
			}

			if (!empty($data['params']['modified_by']))
			{
				$data['modified_by'] = $data['params']['modified_by'];
			}
		}

		// Unset these parameters
		if (isset($data['params']) && is_array($data['params']))
		{
			if (isset($data['params']['created']))
			{
				unset($data['params']['created']);
			}

			if (isset($data['params']['created_date']))
			{
				unset($data['params']['created_date']);
			}

			if (isset($data['params']['created_by']))
			{
				unset($data['params']['created_by']);
			}

			if (isset($data['params']['modified']))
			{
				unset($data['params']['modified']);
			}

			if (isset($data['params']['modified_date']))
			{
				unset($data['params']['modified_date']);
			}

			if (isset($data['params']['modified_by']))
			{
				unset($data['params']['modified_by']);
			}
		}

		// Prepare the column-fields
		if (!empty($this->_columnFields))
		{
			foreach ($this->_columnFields as $columnField)
			{
				if (!empty($data[$columnField]) && is_array($data[$columnField]))
				{
					$data[$columnField] = implode('|', $data[$columnField]);
				}
			}
		}

		// Bind the fields to the table
		if (!$this->table->bind($data))
		{
			$this->saveTmpSession($data);
			$this->throwDbException();
		}

		// Make sure the table is valid
		if (!$this->table->check())
		{
			$this->saveTmpSession($data);
			$this->throwDbException();
		}

		// Store the table to the database
		if (!$this->table->store())
		{
			$this->saveTmpSession($data);
			$this->throwDbException();
		}

		// Try to fetch the last ID from the table
		$id = $this->table->getLastInsertId();

		if ((!isset($this->id) || !$this->id > 0) && $id > 0)
		{
			$this->id = $id;
		}

		return true;
	}

	/**
	 * Method to remove multiple items
	 *
	 * @param array $cid
	 *
	 * @return bool
	 */
	public function delete($cid = array())
	{
		if (!count($cid) > 0)
		{
			return false;
		}

		$tableName = $this->table->getTableName();
		$primaryKey = $this->table->getKeyName();

		if (empty($tableName))
		{
			throw new RuntimeException(JText::_('LIB_YIREO_MODEL_ITEM_NO_TABLE_NAME'));
		}

		if (empty($primaryKey))
		{
			throw new RuntimeException(JText::_('LIB_YIREO_MODEL_ITEM_NO_TABLE_KEY'));
		}

		\Joomla\Utilities\ArrayHelper::toInteger($cid);
		$cids  = implode(',', $cid);

		$query = $this->_db->getQuery(true);
		$query->delete($this->_db->quoteName($tableName));
		$query->where($this->_db->quoteName($primaryKey) . ' IN (' . $cids . ')');

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->throwDbException();
		}

		return true;
	}

	/**
	 * Method to (un)publish an item
	 *
	 * @param array $cid
	 * @param int   $publish
	 *
	 * @return bool
	 */
	public function publish($cid = array(), $publish = 1)
	{
		if (count($cid))
		{
			$return = $this->table->publish($cid, $publish, $this->user->get('id'));

			return $return;
		}

		return true;
	}

	/**
	 * Method to move an item
	 *
	 * @param mixed  $direction
	 * @param string $field_name
	 * @param int    $field_id
	 *
	 * @return bool
	 */
	public function move($direction, $field_name = null, $field_id = null)
	{
		if (!$this->table->load($this->id))
		{
			$this->throwDbException();
		}

		if (!empty($field_name) && !empty($field_id))
		{
			$rt = $this->table->move($direction, ' ' . $field_name . ' = ' . $field_id);
		}
		else
		{
			$rt = $this->table->move($direction);
		}

		if ($rt == false)
		{
			$this->throwDbException();
		}

		return true;
	}

	/**
	 * Method to reorder items
	 *
	 * @param array  $cid
	 * @param string $order
	 *
	 * @return bool
	 */
	public function saveorder($cid = array(), $order)
	{
		$groupings = array();

		// update ordering values
		for ($i = 0; $i < count($cid); $i++)
		{
			// Load the table
			$this->table->load((int) $cid[$i]);

			// Track extra fields
			if ($this->table->hasField('category_id'))
			{
				$groupings['category_id'] = $this->table->category_id;
			}
			else
			{
				if ($this->table->hasField('parent_id'))
				{
					$groupings['parent_id'] = $this->table->parent_id;
				}
			}

			// Save the ordering
			$ordering = $this->table->getDefaultOrderBy();

			if ($this->table->$ordering != $order[$i])
			{
				$this->table->$ordering = $order[$i];

				if (!$this->table->store())
				{
					$this->throwDbException();
				}
			}
		}

		// Execute updateOrder for each parent group
		$groupings = array_unique($groupings);

		foreach ($groupings as $fieldName => $group)
		{
			$this->table->reorder($fieldName . ' = ' . (int) $group);
		}

		return true;
	}

	/**
	 * Method to increment the hit counter for the item
	 *
	 * @return bool
	 */
	public function hit()
	{
		if ($this->id)
		{
			$this->table->hit($this->id);

			return true;
		}

		return false;
	}

	/**
	 * Method to toggle a certain field
	 *
	 * @param int    $id
	 * @param string $name
	 * @param string $value
	 *
	 * @return bool
	 */
	public function toggle($id, $name, $value)
	{
		if (!$id > 0)
		{
			return false;
		}

		if (empty($name))
		{
			return false;
		}

		$value = ($value == 1) ? 0 : 1;
		$query = 'UPDATE `' . $this->table->getTableName() . '` SET `' . $name . '`=' . $value . ' WHERE `' . $this->table->getKeyName() . '`=' . (int) $id;
		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}

	/**
	 * Method to build the query
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected function buildQuery($query = '')
	{
		// Get the WHERE clauses for the query
		$where = $this->buildQueryWhere();

		// Get the ORDER BY clauses for the query
		$orderby = ($this->isSingular()) ? null : $this->buildQueryOrderBy();

		// Get the GROUP BY clauses for the query
		$groupby = $this->buildQueryGroupBy();

		// Get the extra segments for the query
		$extra = $this->buildQueryExtra();

		// Get the LIMIT segments for the query
		$limitString = null;

		if ($this->getConfig('limit_query') == true)
		{
			$limitstart = $this->getState('limitstart');
			$limit      = $this->getState('limit');

			if (!(empty($limit) && empty($limitStart)))
			{
				$limitString = ' LIMIT ' . $limitstart . ',' . $limit;
			}
		}

		// Build the default query if not set
		if (empty($query))
		{
			// Skip certain fields in frontend
			$skipFrontendFields = array(
				'locked',
				'published',
				'published_up',
				'published_down',
				'checked_out',
				'checked_out_time'
			);

			// Build the fields-string to avoid a *
			$fields        = $this->table->getDatabaseFields();
			$fieldsStrings = array();

			foreach ($fields as $field)
			{
				if ($this->app->isSite() && in_array($field, $skipFrontendFields))
				{
					continue;
				}

				$fieldsStrings[] = '`{tableAlias}`.`' . $field . '`';
			}

			// Append extra fields
			if (!empty($this->_extraFields))
			{
				foreach ($this->_extraFields as $extraField)
				{
					$fieldsStrings[] = $extraField;
				}
			}

			$fieldsString = implode(',', $fieldsStrings);

			// Frontend or backend query
			if ($this->allowCheckout() == true && $this->app->isAdmin())
			{
				$query = "SELECT " . $fieldsString . ", `editor`.`name` AS `editor` FROM `{table}` AS `{tableAlias}`\n";
				$query .= " LEFT JOIN `#__users` AS `editor` ON `{tableAlias}`.`checked_out` = `editor`.`id`\n";
			}
			else
			{
				$query = "SELECT " . $fieldsString . " FROM `{table}` AS `{tableAlias}`\n";
			}
		}

		// Add-in access-details
		if (strstr($query, '{access}'))
		{
			$query = str_replace('{access}', '`viewlevel`.`title` AS `accesslevel`', $query);
			$query .= " LEFT JOIN `#__viewlevels` AS `viewlevel` ON `viewlevel`.`id`=`" . $this->getConfig('table_alias') . "`.`access`\n";
		}

		// Add-in editor-details
		if (strstr($query, '{editor}'))
		{
			$query = str_replace('{editor}', '`user`.`name` AS `editor`', $query);
			$query .= " LEFT JOIN `#__users` AS `user` ON `user`.`id`=`" . $this->getConfig('table_alias') . "`.`checked_out`\n";
		}

		// Return the query including WHERE and ORDER BY and LIMIT
		$query = $query . $extra . $where . $groupby . $orderby . $limitString;
		$query = str_replace('{table}', $this->table->getTableName(), $query);
		$query = str_replace('{tableAlias}', $this->getConfig('table_alias'), $query);
		$query = str_replace('{primary}', $this->table->getKeyName(), $query);

		return $query;
	}

	/**
	 * Method to build the query ORDER BY segment
	 *
	 * @return string
	 */
	protected function buildQueryOrderBy()
	{
		$this->_orderby = array_unique($this->_orderby);

		if (count($this->_orderby))
		{
			foreach ($this->_orderby as $index => $orderby)
			{
				$orderby = trim($orderby);

				if (empty($orderby))
				{
					unset($this->_orderby[$index]);
				}
			}

			if (!empty($this->_orderby))
			{
				return ' ORDER BY ' . implode(', ', $this->_orderby) . "\n";
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
		$this->_groupby = array_unique($this->_groupby);

		if (count($this->_groupby))
		{
			foreach ($this->_groupby as $index => $groupby)
			{
				$groupby = trim($groupby);

				if (empty($groupby))
				{
					unset($this->_groupby[$index]);
				}
			}

			if (!empty($this->_groupby))
			{
				return ' GROUP BY ' . implode(', ', $this->_groupby) . "\n";
			}
		}

		return null;
	}

	/**
	 * Method to build the query WHERE segment
	 *
	 * @return string
	 */
	protected function buildQueryWhere()
	{
		// Automatically add the WHERE-statement for a single ID-based query
		if ($this->isSingular())
		{
			$this->addWhere('`{tableAlias}`.`{primary}`=' . (int) $this->id);
		}

		// Automatically add a WHERE-statement if the state-filter is used
		$state = $this->getFilter('state');

		if ($state == 'U' || $state == 'P')
		{
			$state      = ($state == 'U') ? 0 : 1;
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->db->quoteName($this->getConfig('table_alias')) . '.`' . $stateField . '` = ' . $state);
			}
		}

		// Automatically add a WHERE-statement if only published items should appear on the frontend
		if ($this->app->isSite())
		{
			$stateField = $this->table->getStateField();

			if (!empty($stateField))
			{
				$this->addWhere($this->getConfig('table_alias') . '.' . $stateField . ' = 1');
			}
		}

		// Automatically add a WHERE-statement if the search-filter is used
		$search       = $this->getFilter('search');
		$searchFields = $this->getConfig('search_fields');

		if (!empty($searchFields) && !empty($search))
		{
			$where_search = array();

			foreach ($searchFields as $searchField)
			{
				if (strstr($searchField, '.') == false && strstr($searchField, '`') == false)
				{
					$searchField = $this->db->quoteName($searchField);
				}

				if (strstr($searchField, '.') == false)
				{
					$searchField = $this->db->quoteName($this->getConfig('table_alias')) . "." . $searchField;
				}

				$where_search[] = "$searchField LIKE " . $this->db->quote("%$search%");
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
	 * Method to build an extra query segment
	 *
	 * @return string
	 */
	protected function buildQueryExtra()
	{
		if (count($this->_extra) > 0)
		{
			return ' ' . implode(' ', $this->_extra) . "\n";
		}

		return '';
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

		if (is_string($orderby) && !isset($this->_orderby[$orderby]))
		{
			if (strstr($orderby, '.') == false && preg_match('/^RAND/', $orderby) == false)
			{
				$orderby = '{tableAlias}.' . $orderby;
			}

			if (strstr($orderby, 'accesslevel'))
			{
				$orderby = str_replace('{tableAlias}.', '', $orderby);
			}

			$this->_orderby[] = $orderby;
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

		if (is_string($groupby) && !isset($this->_groupby[$groupby]))
		{
			if (strstr($groupby, '.') == false)
			{
				$groupby = '{tableAlias}.' . $groupby;
			}

			$this->_groupby[] = $groupby;
		}

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

		if (is_array($where))
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
	 * Method to add an extra query argument
	 *
	 * @param string $extra
	 */
	public function addExtra($extra = null)
	{
		if (is_string($extra))
		{
			$this->_extra[] = $extra;
		}
	}

	/**
	 * Method to get the ordering query
	 *
	 * @return string
	 */
	public function getOrderingQuery()
	{
		if (!in_array($this->_orderby_default, array('ordering', 'lft')))
		{
			return false;
		}

		/** @var JDatabaseDriver $db */
		$db = $this->db;

		$query = $db->getQuery(true);
		$query->select($db->quoteName($this->_orderby_default, 'value'));
		$query->select($db->quoteName($this->_orderby_title, 'text'));
		$query->from($db->quoteName($this->table->getTableName()));
		$query->order($db->quoteName($this->_orderby_default));

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

			if ($this->isPlural())
			{
				$this->data = array();

				return true;
			}

			$this->data = (object) $this->table->getProperties();

			foreach ($data as $name => $value)
			{
				$this->data->$name = $value;
			}

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
	 * Method to determine whether this model is singular or not
	 *
	 * @return bool
	 */
	public function isSingular()
	{
		$className = get_class($this);

		if (preg_match('/s$/', $className))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to determine whether this model is plural or not
	 *
	 * @return bool
	 */
	public function isPlural()
	{
		if ($this->isSingular())
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to get the default ORDER BY value
	 *
	 * @return string
	 */
	public function getOrderByDefault()
	{
		return $this->_orderby_default;
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

	/**
	 * Method to check if any errors are set
	 *
	 * @return boolean
	 */
	public function hasErrors()
	{
		return false;
	}
}
