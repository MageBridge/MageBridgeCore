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
 * MageBridge class for the events-block
 */
class Yireo_MageBridge_Block_Settings_Events extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area','adminhtml'); 
        $this->setTemplate('magebridge/settings/events.phtml');
    }

    /*
     * Helper method to get list of all the forwarded events and their current status
     *
     * @access public
     * @param null
     * @return array
     */
    public function getEvents()
    {
        $events = Mage::getModel('magebridge/listener')->getEvents();
        $event_list = array();

        foreach($events as $event) {

            $value = Mage::getStoreConfig('magebridge/settings/event_forwarding/'.$event[0]);
            if(!is_numeric($value)) {
                $value = $event[1];
            }

            $event_list[] = array(
                'name' => $event[0],
                'value' => (int)$value,
                'recommended' => $event[1],
                'group' => $event[2],
            );
        }
        return $event_list;
    }
}
