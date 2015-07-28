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

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with AJAX lazy-loading
 */
class MageBridgeAjaxHelper
{
	/**
	 * Helper-method to return the right AJAX-URL
	 *
	 * @param mixed $user
	 * @return bool
	 */
	static public function getLoaderImage()
	{
		$template = JFactory::getApplication()->getTemplate();
		if (file_exists(JPATH_SITE.'/templates/'.$template.'/images/com_magebridge/loader.gif')) {
			return 'templates/'.$template.'/images/com_magebridge/loader.gif';
		} else {
			return 'media/com_magebridge/images/loader.gif';
		}
	}

	/**
	 * Helper-method to return the right AJAX-URL
	 *
	 * @param mixed $user
	 * @return bool
	 */
	static public function getUrl($block)
	{
		$url = JURI::root().'index.php?option=com_magebridge&view=ajax&tmpl=component&block='.$block;
		$request = MageBridgeUrlHelper::getRequest();
		if (!empty($request)) $url .= '&request='.$request;
		return $url;
	}

	/**
	 * Helper-method to return the right AJAX-script
	 *
	 * @param string $block
	 * @param string $element
	 * @param string $url
	 * @return bool
	 */
	static public function getScript($block, $element, $url = null)
	{
		// Set the default AJAX-URL
		if (empty($url)) $url = self::getUrl($block);

		// Load ProtoType
		if (MageBridgeTemplateHelper::hasPrototypeJs() == true) {
			$script = "Event.observe(window,'load',function(){new Ajax.Updater('$element','$url',{method:'get'});});";

		// Load jQuery
		} else if (JFactory::getApplication()->get('jquery') == true) {
			$script = "jQuery(document).ready(function(){\n"
				. "	jQuery('#".$element."').load('".$url."');"
				. "});\n"
			;

		// Load jQuery ourselves
		} else {
			YireoHelper::jquery();
			$script = "jQuery(document).ready(function(){\n"
				. "	jQuery('#".$element."').load('".$url."');"
				. "});\n"
			;
		}

		return $script;
	}
}
