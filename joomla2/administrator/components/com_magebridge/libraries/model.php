<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__).'/loader.php';

/**
 * Yireo Abstract Model
 *
 * @package Yireo
 */
if(YireoHelper::isJoomla25()) {
    jimport('joomla.application.component.model');
    class YireoAbstractModel extends JModel {}
} else {
    class YireoAbstractModel extends JModelLegacy {}
}

/**
 * Yireo Model 
 *
 * @package Yireo
 */
class YireoModel extends YireoAbstractModel
{
    /**
     * Indicator if this is a model for multiple or single entries
     *
     * @protected int
     */
    protected $_single = null;

    /**
     * Boolean to allow for caching
     *
     * @protected int
     */
    protected $_cache = false;

    /**
     * Boolean to allow for debugging
     *
     * @protected int
     */
    protected $_debug = false;

    /**
     * Boolean to allow for filtering
     *
     * @protected int
     */
    protected $_allow_filter = true;

    /**
     * Boolean to allow for checking out
     *
     * @protected int
     */
    protected $_checkout = true;

    /**
     * Database table object
     *
     * @protected string
     */
    protected $_tbl = null;

    /**
     * Database table-name
     *
     * @protected string
     */
    protected $_tbl_name = null;

    /**
     * Database table-alias
     *
     * @protected string
     */
    protected $_tbl_alias = null;

    /**
     * Database primary key
     *
     * @protected string
     */
    protected $_tbl_key = null;

    /**
     * Unique id
     *
     * @protected int
     */
    protected $_id = null;

    /**
     * Data array
     *
     * @protected array
     */
    protected $_data = null;

    /**
     * Category total
     *
     * @protected integer
     */
    protected $_total = null;

    /**
     * Pagination object
     *
     * @protected object
     */
    protected $_pagination = null;

    /**
     * List limit 
     *
     * @protected int
     */
    protected $_limit = null;

    /**
     * Limit start
     *
     * @protected int
     */
    protected $_limitstart = null;

    /**
     * Ordering field
     *
     * @protected string
     */
    protected $_ordering = null;

    /**
     * Where segments
     *
     * @protected array
     */
    protected $_where = array();

    /**
     * Search columns
     *
     * @protected array
     */
    protected $_search = array();

    /**
     * Order-by segments
     *
     * @protected array
     */
    protected $_orderby = array();

    /**
     * Group-by segments
     *
     * @protected array
     */
    protected $_groupby = array();

    /**
     * Extra query segments
     *
     * @protected array
     */
    protected $_extra = array();

    /**
     * Order-by default-value
     *
     * @protected string
     */
    protected $_orderby_default = null;

    /**
     * Order-by default-title
     *
     * @protected string
     */
    protected $_orderby_title = null;

    /**
     * Enable the limit in the query (or in the data-array)
     *
     * @protected string
     */
    protected $_limit_query = false;

    /**
     * Flag to force a value
     *
     * @constant boolean
     */
    const FORCE_NEW = true;

    /**
     * Constructor
     *
     * @access public
     * @subpackage Yireo
     * @param string $tableAlias
     * @return null
     */
    public function __construct($tableAlias = null)
    {
        // Call the parent constructor
        parent::__construct();

        // Import use full variables from JFactory
        $this->application = JFactory::getApplication();
        $this->user = JFactory::getUser();

        // Set the database variables
        // @todo: Set this in a metadata array 
        $this->_tbl_alias = $tableAlias;
        $this->_tbl = $this->getTable($tableAlias);
        $this->_tbl_name = $this->_tbl->getTableName();
        $this->_tbl_key = $this->_tbl->getKeyName();
        $this->_entity = $tableAlias;

        // Detect the orderby-default
        $this->_orderby_default = $this->_tbl->getDefaultOrderBy();
        if(empty($this->_orderby_title)) {
            if ($this->_tbl->hasField('title')) $this->_orderby_title = 'title';
            if ($this->_tbl->hasField('name')) $this->_orderby_title = 'name';
        }

        // Create the option-namespace
        $classParts = explode('Model', get_class($this));
        $this->_view = (!empty($classParts[1])) ? strtolower($classParts[1]) : JRequest::getCmd('view');
        $this->_option = $this->getOption();
        $this->_option_id = $this->_option.'_'.$this->_view.'_';
        if ($this->application->isSite()) $this->_option_id .= JRequest::getInt('Itemid').'_';

        // Set the parameters for the frontend
        if (empty($this->params)) {
            if ($this->application->isSite() == false) {
                $this->params = JComponentHelper::getParams($this->_option);
            } else {
                $this->params = $this->application->getParams($this->_option);
            }
        }

        // Enable debugging
        if($this->params->get('debug', 0) == 1) $this->_debug = true;

        // Determine whether this model is single or not
        if ($this->_single == null) {
            $className = get_class($this);
            if (preg_match('/s$/', $className)) {
                $this->_single = false;
            } else {
                $this->_single = true;
            }
        }

        // Initialize the ID for single records
        if ($this->isSingular()) {

            $cid = JRequest::getVar( 'cid', array(0), '', 'array' );
            if (!empty($cid) && count($cid) > 0) {
                $this->setId((int)$cid[0]);
            }

            $id = JRequest::getInt( 'id', 0 );
            if (!empty($id) && $id > 0) {
                $this->setId((int)$id);
            }

        // Multiple records
        } else {

            // Initialize limiting
            $this->initLimit();
            $this->initLimitstart();

            // Initialize ordering 
            $filter_order = $this->getFilter('order', '{tableAlias}.'.$this->_orderby_default, 'string');
            $filter_order_Dir = $this->getFilter('order_Dir');
            if (!empty($filter_order_Dir)) $filter_order_Dir = ' '.strtoupper($filter_order_Dir);

            $this->addOrderby($filter_order.$filter_order_Dir);
            $this->addOrderby('{tableAlias}.'.$this->_orderby_default);

        }
    }

