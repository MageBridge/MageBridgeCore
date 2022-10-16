<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Main bridge class
 */
class MageBridgeModelBridgeMessages extends MageBridgeModelBridgeSegment
{
    /**
     * Singleton
     *
     * @param string $name
     * @return object
     */
    public static function getInstance($name = null)
    {
        return parent::getInstance('MageBridgeModelBridgeMessages');
    }

    /**
     * Load the data from the bridge
     */
    public function getResponseData()
    {
        return MageBridgeModelRegister::getInstance()->getData('messages');
    }

    /**
     * Method to set the messages
     */
    public function setMessages()
    {
        if (MageBridgeModelConfig::load('enable_messages') == 0) {
            return false;
        }

        $messages = $this->getResponseData();
        if (!empty($messages) && is_array($messages)) {
            $application = JFactory::getApplication();
            foreach ($messages as $message) {
                if (!is_array($message)) {
                    continue;
                }

                switch($message['type']) {
                    case 'warning':
                        $type = 'warning';
                        break;
                    case 'error':
                        $type = 'error';
                        break;
                    default:
                        $type = 'message';
                        break;
                }

                $application->enqueueMessage($message['message'], $type);
            }
        }

        return true;
    }
}
