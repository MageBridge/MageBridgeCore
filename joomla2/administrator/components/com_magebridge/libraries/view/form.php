<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.5.1
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once dirname(dirname(__FILE__)).'/loader.php';

// Import the needed libraries
jimport('joomla.filter.output');

/**
 * Form View class
 *
 * @package Yireo
 */
class YireoViewForm extends YireoView
{
    /*
     * Identifier of the library-view
     */
    protected $_viewParent = 'form';

    /*
     * Flag to determine whether this view is a single-view
     */
    protected $_single = true;

    /*
     * Array of all the form-fields
     */
    protected $_fields = array();

    /*
     * Editor-field
     */
    protected $_editor_field = null;

    /*
     * Main constructor method
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function __construct()
    {
        // Do not load the toolbar automatically
        //$this->loadToolbar = false; // @todo: Is this already configurable?

        // Template-paths
        $this->templatePaths[] = dirname(__FILE__).'/form';

        // Call the parent constructor
        return parent::__construct();
    }

    /*
     * Main display method
     *
     * @access public
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Hide the menu
        JRequest::setVar('hidemainmenu', 1);
    
        // Initialize tooltips
        JHTML::_('behavior.tooltip');

        // Automatically fetch the item and assign it to the layout
        $this->fetchItem();

        // Automatically load the parameters form
        $this->loadParametersForm();

        parent::display($tpl);
    }

    /*
     * Load common lists
     *
     * @access public
     * @param null
     * @return null
     */
    public function loadLists()
    {
        // Get the model and table
        $model = $this->getModel();
        $table = $model->getTable();

        // Assign the published-list
        if($table->hasField('published') && isset($this->item->published)) {
            $this->lists['published'] = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->item->published );
        } else {
            $this->lists['published'] = null;
        }

        // Assign the access-list 
        // @todo: Does this work under Joomla! 2.5+
        if($table->hasField('published') && isset($this->item->access)) {
            $this->lists['access'] = JHTML::_('list.accesslevel', $this->item);
        } else {
            $this->lists['access'] = null;
        }

        $ordering = $this->model->getOrderByDefault();
        if(!empty($ordering) && $ordering == 'ordering') {
            // @todo: This only works when orderby-field is "ordering"
            $this->lists['ordering'] = JHTML::_('list.specificordering',  $this->item, $this->model->getId(), $this->model->getOrderingQuery());
        } else {
            $this->lists['ordering'] = null;
        }
    }

    /*
     * Load common fields
     *
     * @access public
     * @param null
     * @return null
     */
    public function loadFields()
    {
        // Get the model and table
        $model = $this->getModel();
        $table = $model->getTable();

        // Construct common text-fields
        $fields = array('title', 'name', 'label', 'alias');
        foreach($fields as $field) {
            if($table->hasField($field) == true && isset($this->fields[$field]) == false) {
                $this->fields[$field] = array('name' => $field, 'type' => 'text', 'value' => $this->item->$field);
            }
        }

        // Construct custom lists
        $fields = array('category_id', 'parent_id', 'published', 'ordering', 'access');
        foreach($fields as $field) {
            if(isset($this->lists[$field])) {
                $this->fields[$field] = array('name' => $field, 'custom' => $this->lists[$field]);
            }
        }
    }

    /*
     * Load the parameters form
     *
     * @access public
     * @param null
     * @return null
     */
    public function loadParametersForm()
    {
        // Initialize parameters
        $view = JRequest::getCmd('view');
        $file = JPATH_COMPONENT.'/models/'.$view.'.xml';
        if(file_exists($file) == false) {
            return false;
        }

        if(YireoHelper::isJoomla15()) {
            $params = YireoHelper::toRegistry($this->item->params, $file);
            $this->assignRef('params', $params);
        } else {
            $form = JForm::getInstance('params', $file);
            $this->assignRef('paramsForm', $form);
        }
        return true;
    }


    /*
     * Return the editor-field
     *
     * @access public
     * @param null
     * @return string
     */
    public function getEditorField()
    {
        return $this->_editor_field;
    }
}
