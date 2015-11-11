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

// Import the needed libraries
jimport('joomla.filter.output');

/**
 * HTML View class
 */
class MageBridgeViewStore extends YireoViewForm
{
	/**
	 * Constructor
	 */
	/**
	 * Main constructor method
	 *
	 * @access public
	 * @param array $config
	 * @return null
	 */
	public function __construct($config = array())
	{
		if(JFactory::getApplication()->input->getCmd('task') == 'default') {
			$this->loadToolbar = false;
		}

		// Call the parent constructor
		parent::__construct($config);
	}

	/**
	 * Method to prepare the content for display
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		switch(JFactory::getApplication()->input->getCmd('task')) {
			case 'default':
				$this->showDefaultForm($tpl);
				break;

			default:
				$this->showForm($tpl = 'form');
				break;
		}
	}

	/**
	 * Method to prepare the content for display
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function showDefaultForm($tpl = null)
	{
		// Initialize the view
		$this->setTitle(JText::_('COM_MAGEBRIDGE_VIEW_STORE_DEFAULT_STORE'));

		// Override the normal toolbar
		JToolBarHelper::cancel();
		JToolBarHelper::save();
		JToolBarHelper::apply();

		// Load values from the configuration
		$storegroup = MageBridgeModelConfig::load('storegroup');
		$storeview = MageBridgeModelConfig::load('storeview');

		// Construct the arguments for the HTML-element
		if (!empty($storeview)) {
			$type = 'storeview';
			$name = $storeview;
		} else if (!empty($storegroup)) {
			$type = 'storegroup';
			$name = $storegroup;
		} else {
			$type = null;
			$name = null;
		}

		// Fetch the HTML-element
		$this->lists['store'] = $this->getFieldStore($type, $name);

		parent::display($tpl);
	}

	/**
	 * Method to prepare the content for display
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function showForm($tpl = null)
	{
		// Fetch this item
		$this->fetchItem();

		// Build extra lists
		$this->lists['store'] = $this->getFieldStore($this->item->type, $this->item->name);

		// Initialize the form-file
		$file = JPATH_ADMINISTRATOR.'/components/com_magebridge/models/store.xml';

		// Prepare the params-form
		$params = YireoHelper::toRegistry($this->item->params)->toArray();
		$params_form = JForm::getInstance('params', $file);
		$params_form->bind(array('params' => $params));
		$this->params_form = $params_form;

		// Prepare the actions-form
		$actions = YireoHelper::toRegistry($this->item->actions)->toArray();
		$actions_form = JForm::getInstance('actions', $file);
		JPluginHelper::importPlugin('magebridgestore');
		JFactory::getApplication()->triggerEvent('onMageBridgeStorePrepareForm', array(&$actions_form, (array)$this->item));
		$actions_form->bind(array('actions' => $actions));
		$this->actions_form = $actions_form;

		// Check for a previous connector-value
		if(!empty($this->item->connector)) {
			$plugin = JPluginHelper::getPlugin('magebridgestore', $this->item->connector);
			if(empty($plugin)) {
				$plugin_warning = JText::sprintf('COM_MAGEBRIDGE_STORE_PLUGIN_WARNING', $this->item->connector);
				JError::raiseWarning(500, $plugin_warning);
			}
		}

		parent::display($tpl);
	}

	/**
	 * Helper method to get the HTML-formelement for a store
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $title
	 * @return string
	 */
	protected function getFieldStore($type = null, $value = null)
	{
		if (!empty($type) && !empty($value)) {
			$value = ($type == 'storegroup') ? 'g:'.$value : 'v:'.$value;
		} else {
			$value = null;
		}
	
		if (empty($name)) $name = 'store';
		return MageBridgeFormHelper::getField('magebridge.store', $name, $value, null);
	}

	/**
	 * Helper method to get the HTML-formelement for a storeview
	 *
	 * @param string $default
	 * @return string
	 */
	protected function getFieldStoreview($default = null)
	{
		return MageBridgeFormHelper::getField('magebridge.storeview', 'name', $value, null);
	}

	/**
	 * Helper method to get the HTML-formelement for a storegroup
	 *
	 * @param string $default
	 * @return string
	 */
	protected function getFieldStoregroup($default = null)
	{
		return MageBridgeFormHelper::getField('magebridge.storegroup', 'name', $value, null);
	}
}
