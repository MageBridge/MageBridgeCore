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
 * MageBridge API-model for session resources
 */
class Yireo_MageBridge_Model_Session_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Return the data from the shopping cart session
     *
     * @access public
     * @param null
     * @return array
     */
    public function checkout()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cart = Mage::helper('checkout/cart')->getCart();
        
        $data = array();
        $data['cart_url'] = Mage::helper('checkout/url')->getCartUrl();
        $data['subtotal'] = $quote->getSubtotal();
        $data['subtotal_formatted'] = Mage::helper('checkout')->formatPrice($quote->getSubtotal());
        $data['subtotal_inc_tax'] = (int)$quote->getSubtotalInclTax();
        $data['items'] = array();

        $count = 0;
        foreach($cart->getItems() as $item) {

            // Convert this object into an export-array
            $product = Mage::helper('magebridge/product')->export($item['product']);

            // Skip subproducts of Configurable Products
            if(!empty($product['parent_product_ids'])) continue;

            // Add the quantity
            $product['qty'] = $item->getQty();
            $count = $count + $product['qty'];

            // Add this product to the list
            $data['items'][] = $product;
        }

        $data['items_count'] = $count;

        return $data;
    }
}
