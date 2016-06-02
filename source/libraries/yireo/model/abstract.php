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
 * Yireo Abstract Model
 * Parent class to easily maintain backwards compatibility
 *
 * @package Yireo
 */
class YireoAbstractModel extends JModelLegacy
{
	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JApplicationCms
	 * @deprecated Use $this->app instead
	 */
	protected $application;

	/**
	 * @var JInput
	 */
	protected $input;

	/**
	 * @var JInput
	 * @deprecated Use $this->input instead
	 */
	protected $jinput;

	/**
	 * @var JInput
	 * @deprecated Use $this->input instead
	 */
	protected $_input;

	/**
	 * @var array
	 */
	protected $meta = array();

	/**
	 * Constructor
	 * 
	 * @param array $config
	 * @param JApplication $app
	 * @param JInput $input
	 *
	 * @return mixed
	 */
	public function __construct($config = array(), $app = null, $input = null)
	{
		$rt = parent::__construct($config);
		
		if (empty($app))
		{
			$app = JFactory::getApplication();
		}

		if (empty($input))
		{
			$input = $app->input;
		}

		$this->app   = $app;
		$this->input = $input;
		
		$this->handleAbstractDeprecated();

		return $rt;
	}

	/**
	 * Handle deprecated variables
	 */
	protected function handleAbstractDeprecated()
	{
		$this->application = $this->app;
		$this->_input      = $this->input;
		$this->jinput      = $this->input;
	}

	/**
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function setMeta($name, $value)
	{
		$this->meta[$name] = $value;
	}

	/**
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public function getMeta($name)
	{
		if (empty($this->meta[$name]))
		{
			return false;
		}

		return $this->meta[$name];
	}
	
	/**
	 * @return JApplicationCms
	 */
	public function getApp()
	{
		return $this->app;
	}

	/**
	 * @param JApplicationCms $app
	 */
	public function setApp($app)
	{
		$this->app = $app;
	}

	/**
	 * @return JInput
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * @param JInput $input
	 */
	public function setInput($input)
	{
		$this->input = $input;
	}
}
