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
 * MageBridge model handling the product-search (used in Yireo_MageBridge_Model_Product_Api)
 */
class Yireo_MageBridge_Model_Search extends Mage_Core_Model_Abstract
{
    /**
     * Search for products 
     *
     * @access public
     * @param string $text
     * @param array $searchFields
     * @return array
     */
    public function getResult($text, $searchFields = array())
    {
        try {
            // Definitions
            $helper = Mage::helper('catalogsearch');
            $storeId = Mage::app()->getStore()->getId();

            // Preliminary checks
            if(empty($text)) {
                Mage::getSingleton('magebridge/debug')->error('Empty search-query');
                return false;

            } elseif(Mage::helper('core/string')->strlen($text < $helper->getMinQueryLength())) {
                Mage::getSingleton('magebridge/debug')->error('Search-query shorted than minimum length');
                return false;
            }

            // Get the Query-object to track down this individual search
            $query = Mage::getModel('catalogsearch/query')->loadByQuery($text);
            $query->setStoreId($storeId);

            // Initialize the query and increase its counter
            if(!$query->getId()) {
                $query->setQueryText($text);
                $query->setPopularity(1);
            } else {
                $query->setPopularity($query->getPopularity()+1);
            }

            // Save the search-record to the database
            $query->prepare();
            $query->save();

            // Force preoutput
            if($query->getRedirect()) {
                Mage::app()->getResponse()->setRedirect($query->getRedirect());
                Mage::getSingleton('magebridge/core')->setForcedPreoutput(true);
                return;
            }

            // Get the collection the good way (but this only works if Flat Catalog is disabled)
            // otherwise error "Call to undefined method Mage_Catalog_Model_Resource_Product_Flat::getEntityTablePrefix()"
            if(Mage::getStoreConfig('catalog/frontend/flat_catalog_product') == 0) {

                $visibility = array(
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                );

                $collection = Mage::getResourceModel('catalogsearch/search_collection')
                    ->addSearchFilter($text)
                    ->addAttributeToFilter('visibility', $visibility)
                    ->addStoreFilter()
                    ->addMinimalPrice()
                    ->addTaxPercents()
                ;

            // Instead of using the original classes, grab the collection using SQL-statements
            } else {
                $catalogsearchTable = Mage::getSingleton('core/resource')->getTableName('catalogsearch/fulltext');;
                $collection = Mage::getResourceModel('catalogsearch/search_collection');
                $collection->getSelect()
                    ->join(array('search' => $catalogsearchTable), 'e.entity_id=search.product_id', array())
                    ->where('search.data_index LIKE "%'.$text.'%"')
                    ->where('search.store_id='.(int)$storeId)
                ;
            }

            // Log the collection size with this query-result
            $collectionSize = $collection->getSize();
            if($query->getNumResults() != $collectionSize) {
                $query->setNumResults($collectionSize);
                $query->save();
            }
                
            // Return the collection
            return $collection;

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error($e->getMessage());
            return false;
        }
    }
}
