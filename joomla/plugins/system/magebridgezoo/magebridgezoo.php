<?php
/**
 * Joomla! MageBridge - ZOO System plugin
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
 * MageBridge ZOO System Plugin
 */
class plgSystemMageBridgeZoo extends JPlugin
{
	/**
	 * Event onAfterRender
	 *
	 * @access public
	 * @param null
	 * @return null
	 */
	public function onAfterRender()
	{
		// Don't do anything if MageBridge is not enabled 
		if ($this->isEnabled() == false) return false;

		if (JFactory::getApplication()->input->getCmd('option') == 'com_zoo') {

			$body = JResponse::getBody();

			// Check for Magento CMS-tags
			if (preg_match('/\{\{([^}]+)\}\}/', $body) || preg_match('/\{mb([^}]+)\}/', $body)) {

				// Get system variables
				$bridge = MageBridgeModelBridge::getInstance();
				$register = MageBridgeModelRegister::getInstance();

				// Detect the request-tag
				if (preg_match_all('/\{mbrequest url="([^\"]+)"\}/', $body, $matches)) {
					foreach($matches[0] as $matchIndex => $match) {
						$url = $matches[1][$matchIndex];
						MageBridgeUrlHelper::setRequest($url);
						$body = str_replace($match, '', $body);
					}
				}

				// Detect block-names
				if (preg_match_all('/\{mbblock name="([^\"]+)"\}/', $body, $matches)) {
					foreach($matches[0] as $matchIndex => $match) {
						$block_name = $matches[1][$matchIndex];
						$register->add('block', $block_name);
					}
				}

				// Include the MageBridge register
				$key = md5(var_export($body, true)).':'.JFactory::getApplication()->input->getCmd('option');
				$text = MageBridgeEncryptionHelper::base64_encode($body);

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
					$body = $result;
				}

				// Detect block-names
				if (preg_match_all('/\{mbblock name="([^\"]+)"\}/', $body, $matches)) {
					foreach($matches[0] as $matchIndex => $match) {
						$block_name = $matches[1][$matchIndex];
						$block = $bridge->getBlock($block_name);
						$body = str_replace($match, $block, $body);
					}
				}
			}

			if (!empty($body)) {
				JResponse::setBody($body);
			}
		}
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
		if (is_file(JPATH_SITE.'/components/com_magebridge/models/config.php')) return true;
		return false;
	}
}
