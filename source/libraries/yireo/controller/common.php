<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (https://www.yireo.com/)
 * @package   YireoLib
 * @license   GNU Public License
 * @link      https://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Common Controller
 *
 * @package Yireo
 */
class YireoCommonController extends YireoAbstractController
{
	/**
	 * @var JApplicationWeb
	 */
	protected $app;

	/**
	 * @var JApplicationWeb
	 * @deprecated
	 */
	protected $_app;

	/**
	 * @var JApplicationWeb
	 * @deprecated
	 */
	protected $_application;

	/**
	 * @var JInput
	 */
	protected $input;

	/**
	 * @var JInput
	 * @deprecated
	 */
	protected $_jinput;

	/**
	 * Value of the last message
	 *
	 * @var string
	 */
	protected $msg = '';

	/**
	 * Type of the last message
	 *
	 * @var string
	 * @values    error|notice|message
	 */
	protected $msg_type = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Define variables
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;

		// Add model paths
		$this->addModelPaths();

		// Load additional language-files
		YireoHelper::loadLanguageFile();

		// Call the parent constructor
		parent::__construct();
	}

	/**
	 * Handle legacy calls
	 */
	protected function handleLegacy()
	{
		$this->_app = $this->app;
		$this->_application = $this->app;
		$this->_jinput = $this->input;

		parent::handleLegacy();
	}

	/**
	 * Add model paths for either backend or frontend
	 */
	protected function addModelPaths()
	{
		// Add extra model-paths
		$option = $this->input->getCmd('option');

		if ($this->app->isSite())
		{
			$this->addModelPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/models');
			$this->addModelPath(JPATH_SITE . '/components/' . $option . '/models');
			
			return null;
		}

		$this->addModelPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/models');
		
		return null;
	}

	/**
	 * @param $option
	 * @param $name
	 *
	 * @return mixed
	 * @throws \Yireo\Exception\Controller\NotFound
	 */
	static public function getControllerInstance($option, $name)
	{
		// Check for a child controller
		if (is_file(JPATH_COMPONENT . '/controllers/' . $name . '.php'))
		{
			require_once JPATH_COMPONENT . '/controllers/' . $name . '.php';

			$controllerClass = ucfirst($option) . 'Controller' . ucfirst($name);

			if (class_exists($controllerClass))
			{
				$controller = new $controllerClass;

				return $controller;
			}
		}

		return self::getDefaultControllerInstance($option, $name);
	}

	/**
	 * @param $option
	 * @param $name
	 *
	 * @return mixed
	 * @throws \Yireo\Exception\Controller\NotFound
	 */
	static public function getDefaultControllerInstance($option, $name)
	{
		// Require the base controller
		if (is_file(JPATH_COMPONENT . '/controller.php'))
		{
			require_once JPATH_COMPONENT . '/controller.php';
		}

		$controllerClass = ucfirst($option) . 'Controller';

		if (class_exists($controllerClass))
		{
			$controller = new $controllerClass;

			return $controller;
		}

		throw new \Yireo\Exception\Controller\NotFound(JText::_('LIB_YIREO_NO_CONTROLLER_FOUND'));
	}
}