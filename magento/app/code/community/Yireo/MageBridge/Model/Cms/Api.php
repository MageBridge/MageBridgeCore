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
 * MageBridge API-model for website resources
 */
class Yireo_MageBridge_Model_Cms_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of pages
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $pages = Mage::getModel('cms/page')->getCollection();

        $res = array();
        foreach ($pages as $item) {
            $data['value'] = $item->getId().':'.$item->getData('identifier');
            $data['label'] = $item->getData('title');
            $res[] = $data;
        }

        return $res;
    }
}
