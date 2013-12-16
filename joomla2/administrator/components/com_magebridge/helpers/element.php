<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Element Helper
 */
class MageBridgeElementHelper
{
    /*
     * Add the AJAX-script to the page
     *
     * @param string $url
     * @param string $div
     * @return null
     */
    static public function ajax($url, $div)
    {
        include_once JPATH_ADMINISTRATOR.'/components/com_magebridge/libraries/helper/view.php';
        return YireoHelperView::ajax($url, $div);
    }

    /*
     * Initialize the category-layout
     *
     * @param null
     * @return null
     */
    static public function doCategoryLayout()
    {
        // Set common options
        $this->setTitle('Category');
        $this->setLayout('category');
        
        // Set the data
        $cache = JFactory::getCache('com_magebridge.admin');
        $tree = $cache->call( array( 'MageBridgeElementHelper', 'getCategoryTree' ));
        $categories = $this->getCategoryList($tree);

        $this->assignRef('categories', $categories);
    }

    /*
     * Call the API for a widget-list
     *
     * @param null
     * @return array
     */
    static public function getWidgetList()
    {
        // Register this request
        $register = MageBridgeModelRegister::getInstance();
        $id = $register->add('api', 'magebridge_widget.list');

        // Send the request to the bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();
        $list = $bridge->getAPI('magebridge_widget.list');
        return $list;
    }

    /*
     * Call the API for a customer list
     *
     * @param null
     * @return array
     */
    static public function getCustomerList()
    {
        // Register this request
        $register = MageBridgeModelRegister::getInstance();
        $id = $register->add('api', 'customer_customer.list');

        // Send the request to the bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();
        $list = $bridge->getAPI('customer_customer.list');
        return $list;
    }

    /*
     * Call the API for a product list
     *
     * @param null
     * @return array
     */
    static public function getProductList()
    {
        // Construct the arguments
        $arguments = array(
            'minimal_price' => 0,
        );

        // Fetch any current filters
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-products';
        $limit = $application->getUserStateFromRequest( $option.'.limit', 'limit', $application->getCfg('list_limit'), 'int' );
        $limitstart = $application->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
        $search = $application->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
        $search = JString::strtolower(trim($search));

        // Add the search-filter
        if (strlen($search) > 0) {
            $arguments['filters'] = array(
                'name' => array('like' => array('%'.$search.'%'))
            );
        }

        // Register this request
        $register = MageBridgeModelRegister::getInstance();
        $id = $register->add('api', 'magebridge_product.list', $arguments);

        // Send the request to the bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();

        // Get the list of products
        $list = $bridge->getAPI('magebridge_product.list', $arguments);
        return $list;
    }

    /*
     * Call the API for a category tree
     *
     * @param array $arguments
     * @return array
     */
    static public function getCategoryTree($arguments = array())
    {
        // Initialize some important variables
        $application = JFactory::getApplication();
        $option = JRequest::getCmd( 'option' ).'-element-categories';

        // Add arguments
        $store = $application->getUserStateFromRequest( $option.'.store', 'store');
        $store = explode(':', $store);
        if ($store[0] == 'v' || $store[0] == 's') $arguments['storeId'] = $store[1];
        if ($store[0] == 'g') $arguments['storeGroupId'] = $store[1];

        // Register this request
        $register = MageBridgeModelRegister::getInstance();
        $register->add('api', 'magebridge_category.tree', $arguments);

        // Send the request to the bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build();

        // Get the category-tree
        $tree = $bridge->getAPI('magebridge_category.tree', $arguments);
        return $tree;
    }

    /*
     * Recursive function to parse the category-tree in a flat-list
     *
     * @param array $tree
     * @param array $list
     * @return array
     */
    static public function getCategoryList($tree = null, $list = array()) 
    {
        // Determine if this node has children
        if (count($tree['children']) > 0) {
            $tree['has_children'] = true;
            $children = $tree['children'];
            unset($tree['children']);
        } else {
            $tree['has_children'] = false;
        }

        // Add non-root categories to the list
        if (isset($tree['level']) && $tree['level'] > 0) {

            $tree['indent'] = '';
            for($i = 1; $i < $tree['level']; $i++) {
                $tree['indent'] .= '&nbsp; -';
            }

            $list[] = $tree;
        }

        // Parse the children
        if (!empty($children)) {
            foreach ($children as $child) {
                $list = MageBridgeElementHelper::getCategoryList($child, $list);
            }
        }

        return $list;
    }
}
