<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Configurable - allows models to have a configuration
 *
 * @package Yireo
 */
trait YireoModelTraitConfigurable
{
	/**
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * @param mixed $name
	 * @param mixed $value
	 * 
	 * @return $this
	 */
	public function setConfig($name, $value = null)
	{
		if (!is_array($this->config))
		{
			$this->config = array();
		}
		
		if (is_array($name) && empty($value))
		{
			$this->config = $name;

			return $this;
		}

		$this->config[$name] = $value;
		
		return $this;
	}

	/**
	 * @param $name
	 * @param $default
	 *
	 * @return bool|mixed
	 */
	public function getConfig($name = null, $default = false)
	{
		if (empty($name))
		{
			return $this->config;
		}

		if (empty($this->config[$name]))
		{
			return $default;
		}

		return $this->config[$name];
	}
}