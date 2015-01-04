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
 * MageBridge API-model for website resources
 */
class Yireo_MageBridge_Model_Websites_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of websites
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $websites = Mage::getModel('core/website')->getCollection();

        $res = array();
        foreach ($websites as $item) {
            $data['value'] = $item->getData('website_id');
            $data['label'] = $item->getData('name');

            $data['id'] = $item->getData('website_id');
            $data['name'] = $item->getData('name');
            $data['code'] = $item->getData('code');
            $res[] = $data;
        }

        return $res;
    }
}
