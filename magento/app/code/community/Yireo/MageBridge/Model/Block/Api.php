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
 * MageBridge API-model for blocks
 */
class Yireo_MageBridge_Model_Block_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of blocks
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $blocks = Mage::getModel('block/block')->getCollection();

        $result = $this->getKnownBlocks();
        foreach($blocks as $block) {
            $result[$block->getName()] = array(
                'name' => $block->getName(),
                'description' => $block->getName(),
            );
        }
        return $result;
    }

    /**
     * Retrieve list of widgets
     *
     * @access public
     * @param null
     * @return array
     */
    public function getKnownBlocks()
    {
        return array(
            'left' => array('description' => 'Structural column on the left'),
            'right' => array('description' => 'Structural column on the right'),
            'content' => array('description' => 'Main content-block'),
        );
    }
}
