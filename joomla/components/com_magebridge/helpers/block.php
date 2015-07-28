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
 * Block helper for usage in Joomla!
 */
class MageBridgeBlockHelper
{
	static public function parseBlock($data)
	{
		$formToken = JHtml::_( 'form.token' );
		$data = str_replace('</form>', $formToken . '</form>', $data);

		$replace = array();
		$matches = array();

		if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $data, $matches)) {

			$matches[0] = array_reverse($matches[0]);
			$matches[1] = array_reverse($matches[1]);
			$matches[2] = array_reverse($matches[2]);
			$count = count($matches[1]);

			for ($i = 0; $i < $count; $i++) {
				$attribs = JUtility::parseAttributes($matches[2][$i]);
				$type  = $matches[1][$i];

				if ($type != 'modules') {
					continue;
				}

				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				if (empty($name)) {
					continue;
				}
				unset($attribs['name']);

				jimport('joomla.application.module.helper');
				$modules = JModuleHelper::getModules($name);

				$moduleHtml = null;
				if (!empty($modules)) {
					foreach ($modules as $module) {
						$moduleHtml .= JModuleHelper::renderModule($module, $attribs);
					}
				}

				$data = str_replace($matches[0][$i], $moduleHtml, $data);
			}
		}

		return $data;
	}
}
