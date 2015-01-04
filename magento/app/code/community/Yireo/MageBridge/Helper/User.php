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
 * MageBridge helper for data encryption and decryption
 */
class Yireo_MageBridge_Helper_User extends Mage_Core_Helper_Abstract
{
    /*
     * Load the data mapping the Magento customer to the Joomla! user (and vice versa)
     *
     * @access public
     * @param mixed $select
     * @return array
     */
    public function getUserMap($select = null)
    {
        // Check whether mapping is enabled
        if(Mage::helper('magebridge')->useJoomlaMap() == false) {
            return null;
        }

        // Load primary key
        if(is_int($select)) {
            $map = Mage::getModel('magebridge/customer_joomla');
            $map->load($select);
            return $map->getData();

        // Load by different field
        } elseif(is_array($select)) {

            $collection = Mage::getModel('magebridge/customer_joomla')->getCollection();
            if(!is_object($collection)) {
                return null;
            }

            foreach($select as $name => $value) {
                $collection->addFieldToFilter($name, $value);
            }
            $collection->getSelect()->limit(1);

            $data = $collection->getData();
            if(isset($data[0])) {
                return $data[0];
            }
        }

        return null;
    }

    /*
     * Save the mapping between the Magento customer and the Joomla! user 
     *
     * @access public
     * @param array $data
     * @return bool
     */
    public function saveUserMap($data)
    {
        // Check whether mapping is enabled
        if(Mage::helper('magebridge')->useJoomlaMap() == false) {
            return false;
        }

        // Try to fetch the current mapping
        $map = Mage::getModel('magebridge/customer_joomla');
        if(isset($data['customer_id']) && is_numeric($data['customer_id'])) {
            $map->load($data['customer_id']);
        }

        // Load the new data and save the mapping
        foreach($data as $name => $value) {
            $map->setData($name, $value);
        }

        try {
            $map->save();
        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->trace('Failed to save map', $e->getMessage());
        }

        return true;
    }

    /*
     * Save the mapping between the Magento customer and the Joomla! user 
     *
     * @access public
     * @param array $data
     * @return bool
     */
    public function getCurrentJoomlaId()
    {
        // Check whether mapping is enabled
        if(Mage::helper('magebridge')->useJoomlaMap() == false) {
            return 0;
        }

        // Load the current customer
        $customer = Mage::getModel('customer/session')->getCustomer();

        // Try to fetch the current mapping
        $map = Mage::getModel('magebridge/customer_joomla');
        $map->load($customer->getId());
        if(!empty($map)) {
            return $map->getJoomlaId(); 
        }

        return 0;
    }

    /*
     * Check whether a certain string is an email-address
     *
     * @access public
     * @param string $string
     * @return bool
     */
    public function isEmailAddress($string = null)
    {
        if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $string)) {
            return true;
        }
        return false;
    }
}
