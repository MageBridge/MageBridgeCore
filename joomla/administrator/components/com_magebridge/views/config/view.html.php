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

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewConfig extends YireoCommonView
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// Load important variables
		$layout = JFactory::getApplication()->input->getCmd('layout');

		// Initalize common elements
		MageBridgeViewHelper::initialize('CONFIG');

		// Load the import-layout directly
		if ($layout == 'import') {
			return parent::display($layout);
		}

		// Load the tabs
		jimport('joomla.html.pane');
		if(class_exists('JPane')) {
			$activeTab = $this->application->getUserStateFromRequest( $this->_option.'.tab', 'tab', 1, 'int' );
			$this->pane = JPane::getInstance('tabs', array('startOffset' => $activeTab));
		} else {
			$this->pane = false;
		}

		// Toolbar options
		if (MageBridgeAclHelper::isDemo() == false) JToolBarHelper::custom( 'export', 'export.png', null, 'Export', false );
		if (MageBridgeAclHelper::isDemo() == false) JToolBarHelper::custom( 'import', 'import.png', null, 'Import', false );
		JToolBarHelper::preferences('com_magebridge');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();

		// Extra scripts
		MageBridgeTemplateHelper::load('jquery');
		$this->addJs('backend-config.js');

		// Before loading anything, we build the bridge
		$this->preBuildBridge();

		// Load the configuration and check it
		$config = MagebridgeModelConfig::load();
		$this->checkConfig();

		// Make sure demo-users are not seeing any sensitive data
		if (MageBridgeAclHelper::isDemo() == true) {
			$censored_values = array('supportkey', 'api_user', 'api_key');
			foreach ($censored_values as $censored_value) {
				$config[$censored_value]['value'] = str_repeat('*', YireoHelper::strlen($config[$censored_value]['value']));
			}
		}

		// Instantiate the form
		$configData = array('config' => array());
		foreach($config as $name => $configValue) {
			$configData['config'][$name] = $configValue['value'];
		}
		$formFile = JPATH_SITE.'/components/com_magebridge/models/config.xml';
		$form = JForm::getInstance('config', $formFile);
		$form->bind($configData);
		$this->form = $form;

		$this->config = $config;

		parent::display($tpl);
	}

	/**
	 * Method to check the configuration and generate warnings if needed
	 *
	 * @param null
	 * @return null
	 */
	public function checkConfig()
	{
		// Check if the settings are all empty
		if (MagebridgeModelConfig::allEmpty() == true) {
			JError::raiseWarning( 500, JText::sprintf( 'Check the online %s for more information.', MageBridgeHelper::getHelpText('quickstart')));
			return;
		}

		// Otherwise check all values
		$config = MagebridgeModelConfig::load();
		foreach ($config as $c) {
			if (isset($c['name']) && isset($c['value']) && $message = MageBridge::getConfig()->check($c['name'], $c['value'])) {
				JError::raiseWarning( 500, $message );
			}
		}

		return;
	}

	/**
	 * Get the HTML-field for a custom field
	 *
	 * @param string $type
	 * @param string $name
	 * @return string
	 */
	protected function getCustomField($type, $name)
	{
		require_once JPATH_COMPONENT.'/fields/'.$type.'.php';
		jimport('joomla.form.helper');
		$field = JFormHelper::loadFieldType($type);
		$field->setName($name);
		$field->setValue(MagebridgeModelConfig::load($name));
		return $field->getHtmlInput();
	}

	/**
	 * Shortcut method to build the bridge for this page
	 *
	 * @param null
	 * @return null
	 */
	public function preBuildBridge()
	{
		// Register the needed segments
		$register = MageBridgeModelRegister::getInstance();
		$register->add('headers');
		$register->add('api', 'customer_group.list');
		$register->add('api', 'magebridge_websites.list');

		// Build the bridge and collect all segments
		$bridge = MageBridge::getBridge();
		$bridge->build();
	}

	/**
	 * Method to get all the different tabs
	 */
	public function getTabs()
	{
		$tabs= array();
		return $tabs;
	}

	/**
	 * Method to print a specific tab
	 * @deprecated
	 */
	public function printTab($name, $id, $template)
	{
		if($this->pane) {
			echo $this->pane->startPanel($name, $id);
			echo $this->loadTemplate($template);
			echo $this->pane->endPanel();
		} else {
			echo '<div class="tab-pane" id="'.$id.'">';
			echo $this->loadTemplate($template);
			echo '</div>';
		}
	}

	/**
	 * Method to print a specific fieldset
	 */
	public function printFieldset($form, $fieldset)
	{
		if ($this->pane) {
			echo $this->pane->startPanel(JText::_($fieldset->label), $fieldset->name);
			foreach($form->getFieldset($fieldset->name) as $field) {
				echo $this->loadTemplate('field', array('field' => $field));
			}
			echo $this->pane->endPanel();
		} else {
			echo '<div class="tab-pane" id="'.$fieldset->name.'">';
			foreach($form->getFieldset($fieldset->name) as $field) {
				echo $this->loadTemplate('field', array('field' => $field));
			}
			echo '</div>';
		}
	}
}
