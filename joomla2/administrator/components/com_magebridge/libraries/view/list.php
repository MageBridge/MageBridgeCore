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

/**
 * List View class
 *
 * @package Yireo
 */
class YireoViewList extends YireoView
{
    /*
     * Identifier of the library-view
     */
    protected $_viewParent = 'list';

    /*
     * Flag to determine whether to load edit/copy/new buttons
     */
    protected $loadToolbarEdit = true;

    /*
     * Flag to determine whether to load delete buttons
     */
    protected $loadToolbarDelete = true;

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
        $this->loadToolbar = false;

        // Call the parent constructor
        return parent::__construct();
    }

    /*
     * Main display method
     *
     * @access public
     * @subpackage Yireo
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Extra behaviors
        JHTML::_('behavior.tooltip');
        JHTML::_('behavior.modal');

        // Automatically fetch items, total and pagination - and assign them to the template
        $this->fetchItems();

        // Load model and table
        $model = $this->getModel();
        $primaryKey = $model->getPrimaryKey();
        $table = $model->getTable();

        // Parse the items a bit more
        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {

                // Determine the primary key
                $item->id = (isset($item->$primaryKey)) ? $item->$primaryKey: null;

                // Set the various links
                if(empty($item->edit_link)) {
                    $item->edit_link = JRoute::_('index.php?option='.$this->_option.'&view='.$this->_view.'&task=edit&cid[]='. $item->id);
                }

                // Re-insert the item
                $this->items[$index] = $item;
            }
        }

        // Initialize the toolbar
        if ($table->getStateField() != '') {
            JToolBarHelper::publishList();
            JToolBarHelper::unpublishList();
        }

        // Add the delete-button
        if ($this->loadToolbarDelete == true) {
            JToolBarHelper::deleteList();
        }

        // Load the toolbar edit-buttons
        if ($this->loadToolbarEdit == true) {
            JToolBarHelper::editList();
            JToolBarHelper::custom( 'copy', 'copy.png', 'copy.png', 'LIB_YIREO_VIEW_TOOLBAR_COPY', true, true);
            JToolBarHelper::addNew();
        }

        // Insert extra fields
        $fields = array();
        $fields['primary_field'] = $primaryKey;
        $fields['ordering_field'] = $model->getOrderByDefault();
        $fields['state_field'] = $table->getStateField();
        $this->assignRef('fields', $fields);

        // Add extra variables
        $this->assignRef('option', $this->_option);
        $this->assignRef('view', $this->_view);

        parent::display($tpl);
    }

    /*
     * Method to allow toggling a certain field
     *
     * @access public
     * @subpackage Yireo
     * @param string $name
     * @param string $value
     * @param boolean $ajax
     * @param int $id
     * @return null
     */
    public function toggle($name, $value, $ajax = false, $id = 0)
    {
        if ($value == 1 || !empty($value)) {
            $img = 'tick.png';
        } else {
            $img = 'publish_x.png';
        }

        if ($ajax == false) {
            return $this->getImageTag($img);
        } else {
            $token = (method_exists('JSession', 'getFormToken')) ? JSession::getFormToken() : JUtility::getToken();
            $url = JRoute::_('index.php?option='.$this->_option.'&view='.$this->_view.'&task=toggle&id='.$id.'&name='.$name.'&value='.$value.'&'.$token.'=1');
            return '<a href="'.$url.'">'.$this->getImageTag($img).'</a>';
        }
    }

    /*
     * Method to return the checkedout grid-box
     *
     * @access public
     * @subpackage Yireo
     * @param object $item
     * @param int $i
     * @return string
     */
    public function checkedout($item, $i)
    {
        if (YireoHelper::isJoomla15()) {
            $checked = JHTML::_('grid.checkedout', $item, $i);
        } else {
            $user = JFactory::getUser();
            if(!isset($item->editor)) $item->editor = $user->get('id');
            if(!isset($item->checked_out)) $item->checked_out = 0;
            if(!isset($item->checked_out_time)) $item->checked_out_time = 0;

            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
            $checked = JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, '', $canCheckin);
        }

        return $checked;
    }

    /*
     * Method to return the checkbox to do something
     *
     * @access public
     * @subpackage Yireo
     * @param object $item
     * @param int $i
     * @return string
     */
    public function checkbox($item, $i)
    {
        if (YireoHelper::isJoomla15()) {
            $checkbox = JHTML::_('grid.checkedout', $item, $i);
        } else {
            $checkbox = JHtml::_('grid.id', $i, $item->id);
        }

        return $checkbox;
    }

    /*
     * Helper method to return the published grid-box
     *
     * @access public
     * @subpackage Yireo
     * @param object $item
     * @param int $i
     * @return string
     */
    public function published($item, $i, $model = null)
    {
        $published = null;

        if (YireoHelper::isJoomla15()) {
            $published = JHTML::_('grid.published', $item, $i );
        } else {

            // Import variables
            $user = JFactory::getUser();
            $table = $this->getModel()->getTable();

            // Create dummy publish_up and publish_down variables if not set
            if(!isset($item->publish_up)) $item->publish_up = null;
            if(!isset($item->publish_down)) $item->publish_down = null;

            // Fetch the state-field
            $stateField = $table->getStateField();
            if(!empty($stateField)) {
                $canChange = $user->authorise('core.edit.state', $this->_option.'.item.'.$item->id);
                $published = JHtml::_('jgrid.published', $item->$stateField, $i, '', $canChange, 'cb', $item->publish_up, $item->publish_down);
            }
        }

        return $published;
    }

    /*
     * Method to return whether an item is checked out or not
     *
     * @access public
     * @subpackage Yireo
     * @param 
     * @return array
     */
    public function isCheckedOut($item = null)
    {
        // If this item has no checked_out field, it's an easy choice
        if(isset($item->checked_out) == false) return false;

        // Import variables
        $user = JFactory::getUser();
        $table = $this->getModel()->getTable();

        if(YireoHelper::isJoomla15()) {
            return $table->isCheckedOut($user->get('id'), $item->checked_out);
        } else {
            return $table->isCheckedOut($user->get('id'), $item->checked_out);
        }
    }
}
