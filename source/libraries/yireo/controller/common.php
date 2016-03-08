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
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Common Controller
 *
 * @package Yireo
 */
class YireoCommonController extends YireoAbstractController
{
	/**
	 * Value of the last message
	 *
	 * @protected string
	 */
	protected $msg = '';

	/**
	 * Type of the last message
	 *
	 * @protected string
	 * @values    error|notice|message
	 */
	protected $msg_type = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Define variables
		$this->_app = JFactory::getApplication();
		$this->_application = $this->_app;
		$this->_jinput = $this->_app->input;

		// Add extra model-paths
		$option = $this->_jinput->getCmd('option');

		if ($this->_app->isSite())
		{
			$this->addModelPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/models');
			$this->addModelPath(JPATH_SITE . '/components/' . $option . '/models');
		}
		else
		{
			$this->addModelPath(JPATH_ADMINISTRATOR . '/components/' . $option . '/models');
		}

		// Load additional language-files
		YireoHelper::loadLanguageFile();

		// Call the parent constructor
		parent::__construct();
	}

	/**
	 * @param $option
	 * @param $name
	 *
	 * @return mixed
	 * @throws Exception
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
	 * @throws Exception
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

		throw new Exception(JText::_('LIB_YIREO_NO_CONTROLLER_FOUND'));
	}
}