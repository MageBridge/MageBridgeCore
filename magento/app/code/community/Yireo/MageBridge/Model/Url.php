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
 * MageBridge model for fetching lists of URLs
 */
class Yireo_MageBridge_Model_Url extends Mage_Core_Model_Abstract
{
    /**
     * Data
     *
     * @var mixed
     */
    protected $_data = null;

    /*
     * Method to get the URLs as an array
     *
     * @access public
     * @param string $type
     * @param string $id
     * @return array
     */
    public function getData($type = 'product', $id = null)
    {
        static $urls = array();
        if(empty($urls[$type])) {

            $magebridge = Mage::getSingleton('magebridge/core');
            $urls[$type] = array();

            switch($type) {

                case 'category':
                    $categories = Mage::getModel('catalog/category')->getTreeModel();
                    $helper = Mage::helper('catalog/category');
                    $categories = $helper->getStoreCategories('name', true, false);
                    foreach($categories as $category) {
                        $urls[$type][] = array( 'id' => $category->getId(), 'url' => $magebridge->parse($category->getUrl()));
                    }
                    break;

                case 'product':
                default:
                    $products = Mage::getModel('catalog/product')->getCollection();
                    foreach($products as $index => $product) {
                        $urls[$type][] = array( 'id' => $product->getId(), 'url' => $magebridge->parse($product->getProductUrl()));
                    }
                    break;
            }
        }

        if($id > 0) {
            return $urls[$type][$id];
        } else {
            return (array)$urls[$type];
        }
    }
}
