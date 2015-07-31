<?php
/**
 * Joomla! MageBridge - JoomlArt T3 System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

/** Extra notes:
 * Make sure this plugin is published before the T3 Framework Plugin.
 * Future additions may include choosing a proper profile through a GET-variable,
 * which should be defined in templates/TEMPLATE/local/etc/profiles/PROFILE.ini:
 *	 desktop_layout=full-width
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * MageBridge JoomlArt T3 System Plugin
 */
class plgSystemMageBridgeT3 extends JPlugin
{
	/**
	 * Event onAfterDispatch
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onAfterInitialise()
	{
		// Get rid of annoying cookies
		$application = JFactory::getApplication();
		$cookie = $application->getTemplate().'_layouts';
		unset($_COOKIE[$cookie]);
	}

	/**
	 * Event onAfterDispatch
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onAfterRoute()
	{
		// Don't do anything if MageBridge is not enabled 
		if ($this->isEnabled() == false) return false;

		// Change the layout only for MageBridge-pages
		$view = JFactory::getApplication()->input->getCmd('view');
		$request = JFactory::getApplication()->input->getString('request');
		if ($view == 'root') {

			// Magento homepage
			if (empty($request)) {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_homepage', 'full-width'));

			// Magento customer or sales pages
			} else if (preg_match('/^(customer|sales)/', $request))  {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_customer', 'full-width'));

			// Magento product-pages
			} else if (preg_match('/^catalog\/product/', $request))  {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_product', 'full-width'));

			// Magento category-pages
			} else if (preg_match('/^catalog\/category/', $request))  {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_category', 'full-width'));

			// Magento cart-pages
			} else if (preg_match('/^checkout\/cart/', $request))  {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_cart', 'full-width'));

			// Magento checkout-pages
			} else if (preg_match('/^checkout/', $request))  {
				JFactory::getApplication()->input->set('layouts', $this->getParams()->get('layout_checkout', 'full-width'));

			}
		}
	}

	/**
	 * Load the parameters
	 *
	 * @access private
	 * @param null
	 * @return JParameter
	 */
	private function getParams()
	{
		return $this->params;
	}

	/**
	 * Simple check to see if MageBridge exists
	 * 
	 * @access private
	 * @param null
	 * @return bool
	 */
	private function isEnabled()
	{
		if (JFactory::getApplication()->isSite() == false) return false;

		$template = JFactory::getApplication()->getTemplate();
		if(preg_match('/^ja_/', $template) == false) {
			return false;
		}

		if (JFactory::getApplication()->input->getCmd('option') != 'com_magebridge') return false;
		if (is_file(JPATH_SITE.'/components/com_magebridge/models/config.php')) return true;
		return false;
	}
}
