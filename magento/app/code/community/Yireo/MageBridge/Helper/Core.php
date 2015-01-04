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

class Yireo_MageBridge_Helper_Core extends Mage_Core_Helper_Abstract
{
    /*
     * Return the current category ID
     *
     * @access public
     * @param null
     * @return int
     */
    public function getCurrentCategoryId()
    {
        $category = Mage::registry('current_category');
        $product = Mage::registry('current_product');
        if(!empty($category)) {
            return $category->getId();
        } elseif(!empty($product)) {
            $category_ids = $product->getCategoryIds();
            if(!empty($category_ids)) return (int)$category_ids[0];
        }
        return 0;
    }

    /*
     * Return the current product ID
     *
     * @access public
     * @param null
     * @return int
     */
    public function getCurrentCategoryPath()
    {
        $category = Mage::registry('current_category');
        if(!empty($category)) {
            return $category->getPath();
        }
        return null;
    }

    /*
     * Return the current product ID
     *
     * @access public
     * @param null
     * @return int
     */
    public function getCurrentProductId()
    {
        $product = Mage::registry('current_product');
        if(!empty($product)) {
            return $product->getId();
        }
        return 0;
    }
}
