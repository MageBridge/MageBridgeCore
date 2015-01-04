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
 * MageBridge API-model for websites
 */
class Yireo_MageBridge_Model_Widget_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of widgets
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $widgets = Mage::getModel('widget/widget_instance')->getCollection();

        $result = array();
        foreach($widgets as $widget) {
            $result[] = array(
                'id' => $widget->getId(),
                'name' => $widget->getTitle(),
                'type' => $widget->getType(),
            );
        }
        return $result;
    }
}
