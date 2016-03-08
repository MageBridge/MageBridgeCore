<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.1
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Includes from Joomla! Framework
jimport('joomla.filter.output');

// Include the loader
require_once dirname(__FILE__) . '/../loader.php';

/**
 * Yireo Common View
 *
 * @package Yireo
 */
class YireoCommonView extends YireoAbstractView
{
	/**
	 * Array of template-paths to look for layout-files
	 */
	protected $templatePaths = array();

	/**
	 * Flag to determine whether this view is a single-view
	 */
	protected $_single = null;

	/**
	 * Identifier of the library-view
	 */
	protected $_viewParent = 'default';

	/**
	 * Default task
	 */
	protected $_task = null;

	/**
	 * Main constructor method
	 *
	 * @subpackage Yireo
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		// Call the parent constructor
		parent::__construct($config);

		// Import use full variables from JFactory
		$this->db = JFactory::getDBO();
		$this->uri = JFactory::getURI();
		$this->document = JFactory::getDocument();
		$this->user = JFactory::getUser();
		$this->application = JFactory::getApplication();
		$this->jinput = $this->application->input;

		// Create the namespace-variables
		$this->_view = (!empty($config['name'])) ? $config['name'] : $this->jinput->getCmd('view', 'default');
		$this->_option = (!empty($config['option'])) ? $config['option'] : $this->jinput->getCmd('option');
		$this->_name = $this->_view;
		$this->_option_id = $this->_option . '_' . $this->_view . '_';

		if ($this->application->isSite())
		{
			$this->_option_id .= $this->jinput->getInt('Itemid') . '_';
		}

		// Load additional language-files
		YireoHelper::loadLanguageFile();
	}

	/**
	 * Helper-method to set the page title
	 *
	 * @subpackage Yireo
	 *
	 * @param string $title
	 *
	 * @return null
	 */
	protected function setTitle($title = null, $class = 'logo')
	{
		$component_title = YireoHelper::getData('title');

		if (empty($title))
		{
			$views = YireoHelper::getData('views');

			if (!empty($views))
			{
				foreach ($views as $view => $view_title)
				{
					if ($this->_view == $view)
					{
						$title = JText::_($this->jinput->getCmd('option') . '_VIEW_' . $view);
						break;
					}
				}
			}
		}

		if ($this->_single)
		{
			$pretext = ($this->isEdit()) ? JText::_('LIB_YIREO_VIEW_EDIT') : JText::_('LIB_YIREO_VIEW_NEW');
			$title = $pretext . ' ' . $title;
		}

		if (file_exists(JPATH_SITE . '/media/' . $this->_option . '/images/' . $class . '.png'))
		{
			JToolBarHelper::title($component_title . ': ' . $title, $class);
		}
		else
		{
			JToolBarHelper::title($component_title . ': ' . $title, 'generic.png');
		}

		return;
	}

