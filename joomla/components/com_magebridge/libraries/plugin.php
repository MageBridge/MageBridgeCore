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

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * Parent plugin-class
 */
class MageBridgePlugin extends JPlugin
{
	/**
	 * Method to check whether a specific component is there
	 *
	 * @param string $component
	 * @return bool
	 */
	protected function checkComponent($component)
	{
		jimport('joomla.application.component.helper');
		if (is_dir(JPATH_ADMINISTRATOR.'/components/'.$component) && JComponentHelper::isEnabled($component) == true) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getParams()
	{
		return $this->params;
	}
}
