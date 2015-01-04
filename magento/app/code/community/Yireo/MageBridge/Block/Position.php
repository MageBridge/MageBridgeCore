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
 * MageBridge class for the position-block
 */
class Yireo_MageBridge_Block_Position extends Mage_Core_Block_Template
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
        if(Mage::helper('magebridge')->isBridge()) {
            $this->setTemplate('magebridge/position.phtml');
        }
    }

    /*
     * Helper method to set the XML-layout position
     *
     * @access public
     * @param string
     * @return null
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /*
     * Helper method to get the XML-layout position
     *
     * @access public
     * @param null
     * @return string
     */
    public function getPosition()
    {
        $position = $this->position;
        if(empty($position)) {
            $position = $this->getNameInLayout();
        }
        return $position;
    }

    /**
     * Render block HTML
     *
     * @access public
     * @param null
     * @return string
     */
    protected function _toHtml()
    {
        if(Mage::helper('magebridge')->isBridge()) {
            return parent::_toHtml();
        }
        
        $result = Mage::getSingleton('magebridge/client')->call('magebridge.position', array($this->getPosition(), $this->getStyle()));
        return $result;
    }
}
