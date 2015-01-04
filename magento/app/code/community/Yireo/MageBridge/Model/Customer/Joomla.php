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
class Yireo_MageBridge_Model_Customer_Joomla extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('magebridge/customer_joomla');
        parent::_construct();
    }
}
