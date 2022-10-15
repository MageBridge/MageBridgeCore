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

// Import the loader
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Class YireoRouteQuery
 * Abstraction of $query variables
 */
class YireoRouteQuery
{
	/**
	 * @var array
	 */
	protected $segments = array();

	/**
	 * @var array
	 */
	protected $query = array();

	/**
	 * @var string
	 */
	protected $cacheId = '';

	/**
	 * @return mixed
	 */
	public function getSegmentsFromCache()
	{
		/** @var JCache $cache */
		$cache = $this->getCache();
		$cacheId = $this->getCacheId();
		$segments = $cache->get($cacheId);

		if (is_array($segments) && !empty($segments)) {
			return $segments;
		}
	}

	/**
	 * @return array|null
	 */
	public function getMenuItemsByComponent($componentName)
	{
		static $items = null;

		if (empty($items))
		{
			/** @var JCache $cache */
			$cache = $this->getCache();
			$component = JComponentHelper::getComponent($componentName);
			$cacheId = 'menuitems_' . $component->id;
			$items = $cache->get($cacheId);
		}

		if (empty($items))
		{
			$application = JFactory::getApplication();
			$menu = $application->getMenu();
			$items = $menu->getItems('component_id', $component->id);
			$cache->store($items, $cacheId);
		}

		return $items;
	}

	/**
	 * @return string
	 */
	public function getCacheId()
	{
		if (!empty($this->cacheId))
		{
			return $this->cacheId;
		}

		if (empty($this->query) || !is_array($this->query))
		{
			$this->cacheId = 'empty';
			return 'empty';
		}

		$cacheId = implode('_', $this->query);
		$cacheId = md5($cacheId);
		$this->cacheId = $cacheId;

		return $this->cacheId;
	}

	/**
	 * @return JCacheController
	 */
	private function getCache()
	{
		/** @var JCache $cache */
		$cache = JFactory::getCache('lib_yireo_router', '');
		$cache->setCaching(true);
		$cache->setLifeTime(86400);

		return $cache;
	}

	/**
	 * @return array
	 */
	public function getSegments()
	{
		if (empty($this->segments))
		{
			return $this->segments;
		}

		$cache = $this->getCache();
		$cacheId = $this->getCacheId();
		$cache->store($this->segments, $cacheId);

		return $this->segments;
	}

	/**
	 * @param $value
	 */
	public function addSegment($value)
	{
		$this->segments[] = $value;
	}

	/**
	 * @param $name
	 */
	public function addSegmentFromData($name)
	{
		if ($this->hasValue($name))
		{
			$this->segments[] = $this->getValue($name);
		}
	}

	/**
	 * @param $segments
	 */
	public function setSegments($segments)
	{
		$this->segments = $segments;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->query;
	}

	/**
	 * @param $query
	 */
	public function setData($query)
	{
		$this->query = $query;
	}

	/**
	 * @return mixed
	 */
	public function getItemid()
	{
		if (!empty($this->query['Itemid']))
		{
			return $this->query['Itemid'];
		}
	}

	/**
	 * @param        $name
	 * @param object $item
	 *
	 * @return bool
	 */
	public function hasValue($name, $item = null)
	{
		if (empty($item))
		{
			$item = $this;
		}

		if (empty($item->query[$name]))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $name
	 * @param object $item
	 *
	 * @return mixed
	 */
	public function getValue($name, $item = null)
	{
		if (empty($item))
		{
			$item = $this;
		}

		if (!empty($item->query[$name]))
		{
			return $item->query[$name];
		}

		return false;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function setValue($name, $value)
	{
		$this->query[$name] = $value;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @param object $item
	 *
	 * @return bool
	 */
	public function isValue($name, $value, $item = null)
	{
		if (empty($item))
		{
			$item = $this;
		}

		if (!$itemValue = $this->getValue($name, $item))
		{
			return false;
		}

		if ($itemValue !== $value)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $view
	 * @param object $item
	 *
	 * @return bool
	 */
	public function isView($view, $item = null)
	{
		return $this->isValue('view', $view, $item);
	}

	/**
	 * @param string $task
	 *
	 * @return bool
	 */
	public function isTask($task, $item = null)
	{
		return $this->isValue('task', $task, $item);
	}

	/**
	 * @param $vars
	 */
	public function unsetVars($vars)
	{
		foreach ($vars as $var)
		{
			$this->unsetVar($var);
		}
	}

	/**
	 * @param $var
	 */
	public function unsetVar($var)
	{
		if (isset($this->query[$var]))
		{
			unset($this->query[$var]);
		}
	}

    /**
     * @param $names
     * @param $item
     *
     * @return bool
     */
    public function matchValues($names, $item)
    {
        foreach ($names as $name)
        {
            if ($this->matchValue($name, $item) == false)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $name
     * @param $item
     * @param $type
     * @param $allowEmpty
     *
     * @return bool
     */
    public function matchValue($name, $item, $type = null, $allowEmpty = true)
    {
        if (empty($type) && substr($name, -strlen('_id')) == '_id')
        {
            $type = 'int';
        }

        $currentValue = $this->getValue($name);
        
        if ($allowEmpty === false && empty($currentValue))
        {
            return false;
        }

        if ($type == 'int' && (int) $currentValue === (int) $this->getValue($name, $item))
        {
            return true;
        }

        if ($currentValue == $this->getValue($name, $item))
        {
            return true;
        }

        return false;
    }
}
