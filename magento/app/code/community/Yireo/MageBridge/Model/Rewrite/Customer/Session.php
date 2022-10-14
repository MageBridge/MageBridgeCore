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
 * Rewrite of customer session
 */
class Yireo_MageBridge_Model_Rewrite_Customer_Session extends Mage_Customer_Model_Session
{
    /*
     * Rewrite method
     *
     * @access public
     * @return object
     */
    public function regenerateSessionId()
    {
        if (Mage::helper('magebridge')->isBridge() == false) {
            return parent::regenerateSessionId();
        }

        return $this;
    }
}
