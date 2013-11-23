<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2013
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge API-model for themes
 */
class Yireo_MageBridge_Model_Theme_Api extends Mage_Api_Model_Resource_Abstract
{
    /*
     * Method to get a list of themes
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        return array(
            array('value' => 'default', 'label' => 'Default'),
        );
    }
}
