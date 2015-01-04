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
 * MageBridge API-model for customer resources
 */
class Yireo_MageBridge_Model_Customer_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of customers with basic info 
     *
     * @access public
     * @param array $arguments
     * @return array
     */
    public function items($arguments = null)
    {
        // Initialize the collection
        $collection = Mage::getModel('customer/customer')->getCollection()
            ->addAttributeToSelect('*')
        ;

        // Handle a simple email-filter
        if(isset($arguments['emails']) && is_array($arguments['emails']) && !empty($arguments['emails'])) {
            $collection->addFieldToFilter('email', $arguments['emails']);
        }

        // Apply filters
        if(isset($arguments['filters']) && is_array($arguments['filters']) && !empty($arguments['filters'])) {
            try {
                foreach ($arguments['filters'] as $field => $value) {
                    $collection->addFieldToFilter($field, $value);
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Invalid search filter', $e->getMessage());
            }
        }

        // Add a list limit
        if(isset($arguments['count'])) {
            $collection->setPageSize($arguments['count']);
        }

        // Add a page number
        if(isset($arguments['page']) && $arguments['page'] > 0) {
            $collection->setCurPage($arguments['page']);
        }

        // Set ordering
        if(isset($arguments['ordering'])) {
            switch($arguments['ordering']) {
                case 'newest':
                    $collection->setOrder('created_at', 'desc');
                    break;
                case 'oldest':
                    $collection->setOrder('created_at', 'asc');
                    break;
            }
        }

        // Load the actual collection
        $collection->load();

        // Parse the result
        $result = array();
        foreach ($collection as $customer) {
            $customer->setName($customer->getName());
            $result[] = $customer->debug();
        }
        return $result;
    }
}
