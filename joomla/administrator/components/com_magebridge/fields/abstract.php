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
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Import required libraries
jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Generic Form Field-class 
 */
abstract class MagebridgeFormFieldAbstract extends JFormField
{
	/**
	 * Method to wrap the protected getInput() method
	 *
	 * @param null
	 * @return string
	 */
	public function getHtmlInput()
	{
		return $this->getInput();
	}

	/**
	 * Method to set the name
	 *
	 * @param mixed $value
	 * @return null
	 */
	public function setName($value = null)
	{
		$this->name = $value;
	}

	/**
	 * Method to set the value
	 *
	 * @param mixed $value
	 * @return null
	 */
	public function setValue($value = null)
	{
		$this->value = $value;
	}
}
