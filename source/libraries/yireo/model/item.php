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
class YireoModelItem extends YireoDataModel
{
	/**
	 * Trait to implement checkout behaviour
	 */
	use YireoModelTraitCheckable;

	/**
	 * Trait to implement filter behaviour
	 */
	use YireoModelTraitFilterable;

	/**
	 * Boolean to allow for caching
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('cache') instead
	 */
	protected $_cache = false;

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

		$this->initSingle();

		// Set the parameters for the frontend
		$this->initParams();

		return $rt;
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
	 * Method to get data
	 *
	 * @param bool $forceNew
	 *
	 * @return array
	 * @throws \Yireo\Exception\Model\NotFound
	 */
	public function getData($forceNew = false)
	{
		if (!empty($this->data) && $forceNew === false)
		{
			return $this->data;
		}

		// Load some empty data-set
		$this->getEmpty();

		// Try to load the temporary data from this session
		$this->loadTmpSession();

		if ($this->getId() > 0)
		{
			$queryConfig             = array();
			$queryConfig['checkout'] = $this->allowCheckout();

			$query = $this->query->setConfig($queryConfig)
				->setId($this->getId())
				->build();
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

			// Allow to modify the data afterwards
			if (method_exists($this, 'onDataLoadAfter'))
			{
				$this->data = $this->onDataLoadAfter($this->data);
			}
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
		$this->db->setQuery($query);
		$this->db->execute();

		return true;
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
		return $this->query->addWhere($where, $type);
	}

	/**
	 * Method to add an extra query argument
	 *
	 * @param string $extra
	 */
	public function addExtra($extra = null)
	{
		return $this->query->addExtra($extra);
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
}