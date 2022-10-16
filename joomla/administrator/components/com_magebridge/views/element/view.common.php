<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class
 *
 * @static
 * @package    MageBridge
 */
class MageBridgeViewCommon extends MageBridgeView
{
    /**
     * Display method
     *
     * @param string $tpl
     *
     * @return void
     */
    public function display($tpl = null)
    {
        // Add CSS
        JHtml::stylesheet('media/com_magebridge/css/backend-elements.css');

        // Load jQuery
        YireoHelper::jquery();

        $this->current = JFactory::getApplication()->input->get('current');
        $this->object = JFactory::getApplication()->input->get('object');

        parent::display($tpl);
    }

    /**
     * Initialize the AJAX-layout
     */
    public function doAjaxLayout()
    {
        // Set common options
        $this->setLayout('ajax');

        // Create a new request
        $request = [];

        // Get the current request-options
        $get = JFactory::getApplication()->input->get->getArray();

        if (!empty($get)) {
            foreach ($get as $name => $value) {
                $request[$name] = $value;
            }
        }

        // Merge the POST if it is there
        $post = JFactory::getApplication()->input->post->getArray();

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

        foreach ($request as $name => $value) {
            $url .= '&' . $name . '=' . $value;
        }

        MageBridgeElementHelper::ajax($url, 'ajaxelement');
    }

    /**
     * Initialize the category-layout
     */
    public function doCategoryLayout()
    {
        // Initialize some important variables
        $application = JFactory::getApplication();
        $option = $application->input->getCmd('option') . '-element-categories';

        // Set common options
        $this->setTitle('Category');
        $this->setLayout('category');

        // Initialize search
        $search = $application->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        /** @var JCache $cache */
        $cache = JFactory::getCache('com_magebridge.admin');
        $tree = $cache->call(['MageBridgeElementHelper', 'getCategoryTree']);

        // If search is active, we use a flat list instead of a tree
        if (empty($search)) {
            $categories = MageBridgeElementHelper::getCategoryList($tree);
        } else {
            $categories = $tree;
        }

        // Initialize pagination
        $this->categories = $this->initPagination('categories', $categories);

        // Add a dropdown list for Store Views
        $current_store = $application->getUserStateFromRequest($option . '.store', 'store');

        require_once JPATH_COMPONENT . '/fields/store.php';

        $field = JFormHelper::loadFieldType('magebridge.store');
        $field->setName('store');
        $field->setValue($current_store);
        $store = $field->getHtmlInput();

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $lists['store'] = $store;

        $this->lists = $lists;
    }

    /**
     * Initialize the widget-layout
     */
    public function doWidgetLayout()
    {
        // Set common options
        $this->setTitle('Widget');
        $this->setLayout('widget');

        /** @var JCache $cache */
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $widgets = $cache->call(['MageBridgeElementHelper', 'getWidgetList']);

        // Initialize pagination
        $this->widgets = $this->initPagination('widgets', $widgets);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option') . '-element-widgets';
        $search = $application->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Initialize the customer-layout
     */
    public function doCustomerLayout()
    {
        // Set common options
        $this->setTitle('Customer');
        $this->setLayout('customer');

        /** @var JCache $cache */
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $customers = $cache->call(['MageBridgeElementHelper', 'getCustomerList']);

        // Initialize pagination
        $this->customers = $this->initPagination('customers', $customers);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option') . '-element-customers';
        $search = $application->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Initialize the product-layout
     */
    public function doProductLayout()
    {
        // Set common options
        $this->setTitle('Product');
        $this->setLayout('product');

        /** @var JCache $cache */
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->setCaching(0);
        $products = $cache->call(['MageBridgeElementHelper', 'getProductList']);

        // Initialize pagination
        $this->products = $this->initPagination('products', $products);

        // Initialize search
        $application = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option') . '-element-products';
        $search = $application->getUserStateFromRequest($option . '.search', 'search', '', 'string');
        $search = strtolower($search);

        // Build the lists
        $lists = [];
        $lists['search'] = $search;
        $this->lists = $lists;
    }

    /**
     * Helper-method to set pagination
     *
     * @param string $type
     * @param array  $items
     *
     * @return array
     */
    public function initPagination($type = '', $items = [])
    {
        // Get the limit & limitstart
        $application = JFactory::getApplication();
        $option = $application->input->getCmd('option') . '-element-' . $type;
        $limit = (int) $application->getUserStateFromRequest($option . '.limit', 'limit', JFactory::getConfig()->get('list_limit'), 'int');
        $limitstart = (int) $application->getUserStateFromRequest($option . '.limitstart', 'limitstart', 0, 'int');

        // Set the pagination
        $this->pagination = new JPagination(count($items), $limitstart, $limit);

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
