<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (https://www.yireo.com/)
 * @package   YireoLib
 * @license   GNU Public License
 * @link      https://www.yireo.com/
 */

// Namespace
namespace Yireo\System;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Class Autoloader
 *
 * @package Yireo\System
 */
class Autoloader
{
	/**
	 * Mapping of legacy classes
	 */
	protected $mapping = array(
		'YireoRouteQuery'         => 'route/query',
		'YireoDispatcher'         => 'dispatcher',
		'YireoAbstractModel'      => 'model/abstract',
		'YireoCommonModel'        => 'model/common',
		'YireoDataModel'          => 'model/data',
		'YireoServiceModel'       => 'model/service',
		'YireoCommonView'         => 'view/common',
		'YireoAbstractView'       => 'view/abstract',
		'YireoCommonController'   => 'controller/common',
		'YireoAbstractController' => 'controller/abstract',
	);

	/**
	 * Main autoloading function
	 *
	 * @param $className
	 *
	 * @return bool
	 */
	public function load($className)
	{
		if (stristr($className, 'yireo') === false)
		{
			return false;
		}

		$rt = $this->loadLegacy($className);

		if ($rt === true)
		{
			return true;
		}

		// Try to include namespaced files
		$rt = $this->loadNamespaced($className);

		if ($rt === true)
		{
			return true;
		}

		return false;
	}

	/**
	 * Autoloading function for namespaced classes
	 *
	 * @param $className
	 *
	 * @return bool
	 */
	protected function loadNamespaced($className)
	{
		$prefix   = 'Yireo\\';
		$baseDir = dirname(__DIR__) . '/';
		$len      = strlen($prefix);

		if (strncmp($prefix, $className, $len) !== 0)
		{
			return false;
		}

		$relativeClass = substr($className, $len);

		$filename = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

		if (!file_exists($filename))
		{
			return false;
		}

		include_once $filename;

		return true;
	}

	/**
	 * Autoloading function for legacy classes
	 *
	 * @param $className
	 *
	 * @return bool
	 */
	protected function loadLegacy($className)
	{
		// Preliminary check
		if (substr($className, 0, 5) != 'Yireo')
		{
			return false;
		}

		// Construct the filename
		if (isset($this->mapping[$className]))
		{
			$filename = $this->mapping[$className];
		}
		else
		{
			$className = preg_replace('/^Yireo/', '', $className);
			$pieces = preg_split('/(?=[A-Z])/',$className);
			$path = array();

			foreach ($pieces as $piece)
			{
				$path[] = strtolower($piece);
			}

			$filename = implode('/', $path);
		}

		// Try to determine the needed file
		$filename = dirname(dirname(__DIR__)) . '/' . $filename . '.php';

		if (!file_exists($filename))
		{
			return false;
		}

		include_once $filename;

		return true;
	}
}
