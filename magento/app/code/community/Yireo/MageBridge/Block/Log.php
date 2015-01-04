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
 * MageBridge class for the log-block
 */
class Yireo_MageBridge_Block_Log extends Mage_Core_Block_Template
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
        $this->setTemplate('magebridge/log.phtml');
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
        $type = $this->getRequest()->getParam('type');
        return 'MageBridge - '.ucfirst($type).' Log';
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
     * Get the content of a specific file
     *
     * @access public
     * @param null
     * @return string
     */
    public function getContent()
    {
        $type = $this->getRequest()->getParam('type');
        switch($type) {
            case 'system':
                $file = BP.DS.'var'.DS.'log'.DS.'system.log';
                break;
            case 'exception':
                $file = BP.DS.'var'.DS.'log'.DS.'exception.log';
                break;
            default:
                $file = BP.DS.'var'.DS.'log'.DS.'magebridge.log';
                break;
        }

        if(is_readable($file)) {
            $content = @file_get_contents($file);
        } else {
            $content = $file.' does not exists or is not readable';
        }

        return htmlentities($content);
    }

    /*
     * Return the wipelog URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getWipelogUrl($type = null)
    {
        $type = $this->getRequest()->getParam('type');
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/wipelog', array('type' => $type));
    }
}
