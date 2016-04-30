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

/**
 * MageBridge class for the license-block
 */
class Yireo_MageBridge_Block_License extends Mage_Core_Block_Template
{
    /**
     * @var Yireo_MageBridge_Helper_Data
     */
    protected $helper;

    /**
     * @var Mage_Adminhtml_Model_Url
     */
    protected $urlModel;

    /**
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
        $this->setTemplate('magebridge/license.phtml');
        $this->helper = Mage::helper('magebridge');
        $this->urlModel = Mage::getModel('adminhtml/url');
    }

    /**
     * Helper method to get data from the Magento configuration
     *
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->helper->getLicenseKey();
    }

    /**
     * Helper to return the header of this page
     *
     * @param string $title
     *
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - ' . $this->__($title);
    }

    /**
     * Helper to return the menu
     *
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /**
     * Helper to return the save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->urlModel->getUrl('adminhtml/magebridge/save');
    }
}
