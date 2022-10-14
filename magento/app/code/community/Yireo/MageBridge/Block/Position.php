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
 * MageBridge class for the position-block
 */
class Yireo_MageBridge_Block_Position extends Mage_Core_Block_Template
{
    /**
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        if (Mage::helper('magebridge')->isBridge()) {
            $this->setTemplate('magebridge/position.phtml');
        }
    }

    /**
     * Helper method to set the XML-layout position
     *
     * @param string
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Helper method to get the XML-layout position
     *
     * @return string
     */
    public function getPosition()
    {
        $position = $this->position;
        if (empty($position)) {
            $position = $this->getNameInLayout();
        }
        return $position;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (Mage::helper('magebridge')->isBridge()) {
            return parent::_toHtml();
        }

        $result = Mage::getSingleton('magebridge/client')->call('magebridge.position', [$this->getPosition(), $this->getStyle()]);
        return $result;
    }
}
