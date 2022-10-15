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

// Require the parent view
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Home View class
 *
 * @package Yireo
 */
class YireoViewHome extends YireoView
{
	/**
	 * Identifier of the library-view
	 */
	protected $_viewParent = 'home';

	/**
	 * @var bool
	 */
	protected $backend_feed;

	/**
	 * Main constructor method
	 *
	 * @param $config array
	 */
	public function __construct($config = array())
	{
		$this->loadToolbar = false;

		// Call the parent constructor
		parent::__construct($config);

		// Load bootstrap
		YireoHelper::bootstrap();

		// Initialize the toolbar
		if (file_exists(JPATH_COMPONENT . '/config.xml'))
		{
			if ($this->user->authorise('core.admin'))
			{
				JToolbarHelper::preferences($this->getConfig('option'), 600, 800);
			}
		}

		// Add the checks
		$this->runChecks();
	}

	/**
	 * Main display method
	 *
	 * @param string $tpl
	 *
	 * @return mixed
	 */
	public function display($tpl = null)
	{
		// Variables
		$document = JFactory::getDocument();

		// Generate the backend feeds
		$this->backend_feed = (bool) $this->params->get('backend_feed', 1);

		if ($this->backend_feed)
		{
			$this->ajax('index.php?option=' . $this->getConfig('option') . '&view=home&format=ajax&layout=feeds', 'latest_news');
			$this->ajax('index.php?option=' . $this->getConfig('option') . '&view=home&format=ajax&layout=promotion', 'promotion');
		}

		// Add additional CSS
		$document->addStyleSheet('https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700');
		$document->addStyleSheet('https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');

		// Get the current version
		$this->current_version = YireoHelper::getCurrentVersion();

		return parent::display($tpl);
	}

	/**
	 * Helper-method to construct a specific icon
	 *
	 * @param string $view
	 * @param string $text
	 * @param string $image
	 * @param string $folder
	 *
	 * @return string
	 */
	public function icon($view, $text, $image, $folder = null, $target = null)
	{
		$image = 'icon-48-' . $image;

		if (empty($folder))
		{
			$folder = '../media/' . $this->getConfig('option') . '/images/';
		}

		if (!file_exists(JPATH_ADMINISTRATOR . '/' . $folder . '/' . $image))
		{
			$folder = '/templates/' . $this->app->getTemplate() . '/images/header/';
		}

		$icon           = array();
		$icon['link']   = JRoute::_('index.php?option=' . $this->getConfig('option') . '&view=' . $view);
		$icon['text']   = JText::_($text);
		$icon['target'] = $target;
		$icon['icon']   = '<img src="' . $folder . $image . '" title="' . $icon['text'] . '" alt="' . $icon['text'] . '" />';

		return $icon;
	}

	/**
	 * Helper-method to set the page title
	 *
	 * @param string $title
	 * @param string $class
	 *
	 * @return void
	 */
	public function setTitle($title = null, $class = 'logo')
	{
		$component_title = YireoHelper::getData('title');
		$title           = JText::_('LIB_YIREO_VIEW_HOME');

		if (file_exists(JPATH_SITE . '/media/' . $this->getConfig('option') . '/images/' . $class . '.png'))
		{
			JToolbarHelper::title($component_title . ': ' . $title, $class);

			return;
		}

		JToolbarHelper::title($component_title . ': ' . $title, 'generic.png');
	}

	/**
	 * Helper-method to add checks to the homepage
	 */
	public function runChecks()
	{
	}
}
