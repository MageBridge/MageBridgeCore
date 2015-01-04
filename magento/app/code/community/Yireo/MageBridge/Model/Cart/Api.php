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
 * MageBridge API-model for cart resources
 */
class Yireo_MageBridge_Model_Cart_Api extends Mage_Catalog_Model_Api_Resource
{
    /**
     * Return a list of all cart-items
     *
     * @access public
     * @param array $options
     * @return array
     */
    public function items($options = array())
    {
        $cart = Mage::getSingleton('checkout/cart');
        $items = array();
        foreach($cart->getItems() as $item) {
            $items[] = $item->debug();
        }
        return $items;
    }
}
