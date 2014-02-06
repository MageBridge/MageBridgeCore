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
 * Yireo Abstract Controller
 *
 * @package Yireo
 */
if(YireoHelper::isJoomla25() || YireoHelper::isJoomla15()) {
    jimport( 'joomla.application.component.controller' );
    class YireoAbstractController extends JController {}
} else {
    class YireoAbstractController extends JControllerLegacy {}
}

/**
 * Yireo Common Controller
 *
 * @package Yireo
 */
class YireoCommonController extends YireoAbstractController
{
    /**
     * Value of the last message
     *
     * @protected string
     */
    protected $msg = '';

    /**
     * Type of the last message
     *
     * @protected string
     * @values error|notice|message
     */
    protected $msg_type = '';

    /**
     * Constructor
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function __construct()
    {
        // Define variables
        $this->_application = JFactory::getApplication();

        // Add extra model-paths
        $option = JRequest::getCmd('option');
        if ($this->_application->isSite()) {
            $this->addModelPath(JPATH_ADMINISTRATOR.'/components/'.$option.'/models');
            $this->addModelPath(JPATH_SITE.'/components/'.$option.'/models');
        } else {
            $this->addModelPath(JPATH_ADMINISTRATOR.'/components/'.$option.'/models');
        }

        // Load additional language-files
        YireoHelper::loadLanguageFile();

        // Call the parent constructor
        parent::__construct();
    }
}

/**
 * Yireo Controller
 *
 * @package Yireo
 */
class YireoController extends YireoCommonController
{
    /**
     * Value of the default View to use
     *
     * @protected string
     */
    protected $_default_view = '';

    /**
     * Value of the current model
     *
     * @protected object
     */
    protected $_model = null;

    /**
     * Boolean to allow or disallow frontend editing
     *
     * @protected bool
     */
    protected $_frontend_edit = false;

    /**
     * List of allowed tasks
     *
     * @protected array
     */
    protected $_allow_tasks = array(
        'display',
    );

    /**
     * List of POST-values that should be allowed to contain raw content
     *
     * @protected array
     */
    protected $_allow_raw = array(
        'description',
        'text',
        'comment',
    );

    /**
     * List of relations between Views
     *
     * @protected int
     */
    protected $_relations = array(
        'list' => 'lists',
        'category' => 'categories',
        'item' => 'items',
        'status' => 'statuses',
    );

    /**
     * Constructor
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct();

        // If no task has been set, try the default
        if (JRequest::getCmd('view') == '' && !empty($this->_default_view)) {
            JRequest::setVar('view', $this->_default_view);
        }

        // Register extra tasks
        $this->registerTask('new', 'add');
        $this->registerTask('change', 'edit');

        // Allow or disallow frontend editing
        if ($this->_application->isSite() && in_array(JRequest::getCmd('task', 'display'), $this->_allow_tasks) == false) {
            JError::raiseError(500, JText::_('LIB_YIREO_CONTROLLER_ILLEGAL_REQUEST'));
        }

        // Neat trick to automatically remove obsolete files
        if (JRequest::getCmd('view') == $this->_default_view) {
            YireoHelperInstall::remove();
        }
    }

    /**
     * Display the current page
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Set the layout properly
        if (in_array(JRequest::getVar('format'), array('pdf', 'print'))) {
            JRequest::setVar('layout', 'print');
        }

        parent::display($cachable, $urlparams);
    }


    /**
     * Handle the task 'add'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function add()
    {
        JRequest::setVar( 'edit', false );
        $this->setEditForm() ;
    }


    /**
     * Handle the task 'edit'
     * 
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function edit()
    {
        JRequest::setVar( 'edit', true );

        $model = $this->_loadModel();
        $model->checkout();

        $this->setEditForm() ;
    }


    /**
     * Handle the task 'copy'
     * 
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function copy()
    {
        JRequest::setVar( 'edit', false );
        $this->setEditForm() ;
    }


    /**
     * Handle the task 'store'
     * 
     * @access public
     * @subpackage Yireo
     * @param array $post
     * @return null
     */
    public function store($post = null)
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Fetch the POST-data
        if(empty($post)) $post = JRequest::get('post');
        $post['id'] = $this->getId();
        $this->id = $post['id'] ;

