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
 * Yireo Model Trait: Identifiable - allows models to have an ID
 *
 * @package Yireo
 */
trait YireoModelTraitFilterable
{
	/**
	 * Boolean to allow for filtering
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('allow_filter') instead
	 */
	protected $_allow_filter = true;
	
	/**
	 * Method to get a filter from the user-state
	 *
	 * @param string $filter
	 * @param string $default
	 * @param string $type
	 * @param string $option
	 *
	 * @return string
	 */
	public function getFilter($filter = '', $default = '', $type = 'cmd', $option = '')
	{
		if ($this->getConfig('allow_filter', true) == false)
		{
			return null;
		}

		$value = $this->app->getUserStateFromRequest($this->getFilterName($filter, $option), 'filter_' . $filter, $default, $type);

		return $value;
	}

	/**
	 * Get the current filter name
	 *
	 * @param      $filter
	 * @param null $option
	 *
	 * @return string
	 */
	public function getFilterName($filter, $option = null)
	{
		if (empty($option))
		{
			$option = $this->getConfig('option_id');
		}

		return $option . 'filter_' . $filter;
	}
	
	/**
	 * Method to set whether filtering is allowed
	 *
	 * @param boolean
	 *
	 * @return null
	 */
	public function setAllowFilter($allowFilter)
	{
		$this->setConfig('allow_filter', (bool) $allowFilter);
	}
}