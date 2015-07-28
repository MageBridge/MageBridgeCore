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

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent class
require_once JPATH_COMPONENT.'/view.php';

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewContent extends MageBridgeView
{
	/**
	 * Method to display the requested view
	 */
	public function display($tpl = null)
	{
		$application = JFactory::getApplication();
		$params = $application->getParams();

		// Set the request based upon the choosen layout
		switch($this->getLayout()) {
			case 'logout':
				$intermediate_page = $params->get('intermediate_page');
				if ($intermediate_page != 1) {
					$this->setRequest('customer/account/logout');
				} else {
					$this->logout_url = MageBridgeUrlHelper::route('customer/account/logout');
				}
				break;

			default:
				$this->setRequest(MageBridgeUrlHelper::getLayoutUrl($this->getLayout()));
				break;
		}

		// Set which block to display
		$this->setBlock('content');
		
		// Assign the parameters to this template
        $this->params = $params;

		parent::display($tpl);
	}
}
