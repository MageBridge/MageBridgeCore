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

// Include library dependencies
jimport('joomla.filter.input');
jimport('joomla.filter.output');

// Load the helper
require_once dirname(__FILE__).'/loader.php';

/**
 * Generic Table class
 *
 * @static
 * @package    Yireo
 */
class YireoTable extends JTable
{
    /**
     * List of fields to include in the Table-instance
     *
     * @protected array
     */
    protected $_fields = array();

    /**
     * List of required fields that can not be left empty 
     *
     * @protected array
     */
    protected $_required = array();

    /**
     * List of fields that can not have duplicates in the existing table
     *
     * @protected array
     */
    protected $_noduplicate = array();

    /**
     * Flag to enable debugging
     *
     * @access public
     */
    protected $_debug = false;

    /**
     * Constructor
     *
     * @access public
     * @subpackage Yireo
     * @param string $table_name
     * @param string $primary_key
     * @param JDatabase $db
     * @return null
     */
    public function __construct($table_name, $primary_key, $db) 
    {
        // Determine the table name
        $table_namespace = preg_replace( '/^com_/', '', JRequest::getCmd('option'));
        if (!empty($table_name)) {
            if (!strstr($table_name, '#__')) {
                $table_name = $table_namespace.'_'.$table_name;
            }
        } else {
            $table_name = $table_namespace;
        }

        // Call the constructor to finish construction
        parent::__construct($table_name, $primary_key, $db);

        // Initialize the fields based on an array
        $fields = $this->getDatabaseFields();
        if (!empty($fields )) {
            foreach ($fields as $field) {
                if (!empty($this->_defaults[$field])) {
                    $this->$field = $this->_defaults[$field];
                } else {
                    $this->$field = null;
                }
            }
        }
    }

