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
require_once 'view.common.php';

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewElement extends MageBridgeViewCommon
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		// Determine the layout and data 
		switch(JFactory::getApplication()->input->getCmd('type')) {

			case 'product':
				$this->doProductLayout();
				break;

			case 'customer':
				$this->doCustomerLayout();
				break;

			case 'widget':
				$this->doWidgetLayout();
				break;

			default:
			case 'category':
				$this->doCategoryLayout();
				break;
		}

		parent::display($tpl);
	}
}
