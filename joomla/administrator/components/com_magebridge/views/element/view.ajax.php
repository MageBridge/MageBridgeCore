<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
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
		$layoutType = $this->app->input->getCmd('type');

		// Determine the layout and data 
		switch($layoutType) {

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

		$tpl = $layoutType;

		parent::display($tpl);
	}
}
