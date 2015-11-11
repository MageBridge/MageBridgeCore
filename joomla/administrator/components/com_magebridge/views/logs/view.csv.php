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

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewLogs extends MageBridgeView
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		$filename = 'magebridge-debug-'.MagebridgeModelConfig::load('supportkey').'.csv';

		header('Expires: 0');
		header('Cache-control: private');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-disposition: attachment; filename='.$filename);

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__magebridge_log WHERE 1=1');
		$rows = $db->loadObjectList();

		$body = '';
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$data = array(
					$row->id,
					$row->message,
					$this->printType($row->type),
					$row->remote_addr,
					$row->origin,
				);
				foreach ($data as $index => $value) {
					$data[$index] = '"'.str_replace('"', '`', $value).'"';
				}
				$body .= implode(',', $data)."\r\n";
			}
		}

		print $body;
	}

	/**
	 * Helper-method to get list of log-types
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
		);
		return $types;
	}

	/**
	 * Helper-method to get the title of a specific type
	 *
	 * @param null
	 * @return array
	 */
	public function printType($type)
	{
		$types = $this->getTypes();
		foreach ($types as $name => $value) {
			if ($type == $value) return JText::_($name);
		}
	}
}
