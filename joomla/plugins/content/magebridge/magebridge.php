<?php
/**
 * Joomla! MageBridge - Content plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

// Import the MageBridge autoloader
include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Content Plugin
 */
class plgContentMageBridge extends JPlugin
{
	/**
	 * Event onContentPrepare
	 * 
	 * @access public
	 * @param string $context
	 * @param object $row
	 * @param JParameter $params
	 * @param mixed $page
	 * @return null
	 */
	public function onContentPrepare($context, $row, $params, $page)
	{
		// Do not continue if not enabled
		if ($this->isEnabled() == false) {
			return false;
		}

		// Check for Magento CMS-tags
		if (!empty($row->text) && preg_match('/{{([^}]+)}}/', $row->text)) {

			// Get system variables
			$bridge = MageBridgeModelBridge::getInstance();

			// Include the MageBridge register
			$option = JFactory::getApplication()->input->getCmd('option');
			$key = md5(var_export($row, true)).':'.$option;
			$text = MageBridgeEncryptionHelper::base64_encode($row->text);

			// Conditionally load CSS
			if ($this->params->get('load_css') == 1 || $this->params->get('load_js') == 1) {
				$bridge->register('headers');
			}

			// Build the bridge
			$segment_id = $bridge->register('filter', $key, $text);
			$bridge->build();
		
			// Load CSS if needed
			if ($this->params->get('load_css') == 1) {
				$bridge->setHeaders('css');
			}

			// Load JavaScript if needed
			if ($this->params->get('load_js') == 1) {
				$bridge->setHeaders('js');
			}

			// Get the result from the bridge
			$result = $bridge->getSegmentData($segment_id);
			$result = MageBridgeEncryptionHelper::base64_decode($result);
			
			// Only replace the original if the new content exists
			if (!empty($result)) {
				$row->text = $result;
			}
		}
	}

	/**
	 * Joomla! 1.5 alias
	 * 
	 * @access public
	 * @param object $article
	 * @param JParameter $params
	 * @param mixed $limitstart
	 * @return null
	 */
	public function onPrepareContent(&$article, &$params, $limitstart)
	{
		$this->onContentPrepare('content', $article, $params, $limitstart);
	}

	/**
	 * Return whether MageBridge is available or not
	 * 
	 * @access private
	 * @param null
	 * @return mixed $value
	 */
	private function isEnabled()
	{
		if (class_exists('MageBridgeModelBridge')) {
			if (MageBridgeModelBridge::getInstance()->isOffline() == false) {
				return true;
			}
		}
		return false;
	}
}
