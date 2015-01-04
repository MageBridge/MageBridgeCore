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
 * MageBridge block controller
 */
class Yireo_MageBridge_BlockController extends Mage_Core_Controller_Front_Action
{
    /**
     * View a specific block
     *
     * @access public
     * @param null
     * @return null
     */
    public function viewAction()
    {
        // Example URL: magebridge/block/view/name/cart_sidebar
        $name = $this->getRequest()->getParam('name');

        Mage::getSingleton('core/session', array('name' => 'frontend'));
        $layout = Mage::app()->getLayout();
        $layout->getUpdate()->addHandle('default')->load();
        $layout->generateXml()->generateBlocks();
        
        $block = $layout->getBlock($name);
        if(empty($block)) {
            echo '<!-- empty block "'.$name.'" -->';
        } else {
            echo $block->toHtml();
        }
        exit;
    }
}