    /**
     * Method to get the identifier
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return int 
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Method to set the identifier
     *
     * @access public
     * @subpackage Yireo
     * @param int $id
     * @param bool $reinit
     * @return null
     */
    public function setId($id = 0, $reinit = true)
    {
        $this->_id = $id;
        if ($reinit) $this->_data = null;
    }

    /**
     * Method to initialize the limit parameter
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function initLimit($limit = null) 
    {
        if (is_numeric($limit) == false) {
            $limit = $this->application->getUserStateFromRequest( 'global.list.limit', 'limit', $this->application->getCfg('list_limit'), 'int' );
        }

        if ($limit == 0) {
            $limit = $this->application->getCfg('list_limit');
            $this->application->setUserState('limit', $limit);
        }
        $this->setState('limit', $limit);
    }

    /**
     * Method to initialize the limitstart parameter
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function initLimitstart($limitstart = null) 
    {
        if (is_numeric($limitstart) == false) {
            $limitstart = $this->application->getUserStateFromRequest($this->_option_id.'limitstart', 'limitstart', 0, 'int');
        }
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Method to get a filter from the user-state
     *
     * @access public
     * @subpackage Yireo
     * @param string $filter
     * @param string $default
     * @param string $type
     * @param string $option
     * @return string
     */
    public function getFilter($filter = '', $default = '', $type = 'cmd', $option = '') 
    {
        if ($this->_allow_filter == false) return null;
        if (empty($option)) $option = $this->_option_id;
        $value = $this->application->getUserStateFromRequest( $option.'filter_'.$filter, 'filter_'.$filter, $default, $type );
        return $value;
    }

