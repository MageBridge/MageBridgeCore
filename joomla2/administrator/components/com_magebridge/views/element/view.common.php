<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla! 
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT.'/view.php';

/**
 * HTML View class
 *
 * @static
 * @package	MageBridge
 */
class MageBridgeViewCommon extends MageBridgeView
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
	public function display($tpl = null)
	{
        // Add CSS
        JHTML::stylesheet('media/com_magebridge/css/backend-elements.css');

        // Load jQuery
        YireoHelper::jquery();

        $current = JRequest::getVar('current');
        $object = JRequest::getVar('object');

        $this->assignRef('current', $current);
        $this->assignRef('object', $object);
		parent::display($tpl);
    }

    /*
     * Initialize the AJAX-layout
     *
     * @param null
     * @return null
     */
    public function doAjaxLayout()
    {
        // Set common options
        $this->setLayout('ajax');

        // Create a new request
        $request = array();

        // Get the current request-options 
        $get = JRequest::get('get');
        if (!empty($get)) {
            foreach ($get as $name => $value) {
                $request[$name] = $value;
            }
        }
        
        // Merge the POST if it is there
        $post = JRequest::get('post');
        if (!empty($post)) {
            foreach ($post as $name => $value) {
                $request[$name] = $value;
            }
        }

        // Add new variables
        $request['view'] = 'element';
        $request['format'] = 'ajax';

        // Load the AJAX-script
        $url = 'index.php?option=com_magebridge';
        foreach ($request as $name => $value) $url .= '&'.$name.'='.$value;
        MageBridgeElementHelper::ajax($url, 'ajaxelement');
	}

    /*
     * Initialize the category-layout
     *
     * @param null
     * @return null
     */
    public function doCategoryLayout()
    {
        // Initialize some important variables
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-categories';

        // Set common options
        $this->setTitle('Category');
        $this->setLayout('category');
        
        // Initialize search
        $search = $application->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
        $search = JString::strtolower( $search );

        // Set the data
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $tree = $cache->call( array( 'MageBridgeElementHelper', 'getCategoryTree' ));

        // If search is active, we use a flat list instead of a tree
        if(empty($search)) {
            $categories = MageBridgeElementHelper::getCategoryList($tree);
        } else {
            $categories = $tree;
        }

        // Initialize pagination
        $categories = $this->initPagination('categories', $categories);
        $this->assignRef('categories', $categories);

        // Add a dropdown list for Store Views
        $current_store = $application->getUserStateFromRequest($option.'.store', 'store');

        require_once JPATH_COMPONENT.'/fields/store.php';
        $class = 'JFormFieldStore';
        $field = JFormHelper::loadFieldType('store');
        $field->setName('store');
        $field->setValue($current_store);
        $store = $field->getHtmlInput();
        
        // Build the lists
        $lists = array();
        $lists['search']= $search;
        $lists['store']= $store;
        $this->assignRef('lists', $lists);
    }

    /*
     * Initialize the widget-layout
     *
     * @param null
     * @return null
     */
    public function doWidgetLayout()
    {
        // Set common options
        $this->setTitle('Widget');
        $this->setLayout('widget');
        
        // Set the data
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $widgets = $cache->call( array( 'MageBridgeElementHelper', 'getWidgetList' ));

        // Initialize pagination
        $widgets = $this->initPagination('widgets', $widgets);
        $this->assignRef('widgets', $widgets);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-widgets';
        $search = $application->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
        $search = JString::strtolower( $search );
        
        // Build the lists
        $lists = array();
        $lists['search']= $search;
        $this->assignRef('lists', $lists);
    }

    /*
     * Initialize the customer-layout
     *
     * @param null
     * @return null
     */
    public function doCustomerLayout()
    {
        // Set common options
        $this->setTitle('Customer');
        $this->setLayout('customer');
        
        // Set the data
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $customers = $cache->call( array( 'MageBridgeElementHelper', 'getCustomerList' ));

        // Initialize pagination
        $customers = $this->initPagination('customers', $customers);
        $this->assignRef('customers', $customers);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-customers';
        $search = $application->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
        $search = JString::strtolower( $search );
        
        // Build the lists
        $lists = array();
        $lists['search']= $search;
        $this->assignRef('lists', $lists);
    }

    /*
     * Initialize the product-layout
     *
     * @param null
     * @return null
     */
    public function doProductLayout()
    {
        // Set common options
        $this->setTitle('Product');
        $this->setLayout('product');
        
        // Set the data
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $products = $cache->call( array( 'MageBridgeElementHelper', 'getProductList' ));

        // Initialize pagination
        $products = $this->initPagination('products', $products);
        $this->assignRef('products', $products);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-products';
        $search = $application->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
        $search = JString::strtolower( $search );
        
        // Build the lists
        $lists = array();
        $lists['search']= $search;
        $this->assignRef('lists', $lists);
    }

    /*
     * Helper-method to set pagination
     *
     * @param string $type
     * @param array $items
     * @return array
     */
    public function initPagination($type = '', $items = array())
    {
        // Get the limit & limitstart
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-'.$type;
        $limit = $application->getUserStateFromRequest( $option.'.limit', 'limit', $application->getCfg('list_limit'), 'int' );
        $limitstart = $application->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

        // Set the pagination
        jimport('joomla.html.pagination');
        $pagination = new JPagination( count($items), $limitstart, $limit );
        $this->assignRef('pagination', $pagination);

        // Do not do anything when using a limit of 0
        if ($limit == 0) {
            return $items;
        }

        // Split the items 
        if (!empty($items)) {
            $items = array_splice($items, $limitstart, $limit, true);
        }

        // Return the items
        return $items;
    }
}
