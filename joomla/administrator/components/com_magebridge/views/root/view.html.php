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
class MageBridgeViewRoot extends MageBridgeView
{
	/**
	 * Method to display the requested view
	 */
	public function display($tpl = null)
	{
		JToolBarHelper::title( JText::_('MageBridge') . ': ' . JText::_('Magento Admin Panel'), 'yireo' );
		parent::display($tpl);
	}
}
