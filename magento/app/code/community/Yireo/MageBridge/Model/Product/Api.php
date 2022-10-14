<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2017
 * @license Open Source License
 * @link https://www.yireo.com
 */

/**
 * MageBridge API-model for product resources
 */
class Yireo_MageBridge_Model_Product_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * Search for products
     *
     * @param array $options
     * @return array
     */
    public function search($options = [])
    {
        // Extra the attributes to search for
        if (!empty($options['search_fields'])) {
            $searchFields = $options['search_fields'];
        } else {
            $searchFields = ['title', 'description'];
        }

        // Fetch the search collection
        $collection = Mage::getModel('magebridge/search')->getResult($options['text'], $searchFields);

        $result = [];
        if (empty($collection)) {
            return $result;
        }

        $i = 0;
        foreach ($collection as $item) {
            if ($i == $options['search_limit']) {
                break;
            }

            $product = Mage::getModel('catalog/product')->load($item->getId());
            $store = Mage::app()->getStore();

            $result[] = [ // Basic product data
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
                'price'             => $store->formatPrice($product->getPrice()),
                'price_tax'         => $store->formatPrice($store->convertPrice($product->getPrice())),
                'price_tax_raw'     => $store->convertPrice($product->getPrice()),
                'price_raw'         => $product->getPrice(),
                'price_tier'        => $product->getTierPrice(1),
                'special_price'     => $store->formatPrice($product->getSpecialPrice()),
                'special_price_raw' => $product->getSpecialPrice(),
                'special_price_tax' => $store->formatPrice($store->convertPrice($product->getSpecialPrice())),
                'special_price_tax_raw' => $store->convertPrice($product->getSpecialPrice()),
                'special_from_date' => $product->getSpecialFromDate(),
                'special_to_date'   => $product->getSpecialToDate(),
                'created_at'        => $product->getCreatedAt(),
                'is_active'         => 1,
            ];

            $i++;
        }

        return $result;
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $arguments
     * @return array
     */
    public function info($arguments = null)
    {
        $product = Mage::getModel('catalog/product')->load($arguments['product_id']);
        if (!$product->getId() > 0) {
            return null;
        }

        $product = Mage::helper('magebridge/product')->export($product, $arguments);
        return $product;
    }

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param array $arguments
     * @return array
     */
    public function items($arguments = null)
    {
        $this->arguments = $arguments;
        $this->autocompleteScopes();

        // Determine caching
        $caching = $this->allowCollectionCaching();

        // Initializing caching
        if ($caching) {
            if ($cache = Mage::app()->loadCache($this->getCacheId())) {
                $results = unserialize($cache);
                if (!empty($results)) {
                    return $results;
                }
            }
        }

        // Fetch the collection
        $collection = $this->getCollection();

        // Loop through the collection
        $result = [];
        foreach ($collection as $product) {
            $result[] = Mage::helper('magebridge/product')->export($product, $this->arguments);
        }

        // Save to cache
        if ($caching) {
            Mage::app()->saveCache(serialize($result), $this->getCacheId(), ['collections'], 86400);
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

    /**
     *
     */
    protected function autocompleteScopes()
    {
        // Handle store codes
        if (isset($this->arguments['store']) && is_string($this->arguments['store'])) {
            $this->arguments['store'] = Mage::app()->getStore($this->arguments['store'])->getId();
        }

        if (!empty($this->arguments['store'])) {
            $this->arguments['website'] = Mage::app()->getStore($this->arguments['store'])->getWebsite()->getId();
        }

        // Initialize arguments with default values
        if (empty($this->arguments['website'])) {
            $this->arguments['website'] = Mage::app()->getStore()->getWebsiteId();
        }

        if (empty($this->arguments['store'])) {
            $this->arguments['store'] = Mage::app()->getStore()->getStoreId();
        }
    }

    /**
     * @return bool
     */
    protected function allowCollectionCaching()
    {
        if (isset($this->arguments['ordering']) && $this->arguments['ordering'] == 'random') {
            return false;
        }

        return (bool)Mage::app()->useCache('collections');
    }

    /**
     * @return string
     */
    protected function getCacheId()
    {
        return 'magebridge_product_api_items_'.md5(serialize($this->arguments));
    }

    /**
     * Retrieve a product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
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

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getBareCollection()
    {
        Mage::app()->setCurrentStore($this->arguments['store']);

        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToFilter('visibility', $this->getVisibilityFilter())
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status', 1)
        ;

        if (!empty($this->arguments['website'])) {
            $collection->addWebsiteFilter($this->arguments['website']);
        }

        if (!empty($this->arguments['store'])) {
            $collection->addStoreFilter($this->arguments['store']);
            $collection->setStoreId($this->arguments['store']);
        }

        return $collection;
    }

    /**
     * @return array|mixed
     */
    protected function getVisibilityFilter()
    {
        if (isset($this->arguments['visibility'])) {
            return $this->arguments['visibility'];
        }

        return [
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
        ];
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyCategoryFilters(&$collection)
    {
        // Add the category by its URL Key
        if (isset($this->arguments['category_url_key']) && !isset($this->arguments['category_id'])) {
            $categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToFilter('url_key', $this->arguments['category_url_key']);
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $this->arguments['category_id'] = $category->entity_id;
                    break;
                }
            }
        }

        // Add the category by its ID
        if (!empty($this->arguments['category_id'])) {
            /** @var Mage_Catalog_Model_Category $category */
            $category = Mage::getModel('catalog/category');

            if (is_numeric($this->arguments['category_id'])) {
                $category = $category->load($this->arguments['category_id']);
            } else {
                $category = $category->loadByAttribute('url_key', $this->arguments['category_id']);
            }

            if ($category->getId() > 0) {
                $collection->addCategoryFilter($category);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
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

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyOrdering(&$collection)
    {
        if (!isset($this->arguments['ordering'])) {
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

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyRandomOrdering(&$collection)
    {
        $productIds = $collection->getAllIds();

        $numberOfItems = (!empty($this->arguments['count'])) ? $this->arguments['count'] : 3;
        $choosenIds = [];
        $maxKey = count($productIds)-1;
        while (count($choosenIds) < $numberOfItems) {
            $randomKey = mt_rand(0, $maxKey);
            $choosenIds[$randomKey] = $productIds[$randomKey];
        }

        $collection->addIdFilter($choosenIds);
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyPaging(&$collection)
    {
        // Add a list limit
        if (!empty($this->arguments['count'])) {
            $collection->setPageSize($this->arguments['count']);
        }

        // Add a page number
        if (isset($this->arguments['page']) && $this->arguments['page'] > 0) {
            $collection->setCurPage($this->arguments['page']);
        }
    }

    /**
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     */
    protected function applyMinimalPricing(&$collection)
    {
        // Add the minimal price
        if (!isset($this->arguments['minimal_price']) || $this->arguments['minimal_price'] == 1) {
            $collection->addMinimalPrice();
        }
    }
}
