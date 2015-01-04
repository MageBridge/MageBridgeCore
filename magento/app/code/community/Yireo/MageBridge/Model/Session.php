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
 * MageBridge model for importing and exporting data out of a session
 */
class Yireo_MageBridge_Model_Session extends Mage_Core_Model_Session_Abstract
{
    /*
     * Constructor
     *
     * @access public
     * @param null
     * @return null
     */
    public function __construct()
    {
        $this->init('magebridge');
    }

    /*
     * Method to add an event to the bridge-session
     *
     * @access public
     * @param string $group
     * @param string $event
     * @param mixed $arguments
     * @return null
     */
    public function addEvent($group, $event, $arguments)
    {
        $events = $this->getData('events');
        if(empty($events)) $events = array();

        $events[] = array(
            'type' => 'magento',
            'group' => $group,
            'event' => $event,
            'arguments' => $arguments,
        );
        $this->setData('events', $events);
    }

    /*
     * Method to get all the current events registered in the bridge-session
     *
     * @access public
     * @param null
     * @return array
     */
    public function getEvents()
    {
        return $this->getData('events');
    }

    /*
     * Method to remove al the events registered in the bridge-session
     *
     * @access public
     * @param null
     * @return null
     */
    public function cleanEvents()
    {
        $this->setData('events');
    }
}
