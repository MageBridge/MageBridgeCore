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
require_once dirname(__FILE__) . '/loader.php';

/**
 * Yireo Abstract View
 *
 * @package Yireo
 */
if (YireoHelper::isJoomla25())
{
	jimport('joomla.application.component.view');

	class YireoAbstractView extends JView
	{
	}
}
else
{
	class YireoAbstractView extends JViewLegacy
	{
	}
}

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
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param array $config
	 *
	 * @return null
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
	 * @access     protected
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
	 * @param string $title
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
	 * @access     public
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
	 * @access     public
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
	 * @access     protected
	 * @subpackage Yireo
	 *
	 * @param string $path
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

			// Add this path to the end of the array
		}
		else
		{
			$this->templatePaths[] = $path;
		}
	}

	/**
	 * An override of the original JView-function to allow template-files across multiple layouts
	 *
	 * @access public
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

/**
 * Yireo View
 *
 * @package Yireo
 */
class YireoView extends YireoCommonView
{
	/**
	 * Array of HTML-lists for usage in the layout-file
	 */
	protected $lists = array();

	/**
	 * Array of HTML-grid-elements for usage in the layout-file
	 */
	protected $grid = array();

	/**
	 * Flag to determine whether to autoclean item-properties or not
	 */
	protected $autoclean = false;

	/**
	 * Flag to determine whether to load the menu
	 */
	protected $loadToolbar = true;

	/**
	 * Flag to prepare the display-data
	 */
	protected $prepare_display = true;

	/**
	 * Main constructor method
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param array $config
	 *
	 * @return null
	 */
	public function __construct($config = array())
	{
		// Call the parent constructor
		parent::__construct($config);

		// Set the parameters
		if (empty($this->params))
		{
			if ($this->application->isSite() == false)
			{
				$this->params = JComponentHelper::getParams($this->_option);
			}
			else
			{
				$this->params = $this->application->getParams($this->_option);
			}
		}

		// Determine whether this view is single or not
		if ($this->_single === null)
		{
			$className = get_class($this);
			if (preg_match('/s$/', $className))
			{
				$this->_single = false;
			}
			else
			{
				$this->_single = true;
			}
		}

		// Insert the model & table
		$this->_model = $this->getModel();
		if (!empty($this->_model))
		{
			$this->_table = $this->_model->getTable();
		}

		// Add some backend-elements
		if ($this->application->isAdmin())
		{

			// Automatically set the title
			$this->setTitle();
			$this->setMenu();
			$this->setAutoclean(true);

			// Add some things to the task-bar
			if ($this->_single && $this->loadToolbar == true)
			{
				if ($this->params->get('toolbar_show_savenew', 1))
				{
					JToolBarHelper::custom('savenew', 'save.png', 'save.png', 'LIB_YIREO_VIEW_TOOLBAR_SAVENEW', false, true);
				}
				if ($this->params->get('toolbar_show_saveandcopy', 1))
				{
					JToolBarHelper::custom('saveandcopy', 'copy.png', 'copy.png', 'LIB_YIREO_VIEW_TOOLBAR_SAVEANDCOPY', false, true);
				}
				if ($this->params->get('toolbar_show_saveascopy', 1))
				{
					JToolBarHelper::custom('saveascopy', 'copy.png', 'copy.png', 'LIB_YIREO_VIEW_TOOLBAR_SAVEASCOPY', false, true);
				}
				JToolBarHelper::save();
				JToolBarHelper::apply();

				if ($this->isEdit() == false)
				{
					JToolBarHelper::cancel();
				}
				else
				{
					JToolBarHelper::cancel('cancel', 'LIB_YIREO_VIEW_TOOLBAR_CLOSE');
				}

				JHTML::_('behavior.tooltip');
			}
		}
	}

	/**
	 * Main display method
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param string $tpl
	 *
	 * @return null
	 */
	public function display($tpl = null)
	{
		if ($this->prepare_display == true)
		{
			$this->prepareDisplay();
		}
		if (empty($tpl))
		{
			$tpl = $this->getLayout();
		}
		parent::display($tpl);
	}

