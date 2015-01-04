<?php
/**
 * MageBridgeTheme
 *
 * @author Yireo
 * @package MageBridgeTheme
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

class Yireo_MageBridgeTheme_Helper_Theme extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to get the setting "magebridge/theme/product_image_class"
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getProductImageClass()
    {
        return Mage::getStoreConfig('magebridge/theme/product_image_class');
    }

    /*
     * Helper-method to get the setting "magebridge/theme/product_image_rel"
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getProductImageRelation()
    {
        return Mage::getStoreConfig('magebridge/theme/product_image_rel');
    }

    /*
     * Helper-method to get the setting "magebridge/theme/product_image_size"
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getProductImageSize()
    {
        return Mage::getStoreConfig('magebridge/theme/product_image_size');
    }

    /*
     * Helper-method to get the setting "magebridge/theme/product_thumb_size"
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getProductThumbSize()
    {
        return Mage::getStoreConfig('magebridge/theme/product_thumb_size');
    }

    /*
     * Helper-method to get the setting "magebridge/theme/product_image_max_size"
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getProductImageMaxSize()
    {
        return Mage::getStoreConfig('magebridge/theme/product_image_max_size');
    }

    /*
     * Helper-method to get subcategories of a specific category
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getCategoryChilds()
    {
        $category = Mage::registry('current_category');
        $subcategoryIds = (!empty($category) && $category->getId() > 0) ? $category->getData('children') : false;
        $subcategoryIds = explode(',', $subcategoryIds);

        $subcategories = array();
        if(!empty($subcategoryIds)) {
            foreach ($subcategoryIds as $subcategoryId) {
                $subcategory = Mage::getModel('catalog/category')->load($subcategoryId);
                if($subcategory->getId() > 0 && $subcategory->getData('is_active') == 1) {
                    $subcategories[] = $subcategory;
                }
            }
        }
        return $subcategories;
    }
}
