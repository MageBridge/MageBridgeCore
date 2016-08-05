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
trait YireoModelTraitIdentifiable
{
	/**
	 * Unique id
	 *
	 * @var int
	 */
	protected $id = 0;
	
	/**
	 * Unique id
	 *
	 * @var int
	 * @deprecated Use $this->id instead
	 */
	protected $_id = 0;

	/**
	 * @return int
	 */
	public function getId()
	{
		return (int) $this->id;
	}

	/**
	 * @param int $id
	 * @param bool $reInitialize
	 *
	 * @return $this
	 */
	public function setId($id, $reInitialize = true)
	{
		$this->id = $id;

		if ($reInitialize)
		{
			$this->data = null;
		}

		return $this;
	}
}