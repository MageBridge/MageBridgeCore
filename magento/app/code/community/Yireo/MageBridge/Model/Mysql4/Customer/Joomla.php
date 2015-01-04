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
 * MageBridge model for relating a Magento customer ID to a Joomla! ID
 */
class Yireo_MageBridge_Model_Mysql4_Customer_Joomla extends Mage_Core_Model_Mysql4_Abstract
{
    /*
     * Disable the auto_increment behaviour
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('magebridge/customer_joomla', 'customer_id');
    }
}