    /**
     * Method to override a default user-state value
     *
     * @access public
     * @subpackage Yireo
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function overrideUserState( $key, $value )
    {
        $this->$key = $value ;
        return true ;
    }

    /**
     * Method to get data
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return array
     */
    public function getData($forceNew = false)
    {
        // Load the data if they are not just set or if the force-flag is set
        if ($this->_data === null || $forceNew == self::FORCE_NEW) {

            // Load some empty data-set
            $this->getEmpty();

            // Try to load the temporary data from this session
            $this->loadTmpSession();

            // Singular model
            if ($this->isSingular() && $this->getId() > 0) {

                $query = $this->buildQuery();
                $data = $this->getDbResult($query, 'object');

                if (!empty($data)) {

                    // Allow to modify the data
                    if (method_exists($this, 'onDataLoad')) {
                        $data = $this->onDataLoad($data);
                    }

                    // Set the ID
                    $key = $this->getPrimaryKey();
                    $data->id = $data->$key;

                    $data->metadata = $this->getMetadata();
                    $this->_data = $data;
                }

                // Check to see if the data is published
                $stateField = $this->_tbl->getStateField();
                if ($this->application->isSite() && isset($data->$stateField) && $data->$stateField == 0) {
                    JError::raiseError(404, JText::_('LIB_YIREO_MODEL_NOT_FOUND'));
                    return;
                }

                // Fill in non-existing fields
                foreach($this->getEmptyFields() as $fieldName => $fieldValue) {
                    if(!isset($data->$fieldName)) {
                        $data->$fieldName = $fieldValue;
                    }
                }

            // Plural model
            } else if ($this->isSingular() == false) {

                $query = $this->buildQuery();
                $data = $this->getDbResult($query, 'objectList');

                if (!empty($data)) {

                    // Prepare these data
                    foreach ($data as $index => $item) {

                        // Frontend permissions
                        if ($this->application->isSite() && isset($item->access) && is_numeric($item->access)) {
                            $accessLevels = $this->user->getAuthorisedViewLevels();
                            if (YireoHelper::isJoomla25()) {
                                if (!array_key_exists($item->access, $accessLevels) || $accessLevels[$item->access] == 0) {
                                    unset($data[$index]);
                                    continue;
                                }
                            } else {
                                if ($item->access > 0 && !in_array($item->access, $accessLevels)) {
                                    unset($data[$index]);
                                    continue;
                                }
                            }
                        }

                        // Backend permissions
                        if ($this->application->isAdmin() && (bool)$this->_tbl->hasAssetId() == true) {

                            // Get the ID
                            $key = $this->getPrimaryKey();
                            $id = $item->$key;

                            // Asset data
                            $option = str_replace('com_', '#__', $this->_option);
                            $view = JRequest::getCmd('view');

                            // Construct the ACL-data
                            $action = ($id > 0) ? 'core.manage' : 'core.create';
                            $itemAsset = ($id > 0) ? $option.'_'.$view.'.'.$id : $option;

                            // Check
                            if ($this->user->authorise($action, $itemAsset) == false) {
                                unset($data[$index]);
                                continue;
                            }
                        }

                        // Prepare the parameters
                        if (isset($item->params)) {
                            $item->params = YireoHelper::toParameter($item->params);
                        } else {
                            $item->params = YireoHelper::toParameter();
                        }

                        // Check for publish_up and publish_down
                        if ($this->application->isSite()) {
                            $publish_up = $item->params->get('publish_up');
                            $publish_down = $item->params->get('publish_down');
                            if (!empty($publish_up) && strtotime($publish_up) > time()) {
                                unset($data[$index]);
                                continue;
                            } else if (!empty($publish_down) && strtotime($publish_down) < time()) {
                                unset($data[$index]);
                                continue;
                            }
                        }

                        // Allow to modify the data
                        if (method_exists($this, 'onDataLoad')) {
                            $item = $this->onDataLoad($item);
                        }
    
                        // Add the metadata
                        $item->metadata = $this->getMetadata();

                        // Set the ID
                        $key = $this->getPrimaryKey();
                        $item->id = $item->$key;
    
                        // Fill in non-existing fields
                        foreach($this->getEmptyFields() as $fieldName => $fieldValue) {
                            if(!isset($item->$fieldName)) {
                                $item->$fieldName = $fieldValue;
                            }
                        }

                        // Re-insert this item
                        $data[$index] = $item;
                    }

                    if($this->_limit_query == false) $this->_total = count($data);
                    $this->_data = $data;
                }
            }
        }

        if ($this->isSingular() == false && $this->_limit_query == false && $this->getState('limit') > 0) {
            $part = array_slice($this->_data, $this->getState('limitstart'), $this->getState('limit'));
            return $part;
        }

        return $this->_data;
    }

    /**
     * Method to get the total number of records
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return int
     */
    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {

            // The original database-query did NOT include a LIMIT statement
            if ($this->_limit_query == false) {
                $this->_total = count($this->_data);

            // The original database-query included a LIMIT statement, so we need a second query
            } else {
                $query = $this->buildQuery();
                $query = preg_replace('/^(.*)FROM/sm', 'SELECT COUNT(*) FROM', $query);
                $query = preg_replace('/LIMIT(.*)$/', '', $query);
                $query = preg_replace('/ORDER\ BY(.*)$/m', '', $query);

                $data = $this->getDbResult($query, 'result');
                $this->_total = (int)$data;
            }
        }

