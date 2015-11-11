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
 * MageBridge Form Helper
 */
class MageBridgeFormHelper
{
	/**
	 * Method to get the HTML of a certain field
	 *
	 * @param null
	 * @return string
	 */
	static public function getField($type, $name, $value = null, $array = 'magebridge')
	{
		jimport('joomla.form.helper');
		jimport('joomla.form.form');

		$fileType = preg_replace('/^magebridge\./', '', $type);
		include_once JPATH_ADMINISTRATOR.'/components/com_magebridge/fields/'.$fileType.'.php';

		$form = new JForm('magebridge');
		$field = JFormHelper::loadFieldType($type);
		if (is_object($field) == false) {
			$message = JText::sprintf('COM_MAGEBRIDGE_UNKNOWN_FIELD', $type);
			JFactory::getApplication()->enqueueMessage($message, 'error');
			return null;
		}

		$field->setName($name);
		$field->setValue($value);
   
		return $field->getHtmlInput();
	}

	/**
	 * Get an object-list of all Joomla! usergroups
	 *
	 * @param null
	 * @return string
	 */
	static public function getUsergroupOptions()
	{
		$query = 'SELECT `id` AS `value`, `title` AS `text` FROM `#__usergroups` WHERE `parent_id` > 0';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
