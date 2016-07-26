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
 * Yireo Model Trait: Paginable - allows models to have pagination support
 *
 * @package Yireo
 */
trait YireoModelTraitPaginable
{
	/**
	 * Items total
	 *
	 * @var integer
	 */
	protected $total = null;

	/**
	 * Items total
	 *
	 * @var integer
	 * @deprecated Use $this->total instead
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var JPagination
	 */
	protected $pagination = null;

	/**
	 * Pagination object
	 *
	 * @var JPagination
	 * @deprecated Use $this->pagination instead
	 */
	protected $_pagination = null;

	/**
	 * Method to get the total number of records
	 *
	 * @return int
	 */
	public function getTotal()
	{
		if (!empty($this->total))
		{
			return $this->total;
		}

		// The original database-query did NOT include a LIMIT statement
		if ($this->getConfig('limit_query') == false)
		{
			$data        = $this->getData();
			$this->total = count($data);

			return $this->total;
		}

		if (method_exists($this, 'buildQueryObject'))
		{
			/** @var JDatabaseQuery $query */
			$query = $this->buildQueryObject();
			$query->select('COUNT(*) AS count');
			$query->setLimit(0);
			$query->order($this->getPrimaryKey());
			$data = $this->getDbResult($query, 'object');
			$data = $data->count;
		}

		if (method_exists($this, 'buildQuery'))
		{
			/** @var string $query */
			$query = $this->buildQuery();
			$query = preg_replace('/^(.*)FROM/sm', 'SELECT COUNT(*) FROM', $query);
			$query = preg_replace('/LIMIT(.*)$/', '', $query);
			$query = preg_replace('/ORDER\ BY(.*)$/m', '', $query);
			$data        = $this->getDbResult($query, 'result');
		}

		$this->total = (int) $data;

		return $this->total;
	}

	/**
	 * Method to get a pagination object for the fetched records
	 *
	 * @return JPagination
	 */
	public function getPagination()
	{
		if (!empty($this->pagination))
		{
			return $this->pagination;
		}

		// Make sure the data is loaded
		$this->getData();
		$this->getTotal();

		// Reset pagination if it does not make sense
		if ($this->getState('limitstart') > $this->getTotal())
		{
			$this->setState('limitstart', 0);
			$this->app->setUserState('limitstart', 0);
			$this->getData(true);
		}

		// Build the pagination
		$this->pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));

		return $this->pagination;
	}
}