	/**
	 * Helper-method to set the page title
	 *
	 * @subpackage Yireo
	 *
	 * @return null
	 */
	public function setMenu()
	{
		$menuitems = YireoHelper::getData('menu', $this->_option);

		if (!empty($menuitems))
		{
			foreach ($menuitems as $view => $title)
			{
				if (strstr($view, '|'))
				{
					$v = explode('|', $view);
					$view = $v[0];
					$layout = $v[1];
				}
				else
				{
					$layout = null;
				}

				$titleLabel = strtoupper($this->_option) . '_VIEW_' . strtoupper($title);

				if (is_dir(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/views/' . $view))
				{
					if ($this->_view == $view && $this->jinput->getCmd('layout') == $layout)
					{
						$active = true;
					}
					else
					{
						if ($this->_view == $view && empty($layout))
						{
							$active = true;
						}
						else
						{
							$active = false;
						}
					}

					$url = 'index.php?option=' . $this->_option . '&view=' . $view;

					if ($layout)
					{
						$url .= '&layout=' . $layout;
					}

					JSubMenuHelper::addEntry(JText::_($titleLabel), $url, $active);
				}
				else
				{
					if (preg_match('/option=/', $view))
					{
						JSubMenuHelper::addEntry(JText::_($titleLabel), 'index.php?' . $view, false);
					}
				}
			}
		}
	}

	/**
	 * Add a specific CSS-stylesheet to this page
	 *
	 * @subpackage Yireo
	 *
	 * @param string $stylesheet
	 *
	 * @return null
	 */
	public function addCss($stylesheet)
	{
		$prefix = ($this->application->isSite()) ? 'site-' : 'backend-';
		$template = $this->application->getTemplate();

		if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->_option . '/' . $prefix . $stylesheet))
		{
			$this->document->addStyleSheet(JURI::root() . 'templates/' . $template . '/css/' . $this->_option . '/' . $prefix . $stylesheet);
		}
		else
		{
			if (file_exists(JPATH_SITE . '/media/' . $this->_option . '/css/' . $prefix . $stylesheet))
			{
				$this->document->addStyleSheet(JURI::root() . 'media/' . $this->_option . '/css/' . $prefix . $stylesheet);
			}
			else
			{
				if (file_exists(JPATH_SITE . '/templates/' . $template . '/css/' . $this->_option . '/' . $stylesheet))
				{
					$this->document->addStyleSheet(JURI::root() . 'templates/' . $template . '/css/' . $this->_option . '/' . $stylesheet);
				}
				else
				{
					if (file_exists(JPATH_SITE . '/media/' . $this->_option . '/css/' . $stylesheet))
					{
						$this->document->addStyleSheet(JURI::root() . 'media/' . $this->_option . '/css/' . $stylesheet);
					}
					else
					{
						if (file_exists(JPATH_SITE . '/media/lib_yireo/css/' . $stylesheet))
						{
							$this->document->addStyleSheet(JURI::root() . 'media/lib_yireo/css/' . $stylesheet);
						}
					}
				}
			}
		}
	}