        // Make sure fields that are configured as "raw" are loaded correspondingly
        if (!empty($this->_allow_raw)) {
            foreach ( $this->_allow_raw as $raw ) {
                $post[$raw] = JRequest::getVar($raw, '', 'post', 'string', JREQUEST_ALLOWRAW);
            }
        }

        // Check for an alias
        if (in_array( 'alias', $post )) {
            if (empty($post['alias'])) {
                $alias = JRequest::getString('title', '', 'post' );
            }
            $alias = strtolower( JFilterOutput::stringURLSafe( $alias )) ;
            $post['alias'] = $alias ;
        }

        // Get the model
        $model = $this->_loadModel();

        // Store these data with the model
        if ($model->store($post)) {
            $id = $model->getId();
            if($id > 0) $this->id = $id;
            $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_SAVED', JRequest::getCmd('view'));

        // If this fails, set the error
        } else {
            $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_NOT_SAVED', JRequest::getCmd('view'));
            $error = $model->getError();
            if (!empty($error)) $this->msg .= ': '.$error;
            $this->msg_type = 'error';
        }

        // Checkin the model, so it can be edited
        $model->checkin();
        return $this->id;
    }


    /**
     * Handle the task 'save'
     * 
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function save()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Store the data
        $this->store() ;

        // Determine the state of the model
        $model = $this->_loadModel();
        if ($model->hasErrors() == false) {

            // Redirect back to the overview
            $plural = $this->getPluralName( JRequest::getVar('view') );
            $this->doRedirect($plural);

        } else {
            // Redirect back to the form-page
            $this->doRedirect( JRequest::getVar('view'), array( 'id' => $this->getId(), 'task' => 'edit'));
        }
    }


    /**
     * Handle the task 'apply'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function apply()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Store the data
        $this->store() ;

        // Redirect back to the form-page
        $apply_url = JRequest::getVar('apply_url');
        if(!empty($apply_url)) {
            return $this->setRedirect($apply_url, $this->msg, $this->msg_type);
        }

        $this->doRedirect( JRequest::getVar('view'), array( 'id' => $this->getId(), 'task' => 'edit'));
    }


    /**
     * Handle the task 'savenew'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function savenew()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Store the data
        $this->store() ;

        // Redirect to the form-page
        $this->doRedirect( JRequest::getVar('view'), array( 'id' => 0, 'task' => 'add'));
    }

    /**
     * Handle the task 'saveandcopy'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function saveandcopy()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Store these data
        $this->store();

        // Remove the identifier from whereever
        JRequest::setVar('id', 0);
        JRequest::setVar('cid[]', 0);
        JRequest::setVar('cid', null);
        $this->setId(0);

        // Store these data
        $this->store();

        // Redirect to the form-page
        $this->doRedirect( JRequest::getVar('view'), array( 'id' => $this->getId(), 'task' => 'copy'));
    }

    /**
     * Handle the task 'saveascopy'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function saveascopy()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Remove the identifier from whereever
        JRequest::setVar('id', 0);
        JRequest::setVar('cid[]', 0);
        JRequest::setVar('cid', null);

        // Store these data
        $this->store();

        // Redirect to the form-page
        $this->doRedirect( JRequest::getVar('view'), array( 'id' => $this->getId(), 'task' => 'copy'));
    }

    /**
     * Handle the task 'remove'
     * 
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function remove()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_DELETE'));
        }

        // Remove all selected items
        $model = $this->_loadModel();
        if (!$model->delete($cid)) {
            $this->msg = $model->getError() ;
        } else {
            if (count($cid) == 1) {
                $singleName = $this->getSingleName(JRequest::getCmd('view'));
                $this->msg = JText::_('LIB_YIREO_CONTROLLER_'.strtoupper($singleName). '_DELETED');
            } else {
                $pluralName = $this->getPluralName(JRequest::getCmd('view'));
                $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_'.strtoupper($pluralName). '_DELETED', count($cid));
            }
        }

        // Redirect to this same page
        $this->doRedirect();
    }


    /**
     * Handle the task 'publish'
     * 
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function publish()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_PUBLISH'));
        }

        // Use the model to publish this entry
        $model = $this->_loadModel();
        if (!$model->publish($cid, 1)) {
            echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
        } else {
            if (count($cid) == 1) {
                $singleName = $this->getSingleName(JRequest::getCmd('view'));
                $this->msg = JText::_('LIB_YIREO_CONTROLLER_ITEM_PUBLISHED');
            } else {
                $pluralName = $this->getPluralName(JRequest::getCmd('view'));
                $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_PUBLISHED', count($cid));
            }
        }

        // Redirect to this same page
        $this->doRedirect();
    }


    /**
     * Handle the task 'unpublish'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function unpublish()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get the ID-list
        $cid = $this->getIds();
        if (count( $cid ) < 1) {
            JError::raiseError(500, JText::_('LIB_YIREO_CONTROLLER_ITEM_SELECT_UNPUBLISH'));
        }

        // Use the model to unpublish this entry
        $model = $this->_loadModel();
        if (!$model->publish($cid, 0)) {
            echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
        } else {
            if (count($cid) == 1) {
                $singleName = $this->getSingleName(JRequest::getCmd('view'));
                $this->msg = JText::_('LIB_YIREO_CONTROLLER_ITEM_UNPUBLISHED');
            } else {
                $pluralName = $this->getPluralName(JRequest::getCmd('view'));
                $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_UNPUBLISHED', count($cid));
            }
        }

        // Redirect to this same page
        $this->doRedirect();
    }


    /**
     * Handle the task 'cancel'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function cancel()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Checkin the model
        $model = $this->_loadModel();
        $model->checkin();
        $model->resetTmpSession();

        // Redirect back to the overview page
        $plural = $this->getPluralName( JRequest::getVar('view') );
        $this->doRedirect( $plural );
    }

    /**
     * Handle the task 'orderup'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function orderup()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Order-up using the model
        $model = $this->_loadModel();
        $model->move(-1);

        // Redirect to this same page
        $this->doRedirect();
    }


    /**
     * Handle the task 'orderdown'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function orderdown()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Order-down using the model
        $model = $this->_loadModel();
        $model->move(1);

        // Redirect to this same page
        $this->doRedirect();
    }


    /**
     * Handle the task 'saveorder'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function saveorder()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Fetch the current ID-list
        $cid = $this->getIds();

        // Fetch the ordering-list
        $order = JRequest::getVar( 'order', array(), 'post', 'array' );
        JArrayHelper::toInteger($order);

        // Auto-correct ordering with only zeros
        if (!empty($order)) {
            $only_zero = true;
            foreach ($order as $o) {
                if ($o > 0) $only_zero = false;
            }

            if ($only_zero == true) {
                $j = 1;
                foreach ($order as $i => $o) {
                    $order[$i] = $j;
                    $j++;
                }
            }
        }

        // Save these data in the model
        $model = $this->_loadModel();
        $model->saveorder($cid, $order);

        // Redirect to this same page
        $this->doRedirect();
    }

    /**
     * Handle the task 'vote'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function vote()
    {
        // Security check
        JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Fetch base-variables
        $url = JRequest::getVar('url', '', 'default', 'string');
        $rating = JRequest::getVar('user_rating', 0, '', 'int');
        $id = JRequest::getVar('cid', 0, '', 'int');

        // Load the current model
        $model = $this->getModel('item');
        $model->setId($id);

        // If this vote is made from an external source, make sure we redirect to an internal page
        if (!JURI::isInternal($url)) {
            $option = JRequest::getCmd('option');
            $view = JRequest::getCmd('view');
            $url = JRoute::_('index.php?option='.$option.'&view='.$view.'&id='.$id);
        }

        // Store the vote in this model
        if ($model->storeVote($rating)) {
            $this->setRedirect($url, JText::_('LIB_YIREO_CONTROLLER_ITEM_VOTE_SUCCESS'));
        } else {
            $this->setRedirect($url, JText::_('LIB_YIREO_CONTROLLER_ITEM_VOTE_ALREADY'));
        }
    } 

    /**
     * Handle the task 'toggle'
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function toggle()
    {
        // Security check
        JRequest::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

        // Fetch the request-parameters
        $id = JRequest::getInt('id');
        $name = JRequest::getCmd('name');
        $value = JRequest::getInt('value');

        if ($id > 0 && strlen($name) > 0) {
            $model = $this->_loadModel();
            if (method_exists($model, 'toggle')) {
                $model->toggle($id, $name, $value);
            }
        }

        // Redirect to this same page
        $this->doRedirect();
    }

    /** Helper function to set the form page
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return bool
     */
    protected function setEditForm() 
    {
        // If we are in a "plural" view, redirect to a "single" view
        $current = JRequest::getCmd('view');
        $single = $this->getSingleName( $current );

        // If the current request does not have the right view, redirect to the right view        
        if ( $current != $single ) {

            $id = $this->getId();
            $variables = array( 'task' => JRequest::getCmd('task'));
            if ($id > 0) $variables['id'] = $id ;

            $this->doRedirect($single, $variables);
            return false;
        }

        // Hide the menu while editing or adding an item
        JRequest::setVar( 'hidemainmenu', 1 );

        // Display this page
        parent::display();
        return true;
    }


