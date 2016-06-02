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

require_once 'trait/identifiable.php';
require_once 'trait/formable.php';
require_once 'trait/table.php';

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
	 * Trait to implement table behaviour
	 */
	use YireoModelTraitTable;

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
	 * @deprecated Use $this->getMeta('view') instead
	 */
	protected $_view;

	/**
	 * @var string
	 * @deprecated Use $this->getMeta('option') instead
	 */
	protected $_option;

	/**
	 * @var string
	 * @deprecated Use $this->getMeta('option_id') instead
	 */
	protected $_option_id;

	/**
	 * Constructor
	 *
	 * @param string $tableAlias
	 *
	 * @return mixed
	 */
	public function __construct($tableAlias = null)
	{
		// Call the parent constructor
		$rt = parent::__construct();

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

		$this->setMeta('view', $view);
		$this->setMeta('option', $option);
		$this->setMeta('option_id', $option_id);
		$this->setMeta('component', $component);
		$this->setMeta('frontend_form', false);

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

		$this->_view          = $this->getMeta('view');
		$this->_option        = $this->getMeta('option');
		$this->_option_id     = $this->getMeta('option_id');
		$this->_form_name     = $this->getMeta('form_name');
		$this->_frontend_form = $this->getMeta('frontend_form');
	}

	/**
	 * Method to get data
	 *
	 * @param bool $forceNew
	 *
	 * @return array
	 */
	public function getData($forceNew = false)
	{
		return $this->data;
	}

	/**
	 * Override the default method to allow for skipping table creation
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array  $options
	 *
	 * @return mixed
	 */
	public function getTable($name = '', $prefix = 'Table', $options = array())
	{
		if ($this->skip_table == true)
		{
			return null;
		}

		if (empty($name))
		{
			$name = $this->_tbl_alias;
		}

		if (!empty($this->_tbl_prefix))
		{
			$prefix = $this->_tbl_prefix;
		}

		return parent::getTable($name, $prefix, $options);
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
}
