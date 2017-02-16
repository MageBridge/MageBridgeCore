<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge API-model for product resources
 */
class Yireo_MageBridge_Model_Product_Api extends Mage_Catalog_Model_Api_Resource
{
    protected $arguments = [];

    /**
     * Search for products 
     *
     * @access public
     * @param array $options
     * @return array
     */
    public function search($options = array())
    {
        // Extra the attributes to search for
        if(!empty($options['search_fields'])) {
            $searchFields = $options['search_fields'];
        } else {
            $searchFields = array('title', 'description');
        }

        // Fetch the search collection
        $collection = Mage::getModel('magebridge/search')->getResult($options['text'], $searchFields);

        $result = array();
        if(empty($collection)) {
            return $result;
        }

        $i = 0;
        foreach($collection as $item) {

            if($i == $options['search_limit']) break;
            $product = Mage::getModel('catalog/product')->load($item->getId());
                
            $product_price_tax = Mage::helper('tax')->getProductPrice($product);

            $result[] = array( // Basic product data
                'product_id'        => $product->getId(),
                'sku'               => $product->getSku(),
                'name'              => $product->getName(),
                'description'       => $product->getDescription(),
                'short_description' => $product->getShortDescription(),
                'meta_description'  => $product->getMetaDescription(),
                'meta_keyword'      => $product->getMetaKeyword(),
                'label'             => htmlentities($product->getName()),
                'author'            => $product->getAuthor(),
                'url_key'           => $product->getUrlKey(),
                'url_path'          => $product->getUrlPath(),
                'url_store'         => $product->getUrlInStore(),
                'url'               => $product->getProductUrl(false),
                'category_ids'      => $product->getCategoryIds(),
                'thumbnail'         => $product->getThumbnailUrl(),
                'image'             => $product->getImageUrl(),
                'small_image'       => $product->getSmallImageUrl(),
                'price'             => Mage::app()->getStore()->formatPrice($product->getPrice()),
                'price_tax'         => Mage::app()->getStore()->formatPrice($product_price_tax),
                'price_tax_raw'     => $product_price_tax,
                'price_raw'         => $product->getPrice(),
                'price_tier'        => $product->getTierPrice(1),
                'special_price'     => Mage::app()->getStore()->formatPrice($product->getSpecialPrice()),
                'special_price_raw' => $product->getSpecialPrice(),
                'special_from_date' => $product->getSpecialFromDate(),
                'special_to_date'   => $product->getSpecialToDate(),
                'created_at'        => $product->getCreatedAt(),
                'is_active'         => 1,
            );

            $i++;
        }

        return $result;
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @access public
     * @param array $filters
     * @return array
     */
    public function info($arguments = null)
    {
        $product = Mage::getModel('catalog/product')->load($arguments['product_id']);
        if(!$product->getId() > 0) {
            return null;
        }

        $product = Mage::helper('magebridge/product')->export($product, $arguments);
        return $product;
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @access public
     * @param array $filters
     * @return array
     */
    public function items($arguments = null)
    {
        $this->arguments = $arguments;
        $this->autocompleteScopes();

        // Determine caching
        $caching = $this->allowCollectionCaching();

        // Initializing caching
        if($caching) {
            if($cache = Mage::app()->loadCache($this->getCacheId())) {
                $results = unserialize($cache);
                if(!empty($results)) {
                    return $results;
                }
            }
        }

        // Fetch the collection
        $collection = $this->getCollection();

        // Loop through the collection
        $result = array();
        foreach ($collection as $product) {
            $result[] = Mage::helper('magebridge/product')->export($product, $this->arguments);
        }

        // Save to cache
        if($caching) {
            Mage::app()->saveCache(serialize($result), $this->getCacheId(), array('collections'), 86400);
        }

        return $result;
    }

    /**
     * Retrieve the count of a listing of products
     *
     * @param array $arguments
     * @return int
     */
    public function count($arguments = null)
    {
        $this->arguments = $arguments;
        $collection = $this->getCollection();
        return $collection->getSize();
    }

    protected function autocompleteScopes()
    {
        // Handle store codes
        if(isset($this->arguments['store']) && is_string($this->arguments['store'])) {
            $this->arguments['store'] = Mage::app()->getStore($this->arguments['store'])->getId();
        }

        if(!empty($this->arguments['store'])) {
            $this->arguments['website'] = Mage::app()->getStore($this->arguments['store'])->getWebsite()->getId();
        }

        // Initialize arguments with default values
        if(empty($this->arguments['website'])) {
            $this->arguments['website'] = Mage::app()->getStore()->getWebsiteId();
        }

        if(empty($this->arguments['store'])) {
            $this->arguments['store'] = Mage::app()->getStore()->getStoreId();
        }
    }

    protected function allowCollectionCaching()
    {
        if(isset($this->arguments['ordering']) && $this->arguments['ordering'] == 'random') {
            return false;
        }

        return (bool)Mage::app()->useCache('collections');
    }

    protected function getCacheId()
    {
        return 'magebridge_product_api_items_'.md5(serialize($this->arguments));
    }

    /**
     * Retrieve a product collection
     *
     * @return collection
     */
    protected function getCollection()
    {
        $collection = $this->getBareCollection();

        $this->applyCategoryFilters($collection);
        $this->applyCustomFilters($collection);
        $this->applyOrdering($collection);
        $this->applyPaging($collection);
        $this->applyMinimalPricing($collection);

        return $collection;
    }

    protected function getBareCollection()
    {
        Mage::app()->setCurrentStore($this->arguments['store']); 

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('visibility', $this->getVisibilityFilter())
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 1)
        ;

        if(!empty($this->arguments['website'])) {
            $collection->addWebsiteFilter($this->arguments['website']);
        }
            
        if(!empty($this->arguments['store'])) {
            $collection->addStoreFilter($this->arguments['store']);
            $collection->setStoreId($this->arguments['store']);
        }

        return $collection;
    }

    protected function getVisibilityFilter()
    {
        if(isset($this->arguments['visibility'])) {
            return $this->arguments['visibility'];
        }

        return array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        );
    }

