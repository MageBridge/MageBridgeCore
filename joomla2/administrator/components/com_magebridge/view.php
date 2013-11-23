<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeView extends YireoView
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Add CSS-code
        $this->addCss('backend.css', 'media/com_magebridge/css/');
        if (MageBridgeHelper::isJoomla25()) $this->addCss('backend-j25.css', 'media/com_magebridge/css/');
        if (MageBridgeHelper::isJoomla35()) $this->addCss('backend-j35.css', 'media/com_magebridge/css/');

        // If we detect the API is down, report it
        $bridge = MageBridgeModelBridge::getInstance();
        if ($bridge->getApiState() != null) {

            $message = null;
            switch(strtoupper($bridge->getApiState())) {

                case 'EMPTY METADATA':
                    $message = JText::_('The bridge-data arrived empty in Magento.' );
                    break;

                case 'SUPPORTKEY FAILED':
                    $message = JText::sprintf('The Joomla! support-key is different from the one in Magento (%s).', $bridge->getApiExtra());
                    break;

                case 'AUTHENTICATION FAILED':
                    $message = JText::_('API authentication failed. Please check your API-user and API-key.' );
                    break;

                case 'INTERNAL ERROR':
                    $help = MageBridgeHelper::getHelpText('troubleshooting');
                    $message = JText::sprintf('Bridge encountered a 500 Internal Server Error. Please check out the %s for more information.', $help );
                    break;

                case 'FAILED LOAD':
                    $help = MageBridgeHelper::getHelpText('faq-troubleshooting:api-widgets');
                    $message = JText::sprintf('Failed to load API-widgets. Please check out the %s for more information.', $help );
                    break;

                default:
                    $message = JText::_('An API-error occurred: '.$bridge->getApiState());
                    break;
            }

            MageBridgeModelDebug::getInstance()->feedback($message);
        }

        // If debugging is enabled report it
        if (MagebridgeModelConfig::load('debug') == 1 && JRequest::getCmd('tmpl') != 'component' && in_array(JRequest::getCmd('view'), array('config', 'home'))) {
            MageBridgeModelDebug::getInstance()->feedback('Debugging is currently enabled');
        }

        parent::display($tpl);
    }
}
