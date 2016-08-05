<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

/*
 * MageBridge model for relating a Magento customer ID to a Joomla! ID
 */
class Yireo_MageBridge_Model_Mysql4_Customer_Joomla_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('magebridge/customer_joomla');
    }
}
