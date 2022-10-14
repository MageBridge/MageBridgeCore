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

/*
 * Class containing messages from sessions
 */
class Yireo_MageBridge_Model_Rewrite_Message_Collection extends Mage_Core_Model_Message_Collection
{
    /**
     * Adding new message to collection
     *
     * @param   Mage_Core_Model_Message_Abstract $message
     * @return  Mage_Core_Model_Message_Collection
     */
    public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
        // Only do this for MB, not Magento standalone
        if (Mage::getSingleton('magebridge/core')->getMetaData('enable_messages') == 1) {
            $text = base64_encode($message->getCode());
            switch($message->getType()) {
                case 'error':
                    header('X-MageBridge-Error: '.$text);
                    break;
                case 'warning':
                    header('X-MageBridge-Warning: '.$text);
                    break;
                case 'success':
                case 'notice':
                default:
                    header('X-MageBridge-Notice: '.$text);
                    break;
            }
        }

        // Perform the parent action
        return parent::addMessage($message);
    }

    /**
     * Retrieve messages collection
     *
     * @return Mage_Core_Model_Message_Collection
     */
    public function getItems($type = null)
    {
        $core = Mage::getSingleton('magebridge/core');
        if ($core->getMetaData('enable_messages') == 1) {
            if ($type) {
                return [];
            }
        }
        return parent::getItems($type);
    }
}