	/**
	 * Add a specific JavaScript-script to this page
	 *
	 * @subpackage Yireo
	 *
	 * @param string $script
	 *
	 * @return null
	 */
	public function addJs($script)
	{
		$prefix = ($this->application->isSite()) ? 'site-' : 'backend-';
		$template = $this->application->getTemplate();

		if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->_option . '/' . $prefix . $script))
		{
			$this->document->addScript(JURI::root() . 'templates/' . $template . '/js/' . $this->_option . '/' . $prefix . $script);
		}
		else
		{
			if (file_exists(JPATH_SITE . '/media/' . $this->_option . '/js/' . $prefix . $script))
			{
				$this->document->addScript(JURI::root() . 'media/' . $this->_option . '/js/' . $prefix . $script);
			}
			else
			{
				if (file_exists(JPATH_SITE . '/templates/' . $template . '/js/' . $this->_option . '/' . $script))
				{
					$this->document->addScript(JURI::root() . 'templates/' . $template . '/js/' . $this->_option . '/' . $script);
				}
				else
				{
					if (file_exists(JPATH_SITE . '/media/' . $this->_option . '/js/' . $script))
					{
						$this->document->addScript(JURI::root() . 'media/' . $this->_option . '/js/' . $script);
					}
					else
					{
						if (file_exists(JPATH_SITE . '/media/lib_yireo/js/' . $script))
						{
							$this->document->addScript(JURI::root() . 'media/lib_yireo/js/' . $script);
						}
					}
				}
			}
		}
	}

	/**
	 * Add a folder to the template-search path
	 *
	 * @subpackage Yireo
	 *
	 * @param string  $path
	 * @param boolean $first
	 *
	 * @return bool
	 */
	protected function addNewTemplatePath($path, $first = true)
	{
		// If this path is non-existent, skip it
		if (!is_dir($path))
		{
			return;
		}

		// If this path is already included, skip it
		if (in_array($path, $this->templatePaths))
		{
			return;
		}

		// Add this path to the beginning of the array
		if ($first)
		{
			array_unshift($this->templatePaths, $path);
		}
		else
		{
			// Add this path to the end of the array
			$this->templatePaths[] = $path;
		}
	}

	/**
	 * An override of the original JView-function to allow template-files across multiple layouts
	 *
	 * @param string $file
	 * @param array  $variables
	 *
	 * @return string
	 */
	public function loadTemplate($file = null, $variables = array())
	{
		// Define version-specific folder
		if (YireoHelper::isJoomla25() == true && YireoHelper::hasBootstrap() == false)
		{
			$versionFolder = 'joomla25';
		}
		else
		{
			$versionFolder = 'joomla35';
		}

		// Construct the paths where to locate a specific template
		if ($this->application->isSite() == false)
		{
			// Reset the template-paths
			$this->templatePaths = array();

			// Local layout
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/views/' . $this->_view . '/tmpl', true);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/views/' . $this->_view . '/tmpl/' . $versionFolder, true);

			// Library defaults
			$this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/lib/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/lib/view/' . $this->_viewParent, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/libraries/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/libraries/view/' . $this->_viewParent, false);
		}
		else
		{
			$template = $this->application->getTemplate();

			// Local layout
			$this->addNewTemplatePath(JPATH_SITE . '/components/' . $this->_option . '/views/' . $this->_view . '/tmpl', true);
			$this->addNewTemplatePath(JPATH_SITE . '/components/' . $this->_option . '/views/' . $this->_view . '/tmpl/' . $versionFolder, true);

			// Template override
			$this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/lib_yireo/' . $this->_view, true);
			$this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/' . $this->_option . '/' . $this->_view, true);
			$this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/' . $this->_option . '/' . $this->_view . '/' . $versionFolder, true);

			// Library defaults
			$this->addNewTemplatePath(JPATH_THEMES . '/' . $template . '/html/lib_yireo/' . $this->_viewParent, true);
			$this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_LIBRARIES . '/yireo/view/' . $this->_viewParent, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/lib/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/lib/view/' . $this->_viewParent, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/libraries/view/' . $this->_viewParent . '/' . $versionFolder, false);
			$this->addNewTemplatePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/libraries/view/' . $this->_viewParent, false);
		}

		// Default file
		if (empty($file))
		{
			$file = 'default.php';
		}

		$templatePaths = $this->templatePaths;

		// Deal with any subfolders (not recommended, but still possible)
		if (strstr($file, '/'))
		{
			$fileParts = explode('/', $file);
			$file = array_pop($fileParts);

			foreach ($templatePaths as $templatePathIndex => $templatePath)
			{
				foreach ($fileParts as $filePart)
				{
					$templatePaths[$templatePathIndex] = $templatePath . '/' . $filePart;
				}
			}
		}

		// Find the template-file
		if (!preg_match('/\.php$/', $file))
		{
			$file = $file . '.php';
		}

		jimport('joomla.filesystem.path');
		$template = JPath::find($templatePaths, $file);

		// If this template is empty, try to use alternatives
		if (empty($template) && $file == 'default.php')
		{
			$file = 'form.php';
			$template = JPath::find($templatePaths, $file);
		}

		$output = null;

		if ($template != false)
		{
			// Include the variables here
			if (!empty($variables))
			{
				foreach ($variables as $name => $value)
				{
					$$name = $value;
				}
			}

			// Unset so as not to introduce into template scope
			unset($file);

			// Never allow a 'this' property
			if (isset($this->this))
			{
				unset($this->this);
			}

			// Unset variables
			unset($variables);
			unset($name);
			unset($value);

			// Start capturing output into a buffer
			ob_start();
			include $template;

			// Done with the requested template; get the buffer and clear it.
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
		else
		{
			return null;
		}
	}
}