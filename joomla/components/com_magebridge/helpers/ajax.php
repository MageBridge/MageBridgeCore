<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
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
	 *
	 * @return bool
	 */
	static public function getLoaderImage()
	{
		$app = JFactory::getApplication();
		$template = $app->getTemplate();

		if (file_exists(JPATH_SITE . '/templates/' . $template . '/images/com_magebridge/loader.gif'))
		{
			return 'templates/' . $template . '/images/com_magebridge/loader.gif';
		}

		return 'media/com_magebridge/images/loader.gif';
	}

	/**
	 * Helper-method to return the right AJAX-URL
	 *
	 * @param mixed $user
	 *
	 * @return bool
	 */
	static public function getUrl($block)
	{
		$url = JURI::root() . 'index.php?option=com_magebridge&view=ajax&tmpl=component&block=' . $block;
		$request = MageBridgeUrlHelper::getRequest();

		if (!empty($request))
		{
			$url .= '&request=' . $request;
		}

		return $url;
	}

	/**
	 * Helper-method to return the right AJAX-script
	 *
	 * @param string $block
	 * @param string $element
	 * @param string $url
	 *
	 * @return bool
	 */
	static public function getScript($block, $element, $url = null)
	{
		$app = JFactory::getApplication();

		// Set the default AJAX-URL
		if (empty($url))
		{
			$url = self::getUrl($block);
		}

		if (MageBridgeTemplateHelper::hasPrototypeJs() == true)
		{
			return "Event.observe(window,'load',function(){new Ajax.Updater('$element','$url',{method:'get'});});";
		}

		if ($app->get('jquery') == true)
		{
			return "jQuery(document).ready(function(){\n" . "	jQuery('#" . $element . "').load('" . $url . "');" . "});\n";
		}

		YireoHelper::jquery();

		return "jQuery(document).ready(function(){\n" . "	jQuery('#" . $element . "').load('" . $url . "');" . "});\n";
	}
}
