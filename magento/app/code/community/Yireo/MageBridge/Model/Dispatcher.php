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
 * MageBridge model serving as dispatcher for Joomla! events in Magento
 */
class Yireo_MageBridge_Model_Dispatcher
{
    /*
     * Method to fire a Joomla! event sent through the bridge
     *
     * @access public
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public function getResult($name, $arguments = null)
    {
        // Construct the event
        $event = 'joomla'.ucfirst($name);
        // Only continue if this event is listed here
        if (in_array($event, $this->getEvents())) {
            // Throw the event and return the result
            return Mage::dispatchEvent($event, $arguments);
        }
        return false;
    }

    /*
     * Method to return all the allowed Joomla! events
     *
     * @access public
     * @param null
     * @return array
     */
    public function getEvents()
    {
        return [
            'onAuthenticate',
            'onPrepareContent',
            'onAfterDisplayTitle',
            'onBeforeDisplayContent',
            'onAfterDisplayContent',
            'onBeforeContentSave',
            'onAfterContentSave',
            'onSearch',
            'onSearchAreas',
            'onAfterInitialise',
            'onAfterRender',
            'onLoginFailure',
            'onBeforeStoreUser',
            'onAfterStoreUser',
            'onBeforeDeleteUser',
            'onAfterDeleteUser',
            'onLoginUser',
            'onLogoutUser',
        ];
    }
}
