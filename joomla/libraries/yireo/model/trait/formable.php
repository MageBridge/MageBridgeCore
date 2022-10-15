<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Yireo Model Trait: Formable - allows models to have a form
 *
 * @package Yireo
 */
trait YireoModelTraitFormable
{
	/**
	 * Boolean to allow forms in the frontend
	 *
	 * @var bool
	 * @deprecated Use $this->getConfig('frontend_form') instead
	 */
	protected $_frontend_form = false;

	/**
	 * Name of the XML-file containing the JForm definitions (if any)
	 *
	 * @var int
	 * @deprecated Use $this->getConfig('form_name') instead
	 */
	protected $_form_name = '';

	/**
	 * Method to get a XML-based form
	 *
	 * @param array $data
	 * @param bool  $loadData
	 *
	 * @return mixed
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Do not continue if this form is not allowed
		if (!$form = $this->loadForm())
		{
			return false;
		}

		if (empty($data))
		{
			$data = $this->getData();
		}

		// Bind the data
		if ($loadData)
		{
			$form->bind(array('item' => $data));
		}

		// Insert the params-data if set
		if (!empty($data->params))
		{
			$params = $data->params;

			if (is_string($params))
			{
				$registry = YireoHelper::toRegistry($params);
				$params   = $registry->toArray();
			}

			$form->bind(array('params' => $params));
		}

		return $form;
	}

	/**
	 * Allow usage of this form
	 *
	 * @return false|JForm
	 */
	protected function loadForm()
	{
		// Do not continue if this is not the right backend
		if ($this->app->isAdmin() == false && $this->getConfig('frontend_form') == false)
		{
			return false;
		}

		// Do not continue if this is not a singular view
		if (method_exists($this, 'isSingular') && $this->isSingular() == false)
		{
			return false;
		}

		// Read the form from XML
		$xmlFile = $this->detectXmlFile();

		if (!file_exists($xmlFile))
		{
			return false;
		}

		// Construct the form-object
		$form = $this->getFormFromXml($xmlFile);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/*
	 * Helper method to override the name of the form
	 *
	 * @param string $form_name
	 *
	 * @deprecated Use $this->setConfig('form_name', $form_name) instead
	 */
	public function setFormName($form_name)
	{
		$this->setConfig('form_name', $form_name);
	}

	/**
	 * Get the form name
	 *
	 * @return string
	 */
	public function getFormName()
	{
		$formName = $this->getConfig('form_name');

		if (empty($formName))
		{
			$formName = $this->getConfig('table_alias');
		}

		return $formName;
	}

	/**
	 * Detect the XML file containing the form
	 *
	 * @return string
	 */
	protected function detectXmlFile()
	{
		$option  = $this->getOption();
		$xmlFile = JPATH_ADMINISTRATOR . '/components/' . $option . '/models/' . $this->getFormName() . '.xml';

		if (!file_exists($xmlFile))
		{
			$xmlFile = JPATH_SITE . '/components/' . $option . '/models/' . $this->getFormName() . '.xml';
		}

		return $xmlFile;
	}

	/**
	 * Get the JForm object from an XML file
	 *
	 * @param $xmlFile
	 *
	 * @return JForm
	 */
	protected function getFormFromXml($xmlFile)
	{
		jimport('joomla.form.form');

		return JForm::getInstance('item', $xmlFile);
	}

	/**
	 * Method to temporarily store an object in the current session
	 *
	 * @param array $data
	 */
	public function saveTmpSession($data)
	{
		$session = JFactory::getSession();
		$session->set($this->getConfig('option_id'), $data);
	}

	/**
	 * Method to temporarily store an object in the current session
	 */
	public function loadTmpSession()
	{
		$session = JFactory::getSession();
		$data    = $session->get($this->getConfig('option_id'));

		if (empty($data))
		{
			return false;
		}

		foreach ($data as $name => $value)
		{
			if (!empty($value))
			{
				$this->data->$name = $value;
			}
		}

		return true;
	}

	/**
	 * Method to temporarily store an object in the current session
	 */
	public function resetTmpSession()
	{
		$session = JFactory::getSession();
		$session->clear($this->getConfig('option_id'));
	}
}