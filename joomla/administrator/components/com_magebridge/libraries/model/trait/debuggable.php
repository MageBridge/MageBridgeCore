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
trait YireoModelTraitDebuggable
{
	/**
	 * Boolean to allow for debugging
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('debug') instead
	 */
	protected $_debug = false;

	/**
	 * @return bool
	 */
	protected function allowDebug()
	{
		// Enable debugging
		if ($this->params->get('debug', 0) == 1)
		{
			return true;
		}

		if ($this->getConfig('debug'))
		{
			return true;
		}
		
		return false;
	}

	/**
	 * Method to get a debug-message of the latest query
	 *
	 * @return string
	 */
	public function getDbDebug()
	{
		$db = JFactory::getDbo();
		
		return '<pre>' . str_replace('#__', $db->getPrefix(), $db->getQuery()) . '</pre>';
	}
}