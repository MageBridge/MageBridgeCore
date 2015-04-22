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

/**
 * Yireo Common Model 
 * Parent class for models that need additional features without JTable functionality
 *
 * @package Yireo
 */
class YireoCommonModel extends YireoAbstractModel
{
    /*
     * Boolean to skip table-detection
     *
     * @protected int
     */
    protected $_skip_table = true;

    /*
     * Boolean to allow forms in the frontend
     *
     * @protected int
     */
    protected $_frontend_form = false;

    /**
     * Name of the XML-file containing the JForm definitions (if any)
     *
     * @protected int
     */
    protected $_form_name = null;

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
     * Flag to automatically set the table class prefix
     *
     * @protected boolean
     */
    protected $_tbl_prefix_auto = false;

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
     * Constructor
     *
     * @access public
     * @subpackage Yireo
     * @param string $tableAlias
     * @return null
     */
    public function __construct($tableAlias = null)
    {
        // Import use full variables from JFactory
        $this->db = JFactory::getDBO();
        $this->app = JFactory::getApplication();
        $this->application = $this->app;
        $this->jinput = $this->app->input;
        $this->user = JFactory::getUser();

        // Create the option-namespace
        $classParts = explode('Model', get_class($this));
        $this->_view = (!empty($classParts[1])) ? strtolower($classParts[1]) : $this->jinput->getCmd('view');
        $this->_option = $this->getOption();
        $this->_option_id = $this->_option.'_'.$this->_view.'_';
        if ($this->app->isSite()) $this->_option_id .= $this->jinput->getInt('Itemid').'_';

        $this->_component = preg_replace('/^com_/', '', $this->_option);
        $this->_component = preg_replace('/[^A-Z0-9_]/i', '', $this->_component);
        $this->_component = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->_component)));

        // Call the parent constructor
        $rt = parent::__construct();
        return $rt;
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
        return array();
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
        // Do not continue if this is not the right backend
        if ($this->app->isAdmin() == false && $this->_frontend_form == false) {
            return false;
        }

        // Do not continue if this is not a singular view
        if (method_exists($this, 'isSingular') && $this->isSingular() == false) {
            return false;
        }

        // Read the form from XML
        $xmlFile = JPATH_ADMINISTRATOR.'/components/'.$this->_option.'/models/'.$this->_form_name.'.xml';
        if (!file_exists($xmlFile)) {
            $xmlFile = JPATH_SITE.'/components/'.$this->_option.'/models/'.$this->_form_name.'.xml';
        }

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

    /* 
     * Helper method to override the name of the form
     */
    public function setFormName($form_name)
    {
        $this->_form_name = $form_name;
    }

    /**
     * Override the default method to allow for skipping table creation
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
        if ($this->_skip_table == true) return null;
        if (empty($name)) $name = $this->_tbl_alias;
        if (!empty($this->_tbl_prefix)) $prefix = $this->_tbl_prefix;
        return parent::getTable($name, $prefix, $options);
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
        $comPart = (!empty($classParts[0])) ? $classParts[0] : null;
        $comPart = preg_replace('/([A-Z])/', '_\\1', $comPart);
        $comPart = strtolower(preg_replace('/^_/', '', $comPart));
        $option = (!empty($comPart) && $comPart != 'yireo') ? 'com_'.$comPart : $this->jinput->getCmd('option');
        return $option;
    }
}
