<?php
/**
 * Joomla! component MageBridge
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the loader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Import the general module-helper
jimport('joomla.application.module.helper');

/**
 * Helper for handling the register
 */

class MageBridgeRegisterHelper extends JModuleHelper
{
	/**
	 * Pre-register the modules, because they are loaded after the component output
	 *
	 * @param null
	 * @return null
	 */
	public static function preload()
	{
		// Preload only once
		static $preload = false;

		if ($preload == true)
		{
			return null;
		}
		$preload = true;

		// Don't preload anything if this is the API
		if (MageBridge::isApiPage() == true)
		{
			return null;
		}

		// Don't preload anything if the current output contains only the component-area
		if (in_array(JFactory::getApplication()->input->getCmd('tmpl'), array('component', 'raw')))
		{
			return null;
		}

		// Only preload once
		static $preloaded = false;

		if ($preloaded == false)
		{
			$preloaded = true;
		}

		// Fetch all the current modules
		$modules = MageBridgeModuleHelper::loadMageBridgeModules();
		$register = MageBridgeModelRegister::getInstance();

		// Loop through all the available Joomla! modules
		if (!empty($modules))
		{
			foreach ($modules as $module)
			{
				// Check the name to see if this is a MageBridge-related module
				if (preg_match('/^mod_magebridge/', $module->module))
				{
					// Initialize variables
					$type = null;
					$name = null;

					$params = YireoHelper::toRegistry($module->params);
					$app = JFactory::getApplication();
					$user = JFactory::getUser();

					// Check whether caching returns a valid module-output
					if ($params->get('cache', 0) && JFactory::getConfig()->get('caching'))
					{
						$cache = JFactory::getCache($module->module);
						$cache->setLifeTime($params->get('cache_time', JFactory::getConfig()->get('cachetime') * 60));
						$contents = $cache->get(array('JModuleHelper', 'renderModule'), array(
							$module,
							$params->toArray()), $module->id . $user->get('aid', 0));
						$contents = trim($contents);

						// If the contents are not empty, there is a cached version so we skip this
						if (!empty($contents))
						{
							continue;
						}
					}

					// If the layout is AJAX-ified, do not fetch the block at all
					if ($params->get('layout') == 'ajax')
					{
						continue;
					}

					// Try to include the helper-file
					if (is_file(JPATH_SITE . '/modules/' . $module->module . '/helper.php'))
					{
						$module_file = JPATH_SITE . '/modules/' . $module->module . '/helper.php';
					}
					else
					{
						if (is_file(JPATH_ADMINISTRATOR . '/modules/' . $module->module . '/helper.php'))
						{
							$module_file = JPATH_ADMINISTRATOR . '/modules/' . $module->module . '/helper.php';
						}
					}

					// If there is no module-file, skip this module
					if (empty($module_file) || !is_file($module_file))
					{
						continue;
					}

					// Include the module file
					require_once $module_file;

					// Construct and detect the module-class
					$class = preg_replace('/_([a-z]{1})/', '\1', $module->module) . 'Helper';

					// If the class does not exist, try it with a uppercase-first match
					if (!class_exists($class))
					{
						$class = ucfirst($class);
					}

					// If the class does not exist, skip this module
					if (!class_exists($class))
					{
						continue;
					}

					// Instantiate the class
					$o = new $class();

					// If the register-method does not exist, skip this module
					if (!method_exists($o, 'register'))
					{
						continue;
					}

					MageBridgeModelDebug::getInstance()->notice('Preloading module-resource for ' . $module->module);

					// Fetch the requested tasks
					$requests = $o->register($params);

					if (is_array($requests) && count($requests) > 0)
					{
						foreach ($requests as $request)
						{
							// Add each requested task to the MageBridge register
							if (!empty($request[2]))
							{
								$register->add($request[0], $request[1], $request[2]);
							}
							else
							{
								if (!empty($request[1]))
								{
									$register->add($request[0], $request[1]);
								}
								else
								{
									$register->add($request[0]);
								}
							}

						}
					}
				}
			}
		}
	}
}
