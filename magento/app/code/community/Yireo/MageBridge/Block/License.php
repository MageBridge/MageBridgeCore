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
 * MageBridge class for the license-block
 */
class Yireo_MageBridge_Block_License extends Mage_Core_Block_Template
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
        $this->setTemplate('magebridge/license.phtml');
    }

    /*
     * Helper method to get data from the Magento configuration
     *
     * @access public
     * @param null
     * @return string
     */
    public function getLicenseKey()
    {
        return Mage::helper('magebridge')->getLicenseKey();
    }

    /*
     * Helper to return the header of this page
     *
     * @access public
     * @param string $title
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - '.$this->__($title);
    }

    /*
     * Helper to return the menu
     *
     * @access public
     * @param null
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /*
     * Helper to return the save URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getSaveUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/save');
    }
}
