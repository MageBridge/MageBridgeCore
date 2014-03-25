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
     * Default task
     */
    protected $_task = null;

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
        // Call the parent constructor
        $rt = parent::__construct();

        // Detect the editor field
        if (empty($this->_editor_field) && !empty($this->_table)) {
            if($this->_table->hasField('body')) $this->_editor_field = 'body';
            if($this->_table->hasField('description')) $this->_editor_field = 'description';
            if($this->_table->hasField('text')) $this->_editor_field = 'text';
        }

        return $rt;
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

        if ($this->prepare_display == true) $this->prepareDisplay();
        parent::display($tpl);
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
