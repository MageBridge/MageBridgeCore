<?php
/**
 * Joomla! module MageBridge: Widget
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper-class for the module
 */

class ModMageBridgeWidgetHelper
{
	/**
	 * Method to be called as soon as MageBridge is loaded
	 *
	 * @access public
	 * @param JRegistry $params
	 * @return array
	 */
	static public function register($params = null)
	{
		// Get the widget name
		$widgetName = $params->get('widget');

		// Initialize the register
		$register = array();
		$register[] = array('widget', $widgetName);

		if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1)
		{
			$register[] = array('headers');
		}

		return $register;
	}

	/**
	 * Build output for the AJAX-layout
	 *
	 * @access public
	 * @param JRegistry $params
	 * @return string
	 */
	static public function ajaxbuild($params = null)
	{
		// Get the widget name
		$widgetName = $params->get('widget');

		// Include the MageBridge bridge
		$bridge = MageBridgeModelBridge::getInstance();

		// Load CSS if needed
		if ($params->get('load_css', 1) == 1)
		{
			$bridge->setHeaders('css');
		}

		// Load JavaScript if needed
		if ($params->get('load_js', 1) == 1)
		{
			$bridge->setHeaders('js');
		}

		// Load the Ajax script
		$script = MageBridgeAjaxHelper::getScript($widgetName, 'magebridge-' . $widgetName);
		$document = JFactory::getDocument();
		$document->addCustomTag('<script type="text/javascript">' . $script . '</script>');
	}

	/**
	 * Fetch the content from the bridge
	 *
	 * @access public
	 * @param JRegistry $params
	 * @return string
	 */
	static public function build($params = null)
	{
		// Get the widget name
		$widgetName = $params->get('widget');

		// Include the MageBridge bridge
		$bridge = MageBridgeModelBridge::getInstance();

		// Load CSS if needed
		if ($params->get('load_css', 1) == 1)
		{
			$bridge->setHeaders('css');
		}

		// Load JavaScript if needed
		if ($params->get('load_js', 1) == 1)
		{
			$bridge->setHeaders('js');
		}

		// Get the widget
		$widget = $bridge->getWidget($widgetName);

		// Return the output
		return $widget;
	}
}
