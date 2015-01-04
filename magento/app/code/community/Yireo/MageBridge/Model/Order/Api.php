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
 * MageBridge API-model for sales-order resources
 */
class Yireo_MageBridge_Model_Order_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of orders with basic info 
     *
     * @access public
     * @param array $filters
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        $collection = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToSelect('*')
            ->setOrder('created_at', 'desc')
            ->setPageSize(20)
            ->load()
        ;

        // @todo: This does not work, but is still needed: $filter = array( array('title' => array('nlike' => array('%a', '%b'))));
        if (is_array($filters)) {
            try {
                foreach ($filters as $field => $value) {
                    if (isset($this->_filtersMap[$field])) {
                        $field = $this->_filtersMap[$field];
                    }

                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_fault('filters_invalid', $e->getMessage());
            }
        }

        $result = array();
        foreach ($collection as $order) {
            $order->base_grand_total_formatted = $order->formatPrice($order->getBaseGrandTotal());
            $result[] = $order->debug();
        }

        return $result;
    }

    /**
     * Retrieve list of order items
     *
     * @access public
     * @param array $filters
     * @return array
     */
    public function getOrderItems($filters = null, $store = null)
    {
        // Parse the customer-filter if needed
        if(isset($filters['customer_email']) && isset($filters['website_id'])) {
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($filters['website_id']);
            $customer->loadByEmail($filters['customer_email']);
            $filters['customer_id'] = $customer->getId();
        }

        // Apply the customer-filter
        if(isset($filters['customer_id'])) {
            if($filters['customer_id'] > 0 == false) {
                return array();
            }

            $orders = $this->fetchOrders($filters);
            $orderIds = array_keys($orders);
            $orderItems = Mage::getResourceModel('sales/order_item_collection')->addFieldToFilter('order_id', array('IN', $orderIds));

        // Initialize all without customer-filter
        } else {
            $orderItems = Mage::getResourceModel('sales/order_item_collection');
            $orders = $this->fetchOrders();
            $customers = $this->fetchCustomers();
        }

        // Loop through all order-items to construct the return-array
        $result = array();
        foreach ($orderItems as $orderItem) {

            // Construct the return-array
            $row = $orderItem->debug();

            // Add customer-data
            if(isset($orders) && isset($customers)) {
                $orderId = $orderItem->getOrderId();
                if(isset($orders[$orderId])) {
                    $customerId = $orders[$orderId]['customer_id'];
                    $customerEmail = $orders[$orderId]['customer_email'];
                    if(isset($customers[$customerId])) {
                        $customerEmail = $customers[$customerId]['email'];
                    }

                    $row['customer_id'] = $customerId;
                    $row['customer_email'] = $customerEmail;
                }
            }

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Helper method to retrieve a list of orders
     *
     * @access protected
     * @param $filters array
     * @return array
     */
    protected function fetchOrders($filters = null)
    {
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('state', 'complete');

        if(isset($filters['customer_id'])) {
            $collection->addFieldToFilter('customer_id', $filters['customer_id']);
        }

        $orders = array();
        foreach($collection as $item) {
            $orders[$item->getId()] = array(
                'id' => $item->getId(),
                'customer_id' => $item->getData('customer_id'),
                'customer_email' => $item->getData('customer_email'),
            );
        }
        return $orders;
    }

    /**
     * Helper method to retrieve a list of customers
     *
     * @access protected
     * @return array
     */
    protected function fetchCustomers()
    {
        // @todo: Automatically set the website_id filter
        $collection = Mage::getResourceModel('customer/customer_collection');

        $customers = array();
        foreach($collection as $item) {
            $customers[$item->getId()] = array(
                'id' => $item->getId(),
                'email' => $item->getData('email'),
            );
        }
        return $customers;
    }
}