    /**
     * Helper function to load the current model
     * 
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return JModel
     */
    protected function _loadModel()
    {
        if ($this->_model === null) {

            // Derive the model-name from the current view
            $name = $this->getSingleName(JRequest::getVar('view'));

            // Create the model-object from the singular model-name
            $model = $this->getModel($name);

            // If it is still empty, try to create the model manually instead
            if (empty($model)) {
                $model = new YireoModel($name, $name.'s', $name.'_id');
            }

            $this->_model = $model;
        }
        
        return $this->_model;
    }

    /**
     * Helper function to het the plural form of a word
     *
     * @access protected
     * @subpackage Yireo
     * @param string $name
     * @return string
     */
    protected function getPluralName($name = '') 
    {
        $relations = $this->_relations;
        if (isset($relations[$name])) {
            return $relations[$name];
        } else if ($index = array_search($name, $relations)) {
            return $name;
        }

        if (preg_match('/s$/', $name) == false) {
            return $name.'s';
        }
        return $name;
    }

    /**
     * Helper function to get the singular form of a word
     *
     * @access protected
     * @subpackage Yireo
     * @param string $name
     * @return string
     */
    protected function getSingleName($name = '') 
    {
        $relations = $this->_relations;
        if (array_key_exists($name, $relations)) {
            return $name;
        } else if ($index = array_search($name, $relations)) {
            return $index;
        } else if (preg_match('/ses$/', $name)) {
            return preg_replace('/es$/', '', $name);
        }
        return preg_replace('/s$/', '', $name);
    }

