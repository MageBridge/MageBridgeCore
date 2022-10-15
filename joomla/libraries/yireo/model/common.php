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
 * Yireo Common Model
 * Parent class for models that need additional features without JTable functionality
 *
 * @package Yireo
 */
class YireoCommonModel extends YireoAbstractModel
{
	/**
	 * Trait to implement ID behaviour
	 */
	use YireoModelTraitIdentifiable;

	/**
	 * Trait to implement form behaviour
	 */
	use YireoModelTraitFormable;


	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * @var JUser
	 */
	protected $user;

	/**
	 * Data array
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Data array
	 *
	 * @var array
	 * @deprecated Use $this->data instead
	 */
	protected $_data = array();

	/**
	 * @var string
	 * @deprecated Use $this->getConfig('view') instead
	 */
	protected $_view;

	/**
	 * @var string
	 * @deprecated Use $this->getConfig('option') instead
	 */
	protected $_option;

	/**
	 * @var string
	 * @deprecated Use $this->getConfig('option_id') instead
	 */
	protected $_option_id;

	/**
	 * Constructor
	 * 
	 * @param array $config
	 *
	 * @return mixed
	 */
	public function __construct($config = array())
	{
		// Call the parent constructor
		$rt = parent::__construct($config);

		$this->initCommon();

		// Create the component options
		$view      = $this->detectViewName();
		$option    = $this->getOption();
		$option_id = $option . '_' . $view . '_';
		$component = $this->getComponentNameFromOption($option);

		if ($this->app->isSite())
		{
			$option_id .= $this->input->getInt('Itemid') . '_';
		}

		$this->setConfig('view', $view);
		$this->setConfig('option', $option);
		$this->setConfig('option_id', $option_id);
		$this->setConfig('component', $component);
		$this->setConfig('frontend_form', false);
		$this->setConfig('skip_table', true);

		$this->handleCommonDeprecated();

		return $rt;
	}

	/**
	 * @param $option
	 *
	 * @return mixed
	 */
	protected function getComponentNameFromOption($option)
	{
		$component = preg_replace('/^com_/', '', $option);
		$component = preg_replace('/[^A-Z0-9_]/i', '', $component);
		$component = str_replace(' ', '', ucwords(str_replace('_', ' ', $component)));
		
		return $component;
	}

	/**
	 * @return string
	 */
	protected function detectViewName()
	{
		$classParts = explode('Model', get_class($this));
		$view       = (!empty($classParts[1])) ? strtolower($classParts[1]) : $this->input->getCmd('view');

		return $view;
	}

	/**
	 * Inititalize system variables
	 */
	protected function initCommon()
	{
		$this->db   = JFactory::getDbo();
		$this->user = JFactory::getUser();
	}

	/**
	 * Handle deprecated variables
	 */
	protected function handleCommonDeprecated()
	{
		$this->_db   = $this->db;
		$this->_user = $this->user;

		$this->_view          = $this->getConfig('view');
		$this->_option        = $this->getConfig('option');
		$this->_option_id     = $this->getConfig('option_id');
		$this->_frontend_form = $this->getConfig('frontend_form');
	}

	/**
	 * Method to determine the component-name
	 *
	 * @return string
	 */
	protected function getOption()
	{
		if (empty($this->option))
		{
			$classParts   = explode('Model', get_class($this));
			$comPart      = (!empty($classParts[0])) ? $classParts[0] : null;
			$comPart      = preg_replace('/([A-Z])/', '_\\1', $comPart);
			$comPart      = strtolower(preg_replace('/^_/', '', $comPart));
			$option       = (!empty($comPart) && $comPart != 'yireo') ? 'com_' . $comPart : $this->input->getCmd('option');
			$this->option = $option;
		}

		return $this->option;
	}
	
	/**
	 * Method to override the parameters
	 *
	 * @param mixed
	 */
	public function setParams($params = null)
	{
		if (!empty($params))
		{
			$this->params = $params;
		}
	}

	/**
	 * @return \Joomla\Registry\Registry
	 */
	public function getParams()
	{
		return $this->params;
	}
}
