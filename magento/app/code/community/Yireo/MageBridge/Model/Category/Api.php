<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2013
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge API-model for category resources
 */
class Yireo_MageBridge_Model_Category_Api extends Mage_Catalog_Model_Api_Resource
{
    /*
     * Method to return a tree of product categories
     *
     * @access public
     * @param int $parentId
     * @param string $store
     * @return array
     */
    public function items($arguments = null)
    {
        // Initializing caching
        if(Mage::app()->useCache('collections')) {
            $cacheId = 'magebridge_category_api__items'.md5(serialize($arguments));
            if($cache = Mage::app()->loadCache($cacheId)) {
                $result = unserialize($cache);
                if(!empty($result)) return $result;
            }
        }

        // Get the collection
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addUrlRewriteToResult()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('include_in_menu')
        ;

        // Set the store
        if(!empty($arguments['store'])) {
            $collection->setStoreId($arguments['store']);
        }

        // Add a filter
        if (isset($arguments['filters']) && is_array($arguments['filters'])) {
            $filters = $arguments['filters'];
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Invalid search filter', $e->getMessage());
            }
        }

        // Parse the collection into an array
        $result = array();
        foreach($collection as $category) {

            // Get the debug-array of this object
            $c = $category->debug();

            $result[] = $c;
        }

        // Save to cache
        if(Mage::app()->useCache('collections')) {
            Mage::app()->saveCache(serialize($result), $cacheId, array('collections'), 86400);
        }

        return $result;
    }

    /*
     * Method to return a tree of product categories
     *
     * @access public
     * @param array $arguments
     * @return array
     */
    public function tree($arguments = null)
    {
        // Parse the arguments
        $storeId = (isset($arguments['storeId'])) ? $arguments['storeId'] : $this->_getStoreId();
        $storeGroupId = (isset($arguments['storeGroupId'])) ? $arguments['storeGroupId'] : null;
        $parentId = (isset($arguments['parentId'])) ? $arguments['parentId'] : null;
        $parentUrlKey = (isset($arguments['parentUrlKey'])) ? $arguments['parentUrlKey'] : null;

        // Select the storeId based on this store-group
        if($storeGroupId > 0) $storeId = Mage::getModel('core/store_group')->load($storeGroupId)->getDefaultStoreId();

        // If the arguments do not include a store-flag, include it so not to mess up caching
        if(!is_array($arguments)) $arguments = array();
        if(!isset($arguments['storeId'])) $arguments['storeId'] = $storeId;

        // Initializing caching
        if(Mage::app()->useCache('collections')) {
            $cacheId = 'magebridge_category_api__tree'.md5(serialize($arguments));
            if($cache = Mage::app()->loadCache($cacheId)) {
                $result = unserialize($cache);
                if(!empty($result)) return $result;
            }
        }

        // Set the current store as active (otherwise the Root Catalog does not switch)
        Mage::app()->setCurrentStore(Mage::app()->getStore($storeId));

        // Try to determine the parentId if the parentUrlKey is set
        if(!empty($parentUrlKey)) {
            $parent = Mage::getModel('catalog/category')->load($parentUrlKey, 'url_key');
            if(!empty($parent) && $parent->getId() > 0) {
                $parentId = $parent->getId();
            }
        }

        // Determine the parent ID
        if (empty($parentId) && !is_null($storeId)) {
            $parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
        } elseif (empty($parentId)) {
            $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        }

        // Get the root of this tree
        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();
        $root = $tree->getNodeById($parentId);
        if($root && $root->getId() == 1) {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }

        // Get the collection
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->setStoreId($storeId)
            ->addUrlRewriteToResult()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('is_active')
            ->addAttributeToSelect('include_in_menu')
        ;

        // Filter only active categories
        if(isset($arguments['active'])) {
            $collection->addAttributeToFilter('is_active', 1);
        }

        // Fetch the products of this category
        if(isset($arguments['include_products'])) {
            foreach($collection as $category) {
                $products = $this->_getAllProducts($storeId);
                $productsInCategory = array();
                if(!empty($products)) {
                    foreach($products as $product) {
                        if(is_array($product['category_ids']) && in_array($category->getId(), $product['category_ids'])) {
                            $productsInCategory[] = $product;
                        }
                    }
                }
                $category->setProducts($productsInCategory);
            }
        }

        // Fetch the product count
        if(isset($arguments['include_product_count'])) {
            foreach($collection as $category) {
                $product_count = $category->getProductCount();
                $category->setData('product_count', $product_count);
            }
        }

        // Add the collection to this tree-structure
        $tree->addCollectionData($collection, true);
        $result = $this->_nodeToArray($root);

        // Save to cache
        if(Mage::app()->useCache('collections')) {
            Mage::app()->saveCache(serialize($result), $cacheId, array('collections'), 86400);
        }

        return $result;
    }

    /*
     * Method to get all (!) products from the database
     *
     * @access protected
     * @param int $storeId
     * @return int
     */
    protected function _getAllProducts($storeId = null)
    {
        static $products = null;
        if(empty($products)) {
            $arguments = array(
                'visibility' => array(
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                ),
            );
            if(!empty($storeId)) $arguments['store'] = $storeId;
            $products = Mage::getModel('magebridge/product_api')->items($arguments);
        }
        return $products;
    }

    /*
     * Override of the original method to fetch the current store-name from the bridge
     *
     * @access protected
     * @param string $store
     * @return int
     */
    protected function _getStoreId($store = null)
    {
        $store = Mage::app()->getStore(Mage::getModel('magebridge/core')->getStore());
        return parent::_getStoreId($store);
    }

    /*
     * Method to convert a category-node to an array
     *
     * @access protected
     * @param Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _nodeToArray(Varien_Data_Tree_Node $node)
    {
        if(empty($node)) {
            return array();
        }

        $result = $node->debug();
        $result['category_id'] = $node->getId();
        $result['parent_id']   = $node->getParentId();
        $result['name']        = $node->getName();
        $result['is_active']   = $node->getIsActive();
        $result['is_anchor']   = $node->getIsAnchor();
        $result['url_key']     = $node->getUrlKey();
        $result['url']         = $node->getRequestPath();
        $result['position']    = $node->getPosition();
        $result['level']       = $node->getLevel();
        $result['products']    = $node->getProducts();

        $result['children']    = array();
        foreach ($node->getChildren() as $child) {
            $result['children'][] = $this->_nodeToArray($child);
        }

        return $result;
    }
}