    protected function applyCategoryFilters(&$collection)
    {
        // Add the category by its URL Key
        if(isset($this->arguments['category_url_key']) && !isset($this->arguments['category_id'])) {
            $categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('url_key', $this->arguments['category_url_key']);
            if(!empty($categories)) {
                foreach($categories as $category) {
                    $this->arguments['category_id'] = $category->entity_id;
                    break;
                }
            }
        }

        // Add the category by its ID
        if(isset($this->arguments['category_id']) && $this->arguments['category_id'] > 0) {
            $collection->addCategoryFilter(Mage::getModel('catalog/category')->load($this->arguments['category_id']));
        }
    }

    protected function applyCustomFilters(&$collection)
    {
        // Add a filter
        if (isset($this->arguments['filters']) && is_array($this->arguments['filters'])) {
            $filters = $this->arguments['filters'];
            try {
                foreach ($filters as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Invalid search filter', $e->getMessage());
            }
        }
    }

    protected function applyOrdering(&$collection)
    {
        if(!isset($this->arguments['ordering'])) {
            $this->arguments['ordering'] = 'newest';
        }

        // Set ordering
        switch($this->arguments['ordering']) {
            case 'newest':
                $collection->setOrder('created_at', 'desc');
                return;

            case 'oldest':
                $collection->setOrder('created_at', 'asc');
                return;

            case 'popular':
                $collection->setOrder('ordered_qty', 'desc');
                return;

            case 'featured':
                $collection->addAttributeToFilter('feature', 1);
                $collection->setOrder('created_at', 'desc');
                return;
        }

        // Create a duplicate query to randomize things
        if ($this->arguments['ordering'] == 'random') {
            $this->applyRandomOrdering($collection);
        }
    }
    protected function applyRandomOrdering(&$collection)
    {
        $productIds = $collection->getAllIds();
        shuffle($productIds);

        //$collection->addIdFilter(implode(',', $productIds));
        $collection->addAttributeToFilter('entity_id', array('in' => $productIds));
    }

    protected function applyPaging(&$collection)
    {
        // Add a list limit
        if(!empty($this->arguments['count'])) {
            $collection->setPageSize($this->arguments['count']);
        }

        // Add a page number
        if(isset($this->arguments['page']) && $this->arguments['page'] > 0) {
            $collection->setCurPage($this->arguments['page']);
        }
    }

    protected function applyMinimalPricing(&$collection)
    {
        // Add the minimal price
        if(!isset($this->arguments['minimal_price']) || $this->arguments['minimal_price'] == 1) {
            $collection->addMinimalPrice();
        }
    }
}
