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

class Yireo_MageBridge_Helper_Event extends Mage_Core_Helper_Abstract
{
    /*
     * Method to convert an underscore-based event-name to camelcase
     * 
     * @access public
     * @param string $event
     * @return string
     */
    public function convertEventName($event)
    {
        $event_parts = explode('_', $event);
        $event = 'mage';
        foreach($event_parts as $part) {
            $event .= ucfirst($part);
        }

        return $event;
    }

    /*
     * Method that returns address-data as a basic array
     * 
     * @access public
     * @param object $address
     * @return array
     */
    public function getAddressArray($address) 
    { 
        if(empty($address)) return;

        // Small hack to make sure we load the English country-name
        Mage::getSingleton('core/locale')->setLocale('en_US');

        $addressArray[] = array_merge(
            Mage::helper('magebridge/event')->cleanAssoc($address->debug()),
            Mage::helper('magebridge/event')->cleanAssoc(array(
                'country' => $address->getCountryModel()->getName(),
                'is_subscribed' => $address->getIsSubscribed(),
            ))
        );
        
        return $addressArray;
    }

    /*
     * Method that returns customer-data as a basic array
     * 
     * @access public
     * @param object $customer
     * @return array
     */
    public function getCustomerArray($customer) 
    { 
        if(empty($customer)) return;

        // Get the customers addresses
        $addresses = $customer->getAddresses();
        $addressArray = array();
        if(!empty($addresses)) {
            foreach($addresses as $address) {
                $addressArray[] = Mage::helper('magebridge/event')->getAddressArray($address);
            }
        }

        // Get the usermap
        $map = Mage::helper('magebridge/user')->getUserMap(array('customer_id' => $customer->getId(), 'website_id' => $customer->getWebsiteId()));
        $joomla_id = (!empty($map)) ? $map['joomla_id'] : 0;

        // Build the customer array
        $customerArray = array_merge(
            Mage::helper('magebridge/event')->cleanAssoc($customer->debug()), 
            Mage::helper('magebridge/event')->cleanAssoc(array(
				'original_data' => $customer->getOrigData(),
                'customer_id' => $customer->getId(),
                'joomla_id' => $joomla_id,
                'name' => $customer->getName(),
                'addresses' => $addressArray,
                'session' => Mage::getSingleton('magebridge/core')->getMetaData('joomla_session'),
            ))
        );

        if(!empty($customerArray['password'])) {
            $customerArray['password'] = Mage::helper('magebridge/encryption')->encrypt($customerArray['password']);
        }

        return $customerArray;
    }

    /*
     * Method that returns order-data as a basic array
     * 
     * @access public
     * @param object $order
     * @return array
     */
    public function getOrderArray($order) 
    { 
        if(empty($order)) return;

        $products = array();
        foreach ($order->getAllItems() as $item) {
            $product = Mage::helper('magebridge/event')->cleanAssoc(array(
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => $item->getQtyOrdered(),
                'product_type' => $item->getProductType(),
            ));
            $products[] = $product;
        }

        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $orderArray = Mage::helper('magebridge/event')->cleanAssoc($order->debug());
        $orderArray['order_id'] = $order->getId();
        $orderArray['customer'] = Mage::helper('magebridge/event')->getCustomerArray($customer);
        $orderArray['products'] = $products;

        return $orderArray;
    }

    /*
     * Method that returns quote-data as a basic array
     * 
     * @access public
     * @param object $quote
     * @return array
     */
    public function getQuoteArray($quote) 
    { 
        if(empty($quote)) return;

        $products = array();
        foreach ($quote->getAllItems() as $item) {
            $product = Mage::helper('magebridge/event')->cleanAssoc(array(
                'id' => $item->getId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'product_type' => $item->getProductType(),
            ));
            $products[] = $product;
        }

        $quoteArray = Mage::helper('magebridge/event')->cleanAssoc(array(
            'quote_id' => $quote->getId(),
            'quote' => $quote->debug(),
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($quote->getCustomer()),
            'products' => $products,
        ));

        return $quoteArray;
    }

    /*
     * Method that returns user-data as a basic array
     * 
     * @access public
     * @param object $user
     * @return array
     */
    public function getUserArray($user) 
    { 
        if(empty($user)) return;

        $userArray = Mage::helper('magebridge/event')->cleanAssoc(array(
            'user_id' => $user->getId(),
            'user' => $user->debug(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ));

        return $userArray;
    }

    /*
     * Method that returns product-data as a basic array
     * 
     * @access public
     * @param object $product
     * @return array
     */
    public function getProductArray($product) 
    { 
        if(empty($product)) return;

        $productArray = Mage::helper('magebridge/event')->cleanAssoc(array(
            'product_id' => $product->getId(),
            'sku' => $product->getSKU(),
            'name' => $product->getName(),
            'status' => $product->getStatus(),
            'price' => $product->getFinalPrice(),
            'category_id' => $product->getCategoryId(),
            'category_ids' => $product->getCategoryIds(),
            'product_type' => $product->getProductType(),
            'product_url' => $product->getProductUrl(false),
            'images' => $product->getMediaGallery('images'),
            'debug' => $product->debug(),
        ));

        return $productArray;
    }

    /*
     * Method that returns category-data as a basic array
     * 
     * @access public
     * @param object $category
     * @return array
     */
    public function getCategoryArray($category) 
    { 
        if(empty($category)) return;

        $categoryArray = Mage::helper('magebridge/event')->cleanAssoc(array(
            'category_id' => $category->getId(),
            'name' => $category->getName(),
            'debug' => $category->debug(),
        ));

        return $categoryArray;
    }

    /*
     * Helper-method that cleans an associative array to prevent empty values
     * 
     * @access public
     * @param array $assoc
     * @return array
     */
    public function cleanAssoc($assoc)
    {
        if(!empty($assoc)) {
            foreach ($assoc as $name => $value) {
                if(empty($value)) unset($assoc[$name]);
            }
        }
        return $assoc;
    }
}
