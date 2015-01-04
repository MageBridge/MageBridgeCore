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
 * MageBridge API-model for store-view resources
 */
class Yireo_MageBridge_Model_Storeviews_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of store views
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $views = Mage::getModel('core/store')->getCollection();

        $res = array();
        foreach ($views as $item) {
            $data = array();
            $data['website_id'] = $item->getData('website_id');
            $data['group_id'] = $item->getData('group_id');
            $data['store_id'] = $item->getData('store_id');
            $data['value'] = $item->getData('code');
            $data['label'] = $item->getData('name');
            $res[] = $data;
        }
        return $res;
    }

    /**
     * Retrieve list of store groups and store views
     *
     * @access public
     * @param null
     * @return array
     */
    public function hierarchy()
    {
        $groups = Mage::getModel('core/store_group')->getCollection();
        $views = Mage::getModel('core/store')->getCollection();

        $res = array();
        foreach ($groups as $group) {
            $data['value'] = $group->getData('group_id');
            $data['website'] = $group->getData('website_id');
            $data['label'] = $group->getData('name');
            $data['childs'] = array();

            foreach($views as $view) {
                if($view->getGroupId() == $group->getGroupId()) {
                    $locale = Mage::getStoreConfig('general/locale/code', $view);
                    $child = array(
                        'value' => $view->getData('code'),
                        'label' => $view->getData('name'),
                        'locale' => $locale,
                    );
                    $data['childs'][] = $child;
                }
            }
            $res[] = $data;
        }
        return $res;
    }
}
