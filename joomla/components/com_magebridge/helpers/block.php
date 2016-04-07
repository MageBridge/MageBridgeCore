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
 * Block helper for usage in Joomla!
 */
class MageBridgeBlockHelper
{
	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	static public function parseBlock($data)
	{
		$formToken = JHtml::_('form.token');
		$data = str_replace('</form>', $formToken . '</form>', $data);

		return $data;
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	static public function parseJdocTags($data)
	{
		$replace = array();
		$matches = array();

		if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $data, $matches))
		{
			$matches[0] = array_reverse($matches[0]);
			$matches[1] = array_reverse($matches[1]);
			$matches[2] = array_reverse($matches[2]);
			$count = count($matches[1]);

			for ($i = 0; $i < $count; $i++)
			{
				$attributes = JUtility::parseAttributes($matches[2][$i]);
				$type = $matches[1][$i];

				if ($type != 'modules')
				{
					continue;
				}

				$name = isset($attributes['name']) ? $attributes['name'] : null;

				if (empty($name))
				{
					continue;
				}

				unset($attributes['name']);
				$moduleHtml = self::getModuleHtml($name, $attributes);;
				$data = str_replace($matches[0][$i], $moduleHtml, $data);
			}
		}

		return $data;
	}

	/**
	 * @param $name
	 * @param $attribs
	 *
	 * @return string
	 */
	static public function getModuleHtml($name, $attributes)
	{
		jimport('joomla.application.module.helper');
		$modules = JModuleHelper::getModules($name);

		if (empty($modules))
		{
			return null;
		}

		$moduleHtml = null;

		foreach ($modules as $module)
		{
			$moduleHtml .= JModuleHelper::renderModule($module, $attributes);
		}

		return $moduleHtml;
	}
}
