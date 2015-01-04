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
 * MageBridge API-model for store-group resources
 */
class Yireo_MageBridge_Model_Storegroups_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of store groups
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $groups = Mage::getModel('core/store_group')->getCollection();

        $res = array();
        foreach ($groups as $item) {
            $data['value'] = $item->getData('group_id');
            $data['label'] = $item->getData('name');
            $res[] = $data;
        }
        return $res;
    }
}