    /**
     * Method to set the proper redirect
     *
     * @access protected
     * @subpackage Yireo
     * @param string $view
     * @param array $variables
     * @return bool
     */
    protected function doRedirect( $view = '', $variables = array())
    {
        // Detect the current view if it is not explicitely set
        if (empty( $view )) {
            $view = JRequest::getCmd('view');
        }

        // Fetch the current component name 
        $option = JRequest::getCmd('option');

        // Construct the URL
        $link = 'index.php?option='.$option.'&view='.$view ;

        // Add the extra variables to the URL if needed
        if (!empty( $variables )) {
            foreach ( $variables as $name => $value ) {
                $link .= '&'.$name.'='.$value;
            }
        }

        // Set the redirect, including messages if they are set
        if ($this->_application->isSite()) $link = JRoute::_($link);
        $this->setRedirect($link, $this->msg, $this->msg_type);
        return true;
    }

    /**
     * Manually set the ID
     *
     * @access protected
     * @subpackage Yireo
     * @param int
     * @param null
     */
    protected function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Method to get the current ID
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @param int 
     */
    protected function getId()
    {
        // Return the internal ID if it is set
        if (isset($this->id) && $this->id > 0) {
            return $this->id;
        }

        // Fetch the ID-list and return the first entry of it
        $cid = JRequest::getVar('cid', array(0), null, 'array');
        return (int)$cid[0];
    }

    /**
     * Method to get the selected IDs
     *
     * @access protected
     * @subpackage Yireo
     * @param null
     * @return array 
     */
    protected function getIds()
    {
        // Fetch the ID-list and make sure it renders as a list of numbers
        $cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
        JArrayHelper::toInteger($cid);
        return $cid;
    }
}
