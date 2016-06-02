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
	 * @deprecated Use $this->getMeta('frontend_form') instead
	 */
	protected $_frontend_form = false;

	/**
	 * Name of the XML-file containing the JForm definitions (if any)
	 *
	 * @var int
	 * @deprecated Use $this->getMeta('form_name') instead
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
		// Do not continue if this is not the right backend
		if ($this->app->isAdmin() == false && $this->getMeta('frontend_form') == false)
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

		// Bind the data
		$data = $this->getData();
		$form->bind(array('item' => $data));

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

	/*
	 * Helper method to override the name of the form
	 */
	public function setFormName($form_name)
	{
		$this->setMeta('form_name', $form_name);
	}

	/**
	 * @return string
	 */
	protected function detectXmlFile()
	{
		$option = $this->getOption();
		$xmlFile = JPATH_ADMINISTRATOR . '/components/' . $option . '/models/' . $this->getMeta('form_name') . '.xml';

		if (!file_exists($xmlFile))
		{
			$xmlFile = JPATH_SITE . '/components/' . $option . '/models/' . $this->getMeta('form_name') . '.xml';
		}

		return $xmlFile;
	}

	/**
	 * @param $xmlFile
	 *
	 * @return JForm
	 */
	protected function getFormFromXml($xmlFile)
	{
		jimport('joomla.form.form');
		return JForm::getInstance('item', $xmlFile);
	}
}