    /**
     * Bind method
     *
     * @access public
     * @subpackage Yireo
     * @param array $array
     * @param string $ignore
     * @return null
     * @see JTable:bind
     */
    public function bind($array, $ignore = '')
    {
        // Set cid[] as primary key
        if (key_exists( 'cid', $array )) {
            $cid = (int)$array['cid'][0];
            $primary_key = $this->getKeyName();
            $array[$primary_key] = $cid;
        }

        // Remove fields that do not exist in the database-table
        $fields = $this->getDatabaseFields();
        foreach ($array as $name => $value) {
            if (!in_array($name, $fields)) {
                unset($array[$name]);
            }
        }

        // Add fields that are defined in this table by default, but are not set to bound
        if (!empty($this->_defaults)) {
            foreach ($this->_defaults as $defaultName => $defaultValue) {
                if (!isset($array[$defaultName])) {
                    $array[$defaultName] = $defaultValue;
                }
            }
        }

        // Generate an alias if it is empty, but if a title exists
        if (empty($array['alias'])) {
            if (!empty($array['name'])) $array['alias'] = JFilterOutput::stringURLSafe($array['name']);
            if (!empty($array['title'])) $array['alias'] = JFilterOutput::stringURLSafe($array['title']);
        }

        // Convert the parameter array to a flat string
        if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overloaded check method to ensure data integrity
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    public function check()
    {
        // Check the required fields
        if (!empty( $this->_required )) {
            foreach ( $this->_required as $r ) {
                if ( !$this->_checkRequired( $r )) {
                    return false;
                }
            }
        }

        // Check the fields for duplicates
        if (!empty( $this->_noduplicate )) {
            foreach ( $this->_noduplicate as $d ) {
                if ( !$this->_checkDuplicate( $d )) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Overloaded store method to debug query-failures
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return bool
     */
	public function store($updateNulls = false)
    {
        $result = parent::store($updateNulls);
        if ( $this->_debug == true ) {
            echo "Query: " . $this->_db->getQuery() ;
            echo "Error: " . $this->_db->getErrorMsg() ;
            exit;
        }
        return $result;
    }

    /**
     * Helper-method to check if a required value is set or not
     *
     * @access public
     * @subpackage Yireo
     * @param string $field
     * @return bool
     */
    protected function _checkRequired($field)
    {
        if (!isset($this->$field) || $this->$field == null || trim($this->$field) == '') {
            $this->_error = JText::sprintf('LIB_YIREO_TABLE_FIELD_VALUE_REQUIRED', $field);
            return false;
        }
        return true;
    }

    /**
     * Helper-method to check for duplicate values in the table
     *
     * @access public
     * @subpackage Yireo
     * @param string $field
     * @return bool
     */
    protected function _checkDuplicate($field)
    {
        if ($this->$field != null ) {
            $table = $this->getTableName();
            $primary_key = $this->getKeyName();
            $query = "SELECT `$primary_key` FROM `$table` WHERE `$field`=".$this->_db->Quote($this->$field);
            $this->_db->setQuery($query);

            $xid = intval($this->_db->loadResult());
            if ($xid && $xid != intval($this->$primary_key)) {
                $fieldLabel = JText::_('LIB_YIREO_TABLE_FIELDNAME_'.$field);
                $this->_error = JText::sprintf('LIB_YIREO_TABLE_FIELD_VALUE_DUPLICATE', $fieldLabel, $this->$field);
                return false;
            }
        }
        return true;
    }

    /**
     * Helper-method to get the latest insert ID
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return int
     */
    public function getLastInsertId()
    {
        $primary_key = $this->getKeyName();
        if($this->$primary_key > 0) return $this->$primary_key;
        return $this->_db->insertid();
    }

    /**
     * Helper-method to get the error-message
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return int
     */
    public function getErrorMsg()
    {
        return $this->_error;
    }

    /**
     * Helper-method to get all fields from this table
     *
     * @access public
     * @param null
     * @return array
     */
    public function getDatabaseFields()
    {
        $tableName = $this->getTableName();
        static $fields = array();
        if (!isset($fields[$tableName]) || !is_array($fields[$tableName])) {
            $cache = JFactory::getCache();
            $cache->setCaching(1);
            $fields[$tableName] = $cache->call(array('YireoTable', 'getCachedDatabaseFields'), $tableName);
        }
        return $fields[$tableName];
    }

    /**     
     * Helper-method to get all fields from this table
     *  
     * @access public
     * @param null
     * @return array
     */     
    static public function getCachedDatabaseFields($tableName)
    {   
        $db = JFactory::getDBO();
        $db->setQuery('SHOW FIELDS FROM `'.$tableName.'`');
        $fields = (method_exists($db, 'loadColumn')) ? $db->loadColumn() : $db->loadResultArray();
        return $fields;
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields)
     *
     * @access public
     * @param null
     * @return array
     */
    public function hasField($check)
    {
        $fields = $this->getDatabaseFields();
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (is_string($check) && $field == $check) {
                    return $field;
                } else if (is_array($check) && in_array($field, $check)) {
                    return $field;
                }
            }
        }
        
        return false;
    }

    /**
     * Helper-method to get all fields from this table
     *
     * @access public
     * @param null
     * @return array
     */
    public function hasAssetId()
    {
        return (bool)$this->hasField('asset_id');
    }

    /**
     * Helper-method to get the state-field
     *
     * @access public
     * @param null
     * @return array
     */
    public function getStateField()
    {
        if ($this->hasField('state')) {
            return 'state';
        } else if ($this->hasField('published')) {
            return 'published';
        }
        return null;
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields)
     *
     * @access public
     * @param null
     * @return array
     */
    public function getDefaultOrderBy()
    {
        if ($this->hasField('ordering')) return 'ordering';
        if ($this->hasField('lft')) return 'lft';
        return null;
    }

    /**
     * Helper-method to turn an array into a CSV-list
     *
     * @access public
     * @param null
     * @return array
     */
    public function arrayToString($array, $seperator = ',')
    {
        if(!empty($array) && is_array($array)) {
            foreach($array as $index => $value) {
                $value = trim($value);
                if(empty($value)) unset($array[$index]);
            }
            $string = implode($seperator, $array);
            return $string;
        }
        return $array;
    }
}
