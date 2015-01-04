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
 * MageBridge class for the updates-block
 */
class Yireo_MageBridge_Block_Updates extends Mage_Core_Block_Template
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
        $this->setTemplate('magebridge/updates.phtml');
    }

    /*
     * Helper method to get data from the Magento configuration
     *
     * @access public
     * @param string $key
     * @return string|null
     */
    public function getSetting($key = '')
    {
        static $data;
        if(empty($data)) {
            $data = array(
                'license_key' => Mage::helper('magebridge')->getLicenseKey(),
                'enabled' => Mage::helper('magebridge')->enabled(),
            );
        }

        if(isset($data[$key])) {
            return $data[$key];
        } else {
            return null;
        }
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
     * Helper to return the update-URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getUpdateUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/doupdate');
    }

    /*
     * Helper to return the updates-URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getThisUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/updates');
    }

    /*
     * Helper to return the current MageBridge version
     *
     * @access public
     * @param null
     * @return string
     */
    public function getCurrentVersion()
    {
        return Mage::getSingleton('magebridge/update')->getCurrentVersion();
    }

    /*
     * Helper to return the latest MageBridge version
     *
     * @access public
     * @param null
     * @return string
     */
    public function getNewVersion()
    {
        return Mage::getSingleton('magebridge/update')->getNewVersion();
    }

    /*
     * Helper to check whether an update is needed or not
     *
     * @access public
     * @param null
     * @return mixed
     */
    public function upgradeNeeded()
    {
        return Mage::getSingleton('magebridge/update')->upgradeNeeded();
    }
}
