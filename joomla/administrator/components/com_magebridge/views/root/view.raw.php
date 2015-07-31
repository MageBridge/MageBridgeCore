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
require_once JPATH_SITE.'/components/com_magebridge/view.php';

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewRoot extends MageBridgeView
{
	/**
	 * Method to display the requested view
	 */
	public function display($tpl = null)
	{
		// Get useful variables
		$application = JFactory::getApplication();

		// Set the admin-request
		MageBridgeUrlHelper::setRequest(JFactory::getApplication()->input->get('request', 'admin'));

		// Set which block to display
		$this->setBlock('root');

		// Build the bridge right away, because we need data from Magento
		$block = $this->build();

		echo $block;
		$application->close();
		exit;
	}
}