	/**
	 * Method to prepare for displaying
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param null
	 *
	 * @return null
	 */
	public function prepareDisplay()
	{
		// Include extra component-related CSS
		$this->addCss('default.css');
		$this->addCss('view-' . $this->_view . '.css');
		if (YireoHelper::isJoomla25() == true)
		{
			$this->addCss('j25.css');
		}
		if (YireoHelper::isJoomla35() == true)
		{
			$this->addCss('j35.css');
		}

		// Include extra component-related JavaScript
		$this->addJs('default.js');
		$this->addJs('view-' . $this->_view . '.js');

		// Fetch parameters if they exist
		$params = null;
		if (!empty($this->item->params))
		{
			if (file_exists(JPATH_COMPONENT . '/models/' . $this->_name . '.xml'))
			{
				$file = JPATH_COMPONENT . '/models/' . $this->_name . '.xml';
				$params = YireoHelper::toParameter($this->item->params, $file);
			}
			else
			{
				if (!empty($this->item->params))
				{
					$params = YireoHelper::toParameter($this->item->params);
				}
			}
		}

		// Assign parameters
		if (!empty($params))
		{
			if (isset($this->item->created))
			{
				$params->set('created', $this->item->created);
			}

			if (isset($this->item->created_by))
			{
				$params->set('created_by', $this->item->created_by);
			}

			if (isset($this->item->modified))
			{
				$params->set('modified', $this->item->modified);
			}

			if (isset($this->item->modified_by))
			{
				$params->set('modified_by', $this->item->modified_by);
			}

			$this->params = $params;
		}

		// Load the form if it's there
		$form = $this->get('Form');

		if (!empty($form))
		{
			$this->form = $form;
		}
	}

	/**
	 * Helper-method to set a specific filter
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param string $filter
	 * @param string $default
	 * @param string $type
	 * @param string $option
	 *
	 * @return mixed
	 */
	protected function getFilter($filter = '', $default = '', $type = 'cmd', $option = '')
	{
		if (empty($option))
		{
			$option = $this->_option_id;
		}
		$value = $this->application->getUserStateFromRequest($option . 'filter_' . $filter, 'filter_' . $filter, $default, $type);

		return $value;
	}

	/**
	 * Helper-method to get multiple items from the MVC-model
	 *
	 * @return array
	 */
	protected function fetchItems()
	{
		// Get data from the model
		if (empty($this->items))
		{
			$this->total = $this->get('Total');
			$this->pagination = $this->get('Pagination');
			$this->items = $this->get('Data');
		}

		if (!empty($this->items))
		{
			foreach ($this->items as $index => $item)
			{

				// Clean data
				if ($this->autoclean == true)
				{
					JFilterOutput::objectHTMLSafe($item, ENT_QUOTES, 'text');
					if (isset($item->text))
					{
						$item->text = htmlspecialchars($item->text);
					}
					if (isset($item->description))
					{
						$item->description = htmlspecialchars($item->description);
					}
				}

				// Reinsert this item
				$this->items[$index] = $item;
			}
		}

		// Get other data from the model
		$this->lists['search_name'] = 'filter_search';
		$this->lists['search'] = $this->getFilter('search', null, 'string');
		$this->lists['order'] = $this->getFilter('order', null, 'string');
		$this->lists['order_Dir'] = $this->getFilter('order_Dir');
		$this->lists['state'] = JHTML::_('grid.state', $this->getFilter('state'));

		return $this->items;
	}

