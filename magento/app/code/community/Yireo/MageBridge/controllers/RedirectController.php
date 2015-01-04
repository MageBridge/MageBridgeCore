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

/**
 * MageBridge output tests
 */
class Yireo_MageBridge_RedirectController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirect to another page
     *
     * @access public
     * @param null
     * @return null
     */
    public function indexAction()
    {
        // Get the redirect URL
        $redirectUrl = $this->getRequest()->getParam('url');
        if(!empty($redirectUrl)) $redirectUrl = base64_decode($redirectUrl);
        if(empty($redirectUrl)) $redirectUrl = $this->_getRefererUrl();

        // Set the redirect URL
        $bridge = Mage::getSingleton('magebridge/core');
        $bridge->setMageConfig('redirect_url', $redirectUrl);

        // Simulate the regular layout
        $this->loadLayout();
        $this->renderLayout();
    }
}
