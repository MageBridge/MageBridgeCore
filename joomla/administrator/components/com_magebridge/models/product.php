<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import Joomla! libraries
jimport('joomla.utilities.date');

/**
 * MageBridge Product model
 */
class MagebridgeModelProduct extends YireoModel
{
	/**
	 * Constructor method
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function __construct()
	{
		$this->_orderby_title = 'label';
		parent::__construct('product');
	}

	/**
	 * Method to store the item
	 *
	 * @package MageBridge
	 * @access public
	 * @param array $data
	 * @return bool
	 */
	public function store($data)
	{
		if (empty($data['label'])) {
			$data['label'] = $data['sku'];
		}

		$data['connector'] = '';
		$data['connector_value'] = '';

		return parent::store($data);
	}
}
