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

// Require the parent view
require_once JPATH_COMPONENT.'/view.php';

// Import the needed libraries
jimport('joomla.filter.output');

/**
 * HTML View class
 */
class MageBridgeViewUsergroup extends MageBridgeView
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// Before loading anything, we build the bridge
		$this->preBuildBridge();

		// Fetch the item
		$this->fetchItem();

		// Build the fields
		$fields = array();
		$fields['joomla_group'] = $this->getFieldJoomlaGroup($this->item->joomla_group);
		$fields['magento_group'] = $this->getFieldMagentoGroup($this->item->magento_group);
		$fields['ordering'] = $this->getFieldOrdering($this->item);
		$fields['published'] = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $this->item->published );

		// Initialize parameters
		$file = JPATH_ADMINISTRATOR.'/components/com_magebridge/models/usergroup.xml';
		$form = JForm::getInstance('params', $file);
		$params = YireoHelper::toRegistry($this->item->params);
		$form->bind(array('params' => $params->toArray()));
		$this->params_form = $form;

		$this->fields = $fields;

		parent::display($tpl);
	}

	/**
	 * Get the HTML-field for the ordering
	 *
	 * @param null
	 * @return string
	 */
	public function getFieldOrdering($item = null)
	{
		// Build the HTML-select list for ordering
		$query = 'SELECT ordering AS value, description AS text'
			. ' FROM #__magebridge_usergroups'
			. ' ORDER BY ordering';

		if (MageBridgeHelper::isJoomla35() == false) {
			return JHTML::_('list.specificordering',  $item, $item->id, $query );
		}
		return null;
	}

	/**
	 * Get the HTML-field for the Joomla! usergroup
	 *
	 * @param null
	 * @return string
	 */
	public function getFieldJoomlaGroup($value = null)
	{
		$usergroups = MageBridgeFormHelper::getUsergroupOptions();
		return JHTML::_('select.genericlist', $usergroups, 'joomla_group', null, 'value', 'text', $value);
	}

	/**
	 * Get the HTML-field for the Magento customer group
	 *
	 * @param null
	 * @return string
	 */
	public function getFieldMagentoGroup($value = null)
	{
		return MageBridgeFormHelper::getField('magebridge.customergroup', 'magento_group', $value);
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
		$register->add('api', 'customer_group.list');

		// Build the bridge and collect all segments
		$bridge = MageBridge::getBridge();
		$bridge->build();
	}
}