	/**
	 * Helper-method to get a single item from the MVC-model
	 *
	 * @return false|object
	 */
	protected function fetchItem()
	{
		// Fetch the model
		$this->model = $this->getModel();

		if (empty($this->model))
		{
			return false;
		}

		// Determine if this is a new item or not
		$primary_key = (method_exists($this->model, 'getPrimaryKey')) ? $this->model->getPrimaryKey() : 'id';
		$this->item = (method_exists($this->model, 'getData')) ? $this->model->getData() : (object) null;
		$this->item->isNew = (isset($this->item->$primary_key) && $this->item->$primary_key < 1);

		// Override in case of copying
		if ($this->jinput->getCmd('task') == 'copy')
		{
			$this->item->$primary_key = 0;
			$this->item->isNew = true;
		}

		// If there is a key, fetch the data
		if ($this->item->isNew == false)
		{

			// Extra checks in the backend
			if ($this->application->isAdmin())
			{

				// Fail if checked-out not by current user
				if (method_exists($this->model, 'isCheckedOut') && $this->model->isCheckedOut($this->user->get('id')))
				{
					$msg = JText::sprintf('LIB_YIREO_MODEL_CHECKED_OUT', $this->item->title);
					$this->application->redirect('index.php?option=' . $this->_option, $msg);
				}

				// Checkout older items
				if ($this->item->isNew == false && method_exists($this->model, 'checkout'))
				{
					$this->model->checkout($this->user->get('id'));
				}
			}

			// Clean data
			if ($this->application->isAdmin() == false || ($this->jinput->getCmd('task') != 'edit' && $this->_viewParent != 'form'))
			{
				if ($this->autoclean == true)
				{
					JFilterOutput::objectHTMLSafe($this->item, ENT_QUOTES, 'text');
					if (isset($this->item->title))
					{
						$this->item->title = htmlspecialchars($this->item->title);
					}
					if (isset($this->item->text))
					{
						$this->item->text = htmlspecialchars($this->item->text);
					}
					if (isset($this->item->description))
					{
						$this->item->description = htmlspecialchars($this->item->description);
					}
				}
			}
		}

		// Automatically hit this item
		if ($this->application->isSite())
		{
			$this->model->hit();
		}

		// Assign the published-list
		if (isset($this->item->published))
		{
			$this->lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $this->item->published);
		}
		else
		{
			$this->lists['published'] = null;
		}

		// Assign the access-list
		if (isset($this->item->access))
		{
			if (class_exists('JHtmlAccess'))
			{
				$this->lists['access'] = JHtmlAccess::level('access', $this->item->access);
			}
			else
			{
				$this->lists['access'] = JHTML::_('list.accesslevel', $this->item);
			}
		}
		else
		{
			$this->lists['access'] = null;
		}

		$ordering = (method_exists($this->model, 'getOrderByDefault')) ? $this->model->getOrderByDefault() : null;

