<?php
/**
 * PHPUnit parent class for Joomla plugins
 *
 * @author     Jisse Reitsma <jisse@yireo.com>
 * @copyright  Copyright 2017 Jisse Reitsma
 * @license    GNU Public License version 3 or later
 * @link       https://www.yireo.com/
 */

namespace Yireo\Test;

use PHPUnit\Framework\TestCase as ParentTestCase;
use JEventDispatcher, JPlugin;

/**
 * Class PluginCase
 */
class PluginCase extends JoomlaCase
{
	/**
	 * @var string
	 */
	protected $pluginName;

	/**
	 * @var string
	 */
	protected $pluginGroup;

	/**
	 * @var
	 */
	protected $pluginParams = [];

	/**
	 * @return JPlugin
	 */
	protected function getPluginInstance()
	{
		$pluginPath = JPATH_BASE . '/plugins/' . $this->pluginGroup . '/' . $this->pluginName . '/' . $this->pluginName . '.php';
		require_once $pluginPath;

		$dispatcher = JEventDispatcher::getInstance();
		$className = '\\' . $this->getTargetClassName();
		$pluginParams = new \Joomla\Registry\Registry;
		$pluginParams->loadArray($this->pluginParams);

		$plugin     = new $className($dispatcher, array('params' => $pluginParams));

		return $plugin;
	}
}

