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

// Define constants
define('MAGEBRIDGE_UPDATE_NOTAVAILABLE', 0);
define('MAGEBRIDGE_UPDATE_NOTNEEDED', 1);
define('MAGEBRIDGE_UPDATE_AVAILABLE', 2);

/**
 * HTML View class
 *
 * @static
 * @package	MageBridge
 */
class MageBridgeViewUpdate extends YireoView
{
	protected $loadToolbar = false;

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// ???
		MageBridgeViewHelper::initialize('UPDATE');

		// Filters - type
		$options = array( 
			array('value' => '', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_NONE')),
			array('value' => 'module', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE')),
			array('value' => 'module-site', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE_SITE')),
			array('value' => 'module-admin', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE_ADMIN')),
			array('value' => 'plugin', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN')),
			array('value' => 'plugin-other', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_OTHER')),
			array('value' => 'plugin-magebridgeproduct', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_PRODUCT')),
			array('value' => 'plugin-magebridgestore', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_STORE')),
			array('value' => 'plugin-magebridgeprofile', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_PROFILE')),
			array('value' => 'plugin-magebridgenewsletter', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_NEWSLETTER')),
		);
		$filter_type = $this->getFilter('type');
		$javascript = 'onchange="document.adminForm.submit();"';
		$this->lists['type'] = JHTML::_('select.genericlist', $options, 'filter_type', $javascript, 'value', 'text', $filter_type );

		// Filters - search
		$filter_search = $this->getFilter('search');

		// Toolbar options
		JToolBarHelper::custom('home', 'back', '', 'LIB_YIREO_VIEW_TOOLBAR_HOME', false);
		JToolBarHelper::custom('refresh', 'preview.png', 'preview_f2.png', 'LIB_YIREO_VIEW_TOOLBAR_REFRESH', false);
		JToolBarHelper::custom('updateQueries', 'archive', '', 'LIB_YIREO_VIEW_TOOLBAR_DBUPGRADE', false);

		// Add jQuery for selection effects
		MageBridgeTemplateHelper::load('jquery');
		$this->addJs('backend-update.js');

		// Load the packages
		$data = MageBridgeUpdateHelper::getData();

		// Add filtering
		if(!empty($filter_type) || !empty($filter_search)) {

			if(!empty($filter_type)) $filter_type = explode('-', $filter_type);

			foreach ($data as $index => $extension) {

				if (!empty($filter_search)) {
					if (!stristr($extension['name'], $filter_search) && !stristr($extension['title'], $filter_search)
						&& !stristr($extension['description'], $filter_search)) {
						unset($data[$index]);
						continue;
					}
				}

				if (!empty($filter_type)) {
					if (count($filter_type) == 1) {
						if ($extension['type'] != $filter_type[0]) {
							unset($data[$index]);
							continue;
						}
					} else {
						if ($filter_type[0] == 'module') {
							if ($extension['type'] != 'module') {
								unset($data[$index]);
								continue;
							} elseif ($extension['type'] != $filter_type[0] || $extension['app'] != $filter_type[1]) {
								unset($data[$index]);
								continue;
							}
						} elseif ($filter_type[0] == 'plugin') {
							if ($extension['type'] != 'plugin') {
								unset($data[$index]);
								continue;
							} elseif ($filter_type[1] == 'other' && in_array($extension['group'],
									array('magebridgeproduct', 'magebridgenewsletter', 'magebridgestore', 'magebridgeprofile'))
							) {
								unset($data[$index]);
								continue;
							} elseif ($filter_type[1] != 'other' && $extension['group'] != $filter_type[1]) {
								unset($data[$index]);
								continue;
							}
						}
					}
				}
			}
		}

		// Detect whether updates are available
		$update = MAGEBRIDGE_UPDATE_NOTAVAILABLE;
		if (!empty($data)) {
			$update = MAGEBRIDGE_UPDATE_NOTNEEDED;
			foreach ($data as $index => $extension) {

				// Skip this entry for version detection
				if (empty($extension['version']) || empty($extension['current_version'])) continue;

				if ($extension['current_version'] != $extension['version']) {
					$update = MAGEBRIDGE_UPDATE_AVAILABLE;
					break;
				}
			}
		}

		if ($update == MAGEBRIDGE_UPDATE_NOTAVAILABLE) {
			JError::raiseWarning('MB', JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_EMPTY_VERSION'));
		} else {
			JToolBarHelper::custom( 'update', 'download.png', 'download_f2.png', 'Update', false );
		}

		if ($update == MAGEBRIDGE_UPDATE_AVAILABLE) {
			JError::raiseNotice( 'UPDATE', JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_NEW_VERSIONS'));
		}

		$this->data = $data;
		$this->update = $update;
		$this->search = $filter_search;

		parent::display($tpl);
	}
}