		if ($this->application->isAdmin() && !empty($ordering) && $ordering == 'ordering')
		{
			$this->lists['ordering'] = JHTML::_('list.ordering', 'ordering', $this->model->getOrderingQuery(), $this->item->ordering);
		}
		else
		{
			$this->lists['ordering'] = null;
		}
	}

	/**
	 * Add the AJAX-script to the page
	 *
	 * @param string $url
	 * @param string $div
	 *
	 * @return mixed
	 */
	public function ajax($url = null, $div = null)
	{
		return YireoHelperView::ajax($url, $div);
	}

	/**
	 * Add the AJAX-script to the page
	 */
	public function getAjaxFunction()
	{
		if (YireoHelper::isJoomla25())
		{
			$script = "<script type=\"text/javascript\">\n" . "function getAjax(ajax_url, element_id, type) {\n" . "    var MBajax = new Request({\n" . "        url: ajax_url, \n" . "        method: 'get', \n" . "        onSuccess: function(result){\n" . "            if (result == '') {\n" . "                alert('Empty result');\n" . "            } else {\n" . "                if (type == 'input') {\n" . "                    $(element_id).value = result;\n" . "                } else {\n" . "                    $(element_id).innerHTML = result;\n" . "                }\n" . "            }\n" . "        }\n" . "    }).send();\n" . "}\n" . "</script>";
		}
		else
		{
			$script = "<script type=\"text/javascript\">\n" . "function getAjax(ajax_url, element_id, type) {\n" . "    var MBajax = jQuery.ajax({\n" . "        url: ajax_url, \n" . "        method: 'get', \n" . "        success: function(result){\n" . "            if (result == '') {\n" . "                alert('Empty result');\n" . "            } else {\n" . "                 jQuery('#' + element_id).val(result);\n" . "            }\n" . "        }\n" . "    });\n" . "}\n" . "</script>";
		}

		$this->document->addCustomTag($script);
	}

	/**
	 * Automatically decode HTML-characters from specified item-fields
	 *
	 * @param bool $autoclean
	 */
	public function setAutoClean($autoclean = true)
	{
		$this->autoclean = $autoclean;
	}

	/**
	 * Helper method to determine whether this is a new entry or not
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function isEdit()
	{
		$cid = $this->jinput->get('cid', array(0), '', 'array');

		if (!empty($cid) && $cid > 0)
		{
			return true;
		}

		$id = $this->jinput->getInt('id');

		if (!empty($id) && $id > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Overload the original method
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getModel($name = null)
	{
		if (empty($name))
		{
			$name = $this->_name;
		}

		$name = strtolower($name);

		if (isset($this->_models[$name]))
		{
			$model = $this->_models[$name];
		}

		if (empty($model))
		{
			jimport('joomla.application.component.model');

			if (YireoHelper::isJoomla25())
			{
				JModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/models');
			}
			else
			{
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/' . $this->_option . '/models');
			}

			$classPrefix = ucfirst(preg_replace('/^com_/', '', $this->_option)) . 'Model';
			$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $classPrefix);
			$classPrefix = str_replace(' ', '', ucwords(str_replace('_', ' ', $classPrefix)));

			if (YireoHelper::isJoomla25())
			{
				$model = JModel::getInstance($name, $classPrefix, array());
			}
			else
			{
				$model = JModelLegacy::getInstance($name, $classPrefix, array());
			}
		}

		return $model;
	}

	/**
	 * Helper method to display a certain grid-header
	 *
	 * @param string $type
	 * @param string $title
	 *
	 * @return string
	 */
	public function getGridHeader($type, $title)
	{
		$html = null;
		if ($type == 'orderby')
		{
			$field = $this->get('OrderByDefault');
			$html .= JHTML::_('grid.sort', $title, $field, $this->lists['order_Dir'], $this->lists['order']);
			$html .= JHTML::_('grid.order', $this->items);
		}
		else
		{
		}

		return $html;
	}

	/**
	 * Helper method to display a certain grid-cell
	 *
	 * @param string $type
	 * @param object $item
	 * @param int    $i
	 * @param int    $n
	 *
	 * @return string
	 */
	public function getGridCell($type, $item, $i = 0, $n = 0)
	{
		$html = null;
		if ($type == 'reorder')
		{
			$field = $this->get('OrderByDefault');
			$ordering = ($this->lists['order'] == $field);
			$disabled = ($ordering) ? '' : 'disabled="disabled"';

			$html .= '<span>' . $this->pagination->orderUpIcon($i, 1, 'orderup', 'Move Up', $ordering) . '</span>';
			$html .= '<span>' . $this->pagination->orderDownIcon($i, $n, 1, 'orderdown', 'Move Down', $ordering) . '</span>';
			$html .= '<input type="text" name="order[]" size="5" value="' . $item->$field . '" ' . $disabled . ' class="text_area" style="text-align: center" />';

		}
		else
		{
			if ($type == 'published')
			{
				$html .= JHtml::_('jgrid.published', $item->published, $i, 'articles.', false, 'cb', $item->params->get('publish_up'), $item->params->get('publish_down'));

			}
			else
			{
				if ($type == 'checked')
				{
					$html .= JHTML::_('grid.checkedout', $item, $i);
				}
			}
		}

		return $html;
	}

	/**
	 * Method to return img-tag for a certain image, if that image exists
	 *
	 * @access     public
	 * @subpackage Yireo
	 *
	 * @param
	 *
	 * @return string
	 */
	public function getImageTag($name = null)
	{
		$paths = array(
			'/media/' . $this->_option . '/images/' . $name,
			'/media/lib_yireo/images/' . $name,
			'/images/' . $name,);

		foreach ($paths as $path)
		{
			if (file_exists(JPATH_SITE . $path))
			{
				return '<img src="' . $path . '" alt="' . $name . '" />';
			}
		}
	}

	/**
	 * Override original method
	 *
	 * @throws Exception
	 * @return  string|boolean  The name of the model
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$match = null;

			if (!preg_match('/View((view)*(.*(view)?.*))$/i', get_class($this), $match))
			{
				throw new Exception("JView::getName() : Cannot get or parse class name.", 500);

				return false;
			}
			$name = strtolower($match[3]);
		}

		return $name;
	}

	/**
	 * Add a layout to this view
	 *
	 * @access public
	 *
	 * @param string $name
	 * @param array  $variables
	 *
	 * @return string
	 */
	public function loadLayout($name = null, $variables = array())
	{
		// Skip for Joomla 2.5
		if (YireoHelper::isJoomla25() == true)
		{
			return false;
		}

		$name = $this->getLayoutPrefix() . $name;

		// Merge current object variables
		$variables = array_merge($variables, get_object_vars($this));

		$basePath = null;
		$layout = new JLayoutFile($name, $basePath);

		echo $layout->render($variables);
	}

	/**
	 * Return a common prefix for all layouts in this component
	 *
	 * @return string
	 */
	public function getLayoutPrefix()
	{
		return '';
	}
}