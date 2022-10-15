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
 * Yireo Model Trait: Formable - allows models to have a form
 *
 * @package Yireo
 */
trait YireoModelTraitLimitable
{
	/**
	 * List limit
	 *
	 * @var int
	 * @deprecated Use $this->getState('limit') instead
	 */
	protected $_limit = null;

	/**
	 * Limit start
	 *
	 * @var int
	 * @deprecated Use $this->getState('limitstart') instead
	 */
	protected $_limitstart = null;

	/**
	 * Enable the limit in the query (or in the data-array)
	 *
	 * @var string
	 * @deprecated Use $this->getConfig('limit_query') instead 
	 */
	protected $_limit_query = false;

	/**
	 * Method to initialize the limit parameter
	 *
	 * @param string $limit
	 */
	public function initLimit($limit = null)
	{
		if (empty($limit))
		{
			$limit = $this->getFilter('list_limit');
		}

		if (empty($limit))
		{
			$limit = $this->app->getUserStateFromRequest($this->getFilterName('limit'), 'list_limit', $this->app->getCfg('list_limit'), 'int');
		}

		$this->setState('limit', $limit);
	}

	/**
	 * Method to initialize the limitstart parameter\
	 *
	 * @param string $limitStart
	 */
	public function initLimitstart($limitStart = null)
	{
		if (is_numeric($limitStart) === false)
		{
			if ($this->app->isSite())
			{
				$limitStart = $this->app->input->getInt('limitstart', 0);
			}
			else
			{
				$limitStart = $this->app->getUserStateFromRequest($this->getFilterName('limitstart'), 'limitstart', 0, 'int');
			}
		}

		$this->setState('limitstart', $limitStart);
	}

	/**
	 * Reset the limit parameters
	 */
	public function resetLimits()
	{
		$this->setState('limitstart', 0);
		$this->setState('limit', 0);
	}
	
	/**
	 * Method to set whether the query should use LIMIT or not
	 *
	 * @param boolean
	 *
	 * @return null
	 */
	public function setLimitQuery($bool)
	{
		$this->setConfig('limit_query', (bool) $bool);
	}
}