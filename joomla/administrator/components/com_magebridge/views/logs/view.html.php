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
class MageBridgeViewLogs extends YireoViewList
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// Automatically fetch items, total and pagination - and assign them to the template
		$this->fetchItems();

		// Toolbar options
		$this->loadToolbarEdit = false;
		$this->loadToolbarDelete = false;
		JToolBarHelper::custom('delete', 'delete', '', 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_TRUNCATE', false);
		JToolBarHelper::custom('refresh', 'preview', '', 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_REFRESH', false);
		JToolBarHelper::custom('export', 'archive', '', 'COM_MAGEBRIDGE_VIEW_LOGS_TOOLBAR_EXPORT', false);

		// Custom filters
		$this->lists['remote_addr'] = $this->selectRemoteAddress($this->getFilter('remote_addr'));
		$this->lists['type'] = $this->selectType($this->getFilter('type'));
		$this->lists['origin'] = $this->selectOrigin($this->getFilter('origin'));

		// If debugging is enabled report it
		if ($this->countLogs() > 1000) {
			MageBridgeModelDebug::getInstance()->feedback(JText::sprintf('COM_MAGEBRIDGE_VIEW_LOGS_FLOOD_WARNING', (int)$this->countLogs()));
		}

		parent::display($tpl);
	}

	/**
	 * Helper-method to return the HTML-field for log-origin
	 *
	 * @param string $current
	 * @return string
	 */
	public function selectOrigin($current)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT DISTINCT(origin) AS value FROM #__magebridge_log');
		$rows = $db->loadObjectList();

		$options = array();
		$options[] = JHTML::_('select.option', '', '- '.JText::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ORIGIN').' -', 'id', 'title' );

		if (!empty($rows)) {
			foreach ( $rows as $row ) {
				$options[] = JHTML::_('select.option', $row->value, JText::_($row->value), 'id', 'title' );
			}
		}

		$javascript = 'onchange="document.adminForm.submit();"';
		return JHTML::_('select.genericlist', $options, 'filter_origin', $javascript, 'id', 'title', $current );
	}

	/**
	 * Helper-method to return the HTML-field for log-address
	 *
	 * @param string $current
	 * @return string
	 */
	public function selectRemoteAddress($current)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT DISTINCT(remote_addr) AS value FROM #__magebridge_log');
		$rows = $db->loadObjectList();

		$options = array();
		$options[] = JHTML::_('select.option', '', '- '.JText::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_ADDRESS').' -', 'id', 'title' );

		if (!empty($rows)) {
			foreach ( $rows as $row ) {
				$options[] = JHTML::_('select.option', $row->value, $row->value, 'id', 'title' );
			}
		}

		$javascript	 = 'onchange="document.adminForm.submit();"';
		return JHTML::_('select.genericlist', $options, 'filter_remote_addr', $javascript, 'id', 'title', $current );
	}

	/**
	 * Helper-method to return a list of log-types
	 *
	 * @param null
	 * @return array
	 */
	public function getTypes()
	{
		$types = array(
			'Trace' => 1,
			'Notice' => 2,
			'Warning' => 3,
			'Error' => 4,
			'Feedback' => 5,
			'Profiler' => 6,
		);
		return $types;
	}

	/**
	 * Helper-method to return the HTML-field for log-types
	 *
	 * @param string $current
	 * @return string
	 */
	public function selectType($current)
	{
		$options = array();

		$options[] = JHTML::_('select.option', '', '- '.JText::_('COM_MAGEBRIDGE_VIEW_LOGS_SELECT_TYPE').' -', 'id', 'title' );
		foreach ( $this->getTypes() as $title => $id ) {
			$options[] = JHTML::_('select.option', $id, $title, 'id', 'title' );
		}

		$javascript	 = 'onchange="document.adminForm.submit();"';

		return JHTML::_('select.genericlist', $options, 'filter_type', $javascript, 'id', 'title', $current );
	}
	
	/**
	 * Helper-method to return the title for a specific log-type
	 *
	 * @param string $type
	 * @return string
	 */
	public function printType($type)
	{
		$types = $this->getTypes();
		foreach ($types as $name => $value) {
			if ($type == $value) return JText::_($name);
		}
	}
	
	/**
	 * Helper-method to count the total number of logs
	 *
	 * @param null
	 * @return int
	 */
	public function countLogs()
	{
		$db = JFactory::getDBO();
		$db->setQuery("SELECT COUNT(*) AS count FROM #__magebridge_log");
		$result = $db->loadObject();
		return $result->count;
	}
}
