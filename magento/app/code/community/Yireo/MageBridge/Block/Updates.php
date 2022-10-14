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
 * MageBridge class for the updates-block
 */
class Yireo_MageBridge_Block_Updates extends Mage_Core_Block_Template
{
    /**
     * @var Yireo_MageBridge_Model_Update
     */
    protected $updateModel;

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
        $this->setTemplate('magebridge/updates.phtml');
        $this->updateModel = Mage::getSingleton('magebridge/update');
        $this->helper = Mage::helper('magebridge');
        $this->urlModel = Mage::getModel('adminhtml/url');
    }

    /**
     * Helper method to get data from the Magento configuration
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getSetting($key = '')
    {
        static $data;
        if (empty($data)) {
            $data = [
                'license_key' => $this->helper->getLicenseKey(),
                'enabled' => $this->helper->enabled(),
            ];
        }

        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return null;
        }
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
     * Helper to return the update-URL
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->urlModel->getUrl('adminhtml/magebridge/doupdate');
    }

    /**
     * Helper to return the updates-URL
     *
     * @return string
     */
    public function getThisUrl()
    {
        return $this->urlModel->getUrl('adminhtml/magebridge/updates');
    }

    /**
     * Helper to return the current MageBridge version
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->updateModel->getCurrentVersion();
    }

    /**
     * Helper to return the latest MageBridge version
     *
     * @return string
     */
    public function getNewVersion()
    {
        return $this->updateModel->getNewVersion();
    }

    /**
     * Helper to check whether an update is needed or not
     *
     * @return mixed
     */
    public function upgradeNeeded()
    {
        return $this->updateModel->upgradeNeeded();
    }
}
