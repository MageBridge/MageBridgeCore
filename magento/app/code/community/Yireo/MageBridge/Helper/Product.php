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

class Yireo_MageBridge_Helper_Product extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to export a product to the bridge
     *
     * @access public
     * @param Mage_Catalog_Model_Product
     * @return array
     */
    public function export($product, $arguments)
    {
        // Debugging 
        Mage::getSingleton('magebridge/debug')->notice('Exporting product-data: '.$product->getId());

        // Correct the price for Grouped Products, by grabbing the first price (credits to Luke Collymore)
        if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $childProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
            $prices = array();
            foreach ($childProductIds as $ids) {
                foreach ($ids as $id) {
                    $childProduct = Mage::getModel('catalog/product')->load($id);
                    $prices[] = $childProduct->getPriceModel()->getPrice($childProduct);
                }
            }
            sort($prices);
            $product->setPrice(array_shift($prices));
        }

        // Set the custom size
        if(!empty($arguments['custom_image_size'])) {
            $product->setCustomImageSize((int)$arguments['custom_image_size']);
        }

        // Get the debug-array of this object
        $p = $product->debug();

        // Add or alter values
        $p['product_id'] = $p['entity_id'];
        $p['category_ids'] = $product->getCategoryIds();
        $p['label'] = htmlentities($product->getName());
        if($product->getCustomImageSize() > 1) {
            $p['thumbnail'] = (string)Mage::helper('catalog/image')->init($product, 'thumbnail')->resize($product->getCustomImageSize());
            $p['image'] = (string)Mage::helper('catalog/image')->init($product, 'image')->resize($product->getCustomImageSize());
            $p['small_image'] = (string)Mage::helper('catalog/image')->init($product, 'small_image')->resize($product->getCustomImageSize());
        } else {
            $p['thumbnail'] = $product->getThumbnailUrl();
            $p['image'] = $product->getImageUrl();
            $p['small_image'] = $product->getSmallImageUrl();
        }

        // Determine the normal price
        $price = $product->getPrice();
        if($price > 0 == false && $product->getMinimalPrice() > 0) {
            $price = $product->getMinimalPrice();
        }

        // Determine the special price
        $special_price = $product->getSpecialPrice();
        $special_percentage = 0;
        if($special_price > 0 && $product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $special_percentage = $special_price;
            $special_price = $price / 100 * $special_percentage;
        }

        // Get other prices
        try {
            $final_price = $product->getFinalPrice();
            if($final_price == $price) $final_price = false;
        } catch(Exception $e) {
            $final_price = false;
        }

        try {
            $minimal_price = $product->getMinimalPrice();
        } catch(Exception $e) {
            $minimal_price = false;
        }

        // Prices with tax
        $price_tax = Mage::helper('tax')->getPrice($product, $price, true);
        $special_price_tax = Mage::helper('tax')->getPrice($product, $special_price, true);

        // Construct price options
        $p['price'] = Mage::app()->getStore()->formatPrice($price);
        $p['price_raw'] = $price;
        $p['price_tax'] = Mage::app()->getStore()->formatPrice($price_tax);
        $p['price_tax_raw'] = $price_tax;
        $p['price_tier'] = $product->getTierPrice(1);
        $p['special_price'] = Mage::app()->getStore()->formatPrice($special_price);
        $p['special_price_raw'] = $special_price;
        $p['special_price_tax'] = Mage::app()->getStore()->formatPrice($special_price_tax);
        $p['special_price_tax_raw'] = $special_price_tax;
        $p['special_percentage'] = $special_percentage;
        $p['special_from_date'] = $product->getSpecialFromDate();
        $p['special_to_date'] = $product->getSpecialToDate();
        $p['final_price'] = Mage::app()->getStore()->formatPrice($final_price);
        $p['final_price_raw'] = $final_price;
        $p['minimal_price'] = Mage::app()->getStore()->formatPrice($minimal_price);
        $p['minimal_price_raw'] = $minimal_price;
        $p['has_special_price'] = (!empty($p['special_price_raw'])) ? 1 : 0;
        $p['has_final_price'] = (!empty($p['final_price_raw'])) ? 1 : 0;
        $p['has_minimal_price'] = (!empty($p['final_minimal_raw'])) ? 1 : 0;

        // Construct search-options
        if(isset($arguments['search']) && $arguments['search'] == 1) {
            $attributes = $product->getAttributes();
            $search = array();
            foreach($attributes as $attribute){
                if($attribute->getIsSearchable()) {
                    $attributeCode = $attribute->getAttributeCode();
                    $attributeValue = $product->getData($attributeCode);
                    if(!empty($attributeValue)) {
                        $search[$attributeCode] = $attributeValue;
                    }
                }
            }
            $p['search'] = $search;
        }

        // Construct other options
        $p['url_key'] = $product->getUrlKey();
        $p['parent_product_ids'] = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        $p['store'] = $product->getStoreId();

        if(count($p['category_ids']) == 1 && empty($arguments['category_id'])) {
            $arguments['category_id'] = $p['category_ids'][0];
        }

        if(isset($arguments['category_id']) && $arguments['category_id'] > 0) {
            $category = Mage::getModel('catalog/category')->load($arguments['category_id']);
            $p['url'] = $product->getUrlPath($category);
        } else {
            $p['url'] = $product->getProductUrl(false);
        }

        // Unset unwanted values
        unset($p['entity_id']);
        unset($p['entity_type_id']);
        unset($p['attribute_set_id']);

        return $p;
    }
}
