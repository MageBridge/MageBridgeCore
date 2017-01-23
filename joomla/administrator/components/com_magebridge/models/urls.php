<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * MageBridge URLs model
 */
class MagebridgeModelUrls extends YireoModel
{
	/**
	 * Constructor method
	 */
	public function __construct()
	{
		$this->_search = array('source', 'destination');
		parent::__construct('url');
	}
}