        return $this->_total;
    }

    /**
     * Method to get a pagination object for the fetched records
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return JPagination
     */
    public function getPagination()
    {
        // Lets load the pagination if it doesn't already exist
        if (empty($this->_pagination))
        {
            // Make sure the data is loaded
            $this->getData();
            $this->getTotal();

            // Reset pagination if it does not make sense
            if ($this->getState('limitstart') > $this->getTotal()) {
                $this->setState('limitstart', 0);
                $this->application->setUserState('limitstart', 0);
                $this->getData(self::FORCE_NEW);
            }

            // Build the pagination
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }

        return $this->_pagination;
    }

    /**
     * Tests if an item is checked out
     *
     * @access public
     * @subpackage Yireo
     * @param int $uid
     * @return bool
     */
    public function isCheckedOut($uid = 0)
    {
        if ($this->_checkout == true && $this->getData()) {
            if ($uid) {
                return ($this->_data->checked_out && $this->_data->checked_out != $uid);
            } else {
                return $this->_data->checked_out;
            }
        }

        return false;
    }

    /**
     * Method to checkin/unlock the table
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    public function checkin()
    {
        if ($this->_checkout == false) {
            return true;
        }

        if ($this->_id) {
            if (!$this->_tbl->checkin($this->_id)) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return false;
    }

    /**
     * Method to checkout/lock the table
     *
     * @access public
     * @subpackage Yireo
     * @param int $uid
     * @return bool
     */
    public function checkout($uid = null)
    {
        if ($this->_checkout == true) {
            return true;
        }

        if ($this->_id) {
            // Make sure we have a user id to checkout the item with
            if (is_null($uid)) {
                $uid = $this->user->get('id');
            }
            // Lets get to it and checkout the thing...
            if (!$this->_tbl->checkout($uid, $this->_id)) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * Method to store the model
     *
     * @access public
     * @subpackage Yireo
     * @param mixed $data
     * @return bool
     */
    public function store($data)
    {
        // Check the integrity of data
        if (empty($data) || !is_array($data)) {
            $this->setError('Invalid data');
            $this->saveTmpSession($data);
            return false;
        }

        // Get the user metadata
        jimport('joomla.utilities.date');
        $now = new JDate('now');
        $uid = $this->user->get('id');

        // Convert the JForm array into the default data-set
        $fieldgroups = array('text', 'basic', 'item');
        foreach($fieldgroups as $fieldgroup) {
            if (!empty($data[$fieldgroup]) && is_array($data[$fieldgroup])) {
                foreach($data[$fieldgroup] as $name => $value) {
                    $data[$name] = $value;
                }
                unset($data[$fieldgroup]);
            }
        }

        // Automatically set some data
        $data['modified'] = (method_exists('JDate', 'toSql')) ? $now->toSql() : $now->toMySQL();
        $data['modified_date'] = (method_exists('JDate', 'toSql')) ? $now->toSql() : $now->toMySQL();
        $data['modified_by'] = $uid;

        // Set the creation date if the item is new
        if (empty($data['id']) || $data['id'] == 0) {
            $data['created'] = (method_exists('JDate', 'toSql')) ? $now->toSql() : $now->toMySQL();
            $data['created_date'] = (method_exists('JDate', 'toSql')) ? $now->toSql() : $now->toMySQL();
            $data['created_by'] = $uid;
        }

        // Autocorrect the publish_up and publish_down dates
        if (isset($data['params']['publish_up']) && isset($data['params']['publish_down'])) {
            $publish_up = strtotime($data['params']['publish_up']);
            $publish_down = strtotime($data['params']['publish_down']);
            if ($publish_up >= $publish_down) $data['params']['publish_down'] = null;
        }

        // All parameters to override these values
        if (isset($data['params']) && is_array($data['params'])) {
            if (!empty( $data['params']['created'])) $data['created'] = $data['params']['created'];
            if (!empty( $data['params']['created_date'])) $data['created'] = $data['params']['created_date'];
            if (!empty( $data['params']['created_by'])) $data['created_by'] = $data['params']['created_by'];
            if (!empty( $data['params']['modified'])) $data['modified'] = $data['params']['modified'];
            if (!empty( $data['params']['modified_date'])) $data['modified'] = $data['params']['modified_date'];
            if (!empty( $data['params']['modified_by'])) $data['modified_by'] = $data['params']['modified_by'];
        }

        // Unset these parameters
        if (isset($data['params']) && is_array($data['params'])) {
            if (isset($data['params']['created'])) unset( $data['params']['created'] );
            if (isset($data['params']['created_date'])) unset( $data['params']['created_date'] );
            if (isset($data['params']['created_by'])) unset( $data['params']['created_by'] );
            if (isset($data['params']['modified'])) unset( $data['params']['modified'] );
            if (isset($data['params']['modified_date'])) unset( $data['params']['modified_date'] );
            if (isset($data['params']['modified_by'])) unset( $data['params']['modified_by'] );
        }

        // Bind the form fields to the table
        if (!$this->_tbl->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            $this->saveTmpSession($data);
            return false;
        }

        // Make sure the table is valid
        if (!$this->_tbl->check()) {
            $this->setError($this->_tbl->getErrorMsg());
            $this->saveTmpSession($data);
            return false;
        }

        // Store the table to the database
        if (!$this->_tbl->store()) {
            $this->setError($this->_db->getErrorMsg());
            $this->saveTmpSession($data);
            return false;
        }

        // Try to fetch the last ID from the table
        $id = $this->_tbl->getLastInsertId();
        if ((!isset($this->_id) || !$this->_id > 0) && $id > 0) {
            $this->_id = $id;
        }

        return true;
    }
    
    /**
     * Method to remove multiple items
     *
     * @access public
     * @subpackage Yireo
     * @param array $cid
     * @return bool
     */
    public function delete($cid = array())
    {
        if (count($cid) && !empty($this->_tbl_name) && !empty($this->_tbl_key)) {
            JArrayHelper::toInteger($cid);
            $cids = implode( ',', $cid );
            $query = 'DELETE FROM '.$this->_tbl_name.' WHERE '.$this->_tbl_key.' IN ( '.$cids.' )';
            $this->_db->setQuery( $query );
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }

        return true;
    }

    /**
     * Method to (un)publish an item
     *
     * @access public
     * @subpackage Yireo
     * @param array $cid
     * @param int $publish
     * @return bool
     */
    public function publish($cid = array(), $publish = 1)
    {
        if (count($cid)) {
            $return = $this->_tbl->publish($cid, $publish, $this->user->get('id'));
            return $return;
        }
        return true;
    }

    /**
     * Method to move an item 
     *
     * @access public
     * @subpackage Yireo
     * @param mixed $direction
     * @param string $field_name
     * @param int $field_id
     * @return bool
     */
    public function move($direction, $field_name = null, $field_id = null)
    {
        if (!$this->_tbl->load($this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!empty($field_name) && !empty($field_id)) {
            $rt = $this->_tbl->move($direction, ' '.$field_name.' = '.$field_id);
        } else {
            $rt = $this->_tbl->move($direction);
        }

        if ($rt == false) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Method to reorder items
     *
     * @access public
     * @subpackage Yireo
     * @param array $cid
     * @param string $order
     * @return bool
     */
    public function saveorder($cid = array(), $order)
    {
        $groupings = array();

        // update ordering values
        for( $i=0; $i < count($cid); $i++ ) {

            // Load the table
            $this->_tbl->load((int)$cid[$i]);

            // Track extra fields
            if ($this->_tbl->hasField('category_id')) {
                $groupings['category_id'] = $this->_tbl->category_id;
            } else if ($this->_tbl->hasField('parent_id')) {
                $groupings['parent_id'] = $this->_tbl->parent_id;
            }

            // Save the ordering
            $ordering = $this->_tbl->getDefaultOrderBy();
            if ($this->_tbl->$ordering != $order[$i]) {
                $this->_tbl->$ordering = $order[$i];
                if (!$this->_tbl->store()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // Execute updateOrder for each parent group
        $groupings = array_unique($groupings);
        foreach ($groupings as $fieldName => $group){
            $this->_tbl->reorder($fieldName.' = '.(int)$group);
        }

        return true;
    }

    /**
     * Method to increment the hit counter for the item
     *
     * @access public
     * @return bool
     */
    public function hit()
    {
        if ($this->_id)
        {
            $this->_tbl->hit($this->_id);
            return true;
        }
        return false;
    }

    /**
     * Method to toggle a certain field
     *
     * @access public
     * @return bool
     */
    public function toggle($id, $name, $value)
    {
        if (!$id > 0) return false;
        if (empty($name)) return false;

        $value = ($value == 1) ? 0 : 1;
        $query = 'UPDATE `'.$this->_tbl_name.'` SET `'.$name.'`='.$value.' WHERE `'.$this->_tbl_key.'`='.(int)$id;
        $this->_db->setQuery($query);
        $this->_db->query();
        return true;
    }

    /**
     * Method to build the query
     *
     * @access protected
     * @subpackage Yireo
     * @param string $query
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
        $limit = null;
        if ($this->_limit_query == true) {
            $limit = ' LIMIT '.$this->getState('limitstart').','.($this->getState('limit'));
        }

        // Build the default query if not set
        if (empty($query)) {

            // Build the fields-string to avoid a *
            $fields = $this->_tbl->getDatabaseFields();
            $fieldsStrings = array();
            foreach($fields as $field) {
                $fieldsStrings[] = '`{tableAlias}`.`'.$field.'`';
            }
            $fieldsString = implode(',', $fieldsStrings);

            // Frontend or backend query
            if ($this->_checkout == true && $this->application->isAdmin()) {
                $query = "SELECT ".$fieldsString.", `editor`.`name` AS `editor` FROM `{table}` AS `{tableAlias}`\n";
                $query .= " LEFT JOIN `#__users` AS `editor` ON `{tableAlias}`.`checked_out` = `editor`.`id`\n";
            } else {
                $query = "SELECT ".$fieldsString." FROM `{table}` AS `{tableAlias}`\n";
            }
        }

        // Add-in access-details
        if (strstr($query, '{access}')) {
            $query = str_replace('{access}', '`viewlevel`.`title` AS `accesslevel`', $query);
            $query .= " LEFT JOIN `#__viewlevels` AS `viewlevel` ON `viewlevel`.`id`=`".$this->_tbl_alias."`.`access`\n";
        }

        // Add-in editor-details
        if (strstr($query, '{editor}')) {
            $query = str_replace('{editor}', '`user`.`name` AS `editor`', $query);
            $query .= " LEFT JOIN `#__users` AS `user` ON `user`.`id`=`".$this->_tbl_alias."`.`checked_out`\n";
        }

        // Return the query including WHERE and ORDER BY and LIMIT
        $query = $query . $extra . $where . $groupby . $orderby . $limit;
        $query = str_replace( '{table}', $this->_tbl_name, $query );
        $query = str_replace( '{tableAlias}', $this->_tbl_alias, $query );
        $query = str_replace( '{primary}', $this->_tbl_key, $query );
        return $query ;
    }

    /**
     * Method to build the query ORDER BY segment
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    protected function buildQueryOrderBy()
    {
        $this->_orderby = array_unique($this->_orderby);
        if (count($this->_orderby)) {
            foreach ($this->_orderby as $index => $orderby) {
                $orderby = trim($orderby);
                if (empty($orderby)) unset($this->_orderby[$index]);
            }

            if (!empty($this->_orderby)) return ' ORDER BY '. implode( ', ', $this->_orderby ) ."\n" ;
        }
        return null;
    }

    /**
     * Method to build the query GROUP BY segment
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    protected function buildQueryGroupBy()
    {
        $this->_groupby = array_unique($this->_groupby);
        if (count($this->_groupby)) {
            foreach ($this->_groupby as $index => $groupby) {
                $groupby = trim($groupby);
                if (empty($groupby)) unset($this->_groupby[$index]);
            }

            if (!empty($this->_groupby)) return ' GROUP BY '. implode( ', ', $this->_groupby ) ."\n" ;
        }
        return null;
    }

    /**
     * Method to build the query WHERE segment
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    protected function buildQueryWhere()
    {
        // Automatically add the WHERE-statement for a single ID-based query
        if ($this->isSingular()) {
            $this->addWhere('`{tableAlias}`.`{primary}`='.(int)$this->_id);
        }

        // Automatically add a WHERE-statement if the state-filter is used
        $state = $this->getFilter('state');
        if ($state == 'U' || $state == 'P') {
            $state = ($state == 'U') ? 0 : 1;
            $stateField = $this->_tbl->getStateField();
            if (!empty($stateField)) $this->addWhere('`'.$this->_tbl_alias.'`.`'.$stateField.'` = '.$state);
        }

        // Automatically add a WHERE-statement if only published items should appear on the frontend
        if ($this->application->isSite()) {
            $stateField = $this->_tbl->getStateField();
            if (!empty($stateField)) $this->addWhere($this->_tbl_alias.'.'.$stateField.' = 1');
        }

        // Automatically add a WHERE-statement if the search-filter is used
        $search = $this->getFilter('search');
        if (!empty($this->_search) && !empty($search)) {
            $where_search = array();
            foreach ($this->_search as $column) {
                $where_search[] = "`".$this->_tbl_alias."`.`$column` LIKE '%$search%'";
            }
        }

        if (!empty($where_search)) {
            $this->_where[] = '('.implode(' OR ', $where_search).')';
        }

        if ( count( $this->_where ) ) {
            return ' WHERE '. implode( ' AND ', $this->_where ) ."\n" ;
        } else {
            return '';
        }
    }

    /**
     * Method to build an extra query segment
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    protected function buildQueryExtra()
    {
        if ( count( $this->_extra ) > 0 ) {
            return ' '. implode( ' ', $this->_extra ) ."\n" ;
        } else {
            return '';
        }
    }

    /**
     * Method to add a new ORDER BY argument
     *
     * @access protected
     * @subpackage Yireo
     * @param string $orderby
     * @return null
     */
    public function addOrderby($orderby = null)
    {
        $orderby = trim($orderby);
        if (empty($orderby)) return;
        if ($orderby == '{tableAlias}.') return;

        if (is_string($orderby) && !isset($this->_orderby[$orderby])) {
            if (strstr($orderby, '.') == false && preg_match('/^RAND/', $orderby) == false) $orderby = '{tableAlias}.'.$orderby;
            if (strstr($orderby, 'accesslevel')) $orderby = str_replace('{tableAlias}.', '', $orderby);
            $this->_orderby[] = $orderby;
        }
    }

    /**
     * Method to add a new GROUP BY argument
     *
     * @access protected
     * @subpackage Yireo
     * @param string $groupby
     * @return null
     */
    public function addGroupby($groupby = null)
    {
        $groupby = trim($groupby);
        if (empty($groupby)) return;
        if ($groupby == '{tableAlias}.') return;

        if (is_string($groupby) && !isset($this->_groupby[$groupby])) {
            if (strstr($groupby, '.') == false) $groupby = '{tableAlias}.'.$groupby;
            $this->_groupby[] = $groupby;
        }
    }

    /**
     * Method to add a new WHERE argument
     *
     * @access protected
     * @subpackage Yireo
     * @param string $where
     * @return null
     */
    public function addWhere($where = null)
    {
        if ($this->_allow_filter == false) return null;
        if (is_string($where) && !in_array($where, $this->_where)) {
            $this->_where[] = $where;
        }
    }

    /**
     * Method to add an extra query argument
     *
     * @access protected
     * @subpackage Yireo
     * @param string $extra
     * @return null
     */
    public function addExtra($extra = null)
    {
        if (is_string($extra)) {
            $this->_extra[] = $extra;
        }
    }

    /**
     * Method to get the current primary key
     *
     * @access public 
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->_tbl_key;
    }

    /**
     * Method to get the ordering query
     *
     * @access public 
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function getOrderingQuery()
    {
        if ($this->_orderby_default == 'ordering') {
            $query = 'SELECT `ordering` AS `value`, `'.$this->_orderby_title.'` AS `text`'
                . ' FROM `'.$this->_tbl_name.'`'
                . ' ORDER BY `ordering`';
            return $query;

        } else if ($this->_orderby_default == 'lft') {
            $query = 'SELECT `lft` AS `value`, `'.$this->_orderby_title.'` AS `text`'
                . ' FROM `'.$this->_tbl_name.'`'
                . ' ORDER BY `lft`';
            return $query;
        }

        return null;
    }

    /**
     * Method to get empty fields
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return array
     */
    protected function getEmptyFields()
    {
        $data = array(
            'published' => 1,
            'publish_up' => null,
            'publish_down' => null,
            'state' => 1,
            'access' => 1,
            'ordering' => 0,
            'lft' => 0,
            'rgt' => 0,
        );
        return $data;
    }

    /**
     * Method to initialise the data
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    protected function getEmpty()
    {
        // Define the fields to initialize
        $data = $this->getEmptyFields();

        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {

            if ($this->isPlural()) {
                $this->_data = array();
        
            } else {
                $this->_data = (object)$this->_tbl->getProperties();
                foreach ($data as $name => $value) {
                    $this->_data->$name = $value;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Method to get a XML-based form
     *
     * @access public
     * @subpackage Yireo
     * @param array $data
     * @param bool $loadData
     * @return mixed
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Do not continue if this is not a backend singular view
        if ($this->application->isAdmin() == false || $this->isSingular() == false) {
            return false;
        }

        // Read the form from XML
        $xmlFile = JPATH_ADMINISTRATOR.'/components/'.$this->_option.'/models/'.$this->_entity.'.xml';

        if (!file_exists($xmlFile)) {
            return false;
        }

        // Construct the form-object
        jimport('joomla.form.form');
        $form = JForm::getInstance('item', $xmlFile);
        if (empty($form)) {
            return false;
        }

        // Bind the data
        $data = $this->getData();
        $form->bind(array('item' => $data));

        // Insert the params-data if set
        if (!empty($data->params)) {
            $params = $data->params;
            if (is_string($params)) $params = YireoHelper::toRegistry($params)->toArray();
            $form->bind(array('params' => $params));
        }

        return $form;
    }

    /**
     * Check whether this record can be edited
     *
     * @access protected
     * @subpackage Yireo
     * @param array $data
     * @return bool
     */
    protected function canEditState($data)
    {
        // Check the permissions for this edit.state action
        if ($this->getId() > 0) {
            return $this->user->authorise('core.edit.state', $this->_option.'.'.$this->_entity.'.'.(int)$this->getId());
        } else {
            return $this->user->authorise('core.edit.state', $this->_option);
        }
    }
    
    /**
     * Method to determine whether this model is singular or not
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    public function isSingular()
    {
        if (isset($this->_single) && (bool)$this->_single == true) {
            return true;
        }
        return false;
    }

    /**
     * Method to determine whether this model is plural or not
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    public function isPlural()
    {
        if ($this->isSingular()) {
            return false;
        }
        return true;
    }

    /**
     * Method to override the parameters
     *
     * @access public
     * @subpackage Yireo
     * @param mixed
     * @return null
     */
    public function setParams($params = null)
    {
        if (!empty($params)) {
            $this->params = $params;
        }
    }

    /**
     * Override the default method to make sure we always get the right table
     *
     * @access public
     * @subpackage Yireo
     * @param string $name
     * @param string $prefix
     * @param array $options
     * @return mixed
     */
    public function getTable($name = '', $prefix = 'Table', $options = array())
    {
        if (empty($name)) $name = $this->_tbl_alias;
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the default ORDER BY value
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function getOrderByDefault()
    {
        return $this->_orderby_default;
    }

    /**
     * Method to get a debug-message of the latest query
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function getDbDebug()
    {
        return '<pre>'.str_replace('#__', $this->_db->getPrefix(), $this->_db->getQuery()).'</pre>';
    }

    /**
     * Method to temporarily store an object in the current session
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function saveTmpSession($data)
    {
        $session = JFactory::getSession();
        $session->set($this->_option_id, $data);
    }

    /**
     * Method to temporarily store an object in the current session
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function loadTmpSession()
    {
        if ($this->isSingular()) {
            $session = JFactory::getSession();
            $data = $session->get($this->_option_id);
            if (!empty($data)) {
                foreach ($data as $name => $value) {
                    if (!empty($value)) {
                        $this->_data->$name = $value;
                    }
                }
            }
        }
        return;
    }

    /**
     * Method to temporarily store an object in the current session
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function resetTmpSession()
    {
        $session = JFactory::getSession();
        $session->clear($this->_option_id);
    }

    /**
     * Method to reset all filters
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return string
     */
    public function resetFilters()
    {
        $this->_search = null;
        $this->_where = array();
        $this->_orderby = array();
        $this->setState('limitstart', 0);
        $this->setState('limit', 0);
    }

    /**
     * Method to check if any errors are set
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return boolean
     */
    public function hasErrors()
    {
        $errors = $this->getErrors();
        if (!empty($errors)) {
            return true;
        }
        return false;
    }

    /**
     * Method to set whether filtering is allowed
     *
     * @access public
     * @subpackage Yireo
     * @param boolean
     * @return null
     */
    public function setAllowFilter($bool)
    {
        $this->_allow_filter = $bool;
    }

    /**
     * Method to set whether the query should use LIMIT or not
     *
     * @access public
     * @subpackage Yireo
     * @param boolean
     * @return null
     */
    public function setLimitQuery($bool)
    {
        $this->_limit_query = $bool;
    }

    /**
     * Method to fetch database-results
     *
     * @access public
     * @subpackage Yireo
     * @param string $query
     * @param string $type: object|objectList|result
     * @return null
     */
    public function getDbResult($query, $type = 'object')
    {
        if($this->_cache == true) {
            $cache = JFactory::getCache();
            $rs = $cache->call(array($this, '_getDbResult'), $query, $type);
        } else {
            $rs = $this->_getDbResult($query, $type);
        }

        return $rs;
    }

    /**
     * Method to fetch database-results
     *
     * @access public
     * @subpackage Yireo
     * @param string $query
     * @param string $type: object|objectList|result
     * @return null
     */
    public function _getDbResult($query, $type = 'object')
    {
        // Set the query in the database-object
        $this->_db->setQuery($query);

        // Print the query if debugging is enabled
        if (isset($this->_debug) && $this->_debug == true) {
            JError::raiseNotice( 'Query', $this->getDbDebug());
        }

        // Fetch the database-result
        if($type == 'objectList') {
            $rs = $this->_db->loadObjectList();
        } elseif($type == 'result') {
            $rs = $this->_db->loadResult();
        } else {
            $rs = $this->_db->loadObject();
        }

        // Print an error when an error occurs
        if (isset($this->_debug) && $this->_debug == true && $this->_db->getErrorMsg()) {
            JError::raiseWarning( 'DB error', $this->_db->getErrorMsg());
        }

        // Return the result
        return $rs;
    }

    /**
     * Method to return the meta-data of this model
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return array
     */
    protected function getMetadata()
    {
        return array(
            'table' => $this->_entity,
        );
    }

    /**
     * Method to determine the component-name
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return string
     */
    protected function getOption()
    {
        $classParts = explode('Model', get_class($this));
        $comPart = (!empty($classParts[0])) ? strtolower($classParts[0]) : null;
        $option = (!empty($comPart) && $comPart != 'yireo') ? 'com_'.$comPart : JRequest::getCmd('option');
        return $option;
    }
}
