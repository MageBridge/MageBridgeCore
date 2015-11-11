<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
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
	/**
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
					$message = JText::_('COM_MAGEBRIDGE_VIEW_API_ERROR_EMPTY_METADATA');
					break;

				case 'SUPPORTKEY FAILED':
					$message = JText::sprintf('COM_MAGEBRIDGE_VIEW_API_ERROR_KEY_FAILED', $bridge->getApiExtra());
					break;

				case 'AUTHENTICATION FAILED':
					$message = JText::_('COM_MAGEBRIDGE_VIEW_API_ERROR_AUTHENTICATION_FAILED' );
					break;

				case 'INTERNAL ERROR':
					$message = JText::sprintf('COM_MAGEBRIDGE_VIEW_API_ERROR_INTERNAL_ERROR', MageBridgeHelper::getHelpLink('troubleshooting'));
					break;

				case 'FAILED LOAD':
					$message = JText::sprintf('COM_MAGEBRIDGE_VIEW_API_ERROR_FAILED_LOAD', MageBridgeHelper::getHelpLink('faq-troubleshooting:api-widgets'));
					break;

				default:
					$message = JText::sprintf('COM_MAGEBRIDGE_VIEW_API_ERROR_GENERIC', $bridge->getApiState());
					break;
			}

			MageBridgeModelDebug::getInstance()->feedback($message);
		}

		// If debugging is enabled report it
		if (MagebridgeModelConfig::load('debug') == 1 && JFactory::getApplication()->input->getCmd('tmpl') != 'component' && in_array(JFactory::getApplication()->input->getCmd('view'), array('config', 'home'))) {
			MageBridgeModelDebug::getInstance()->feedback('COM_MAGEBRIDGE_VIEW_API_DEBUGGING_ENABLED');
		}

		parent::display($tpl);
	}
}
