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

/**
 * Main bridge class
 */
class MageBridgeModelBridgeWidget extends MageBridgeModelBridgeSegment
{
	/**
	 * Singleton 
	 * 
	 * @param string $name
	 * @return object
	 */
	public static function getInstance($name = null)
	{
		return parent::getInstance('MageBridgeModelBridgeWidget');
	}

	/**
	 * Load the data from the bridge
	 * 
	 * @param string $name
	 * @param mixed $arguments
	 * @return mixed
	 */
	public function getResponseData($name, $arguments = null)
	{
		return MageBridgeModelRegister::getInstance()->getData('widget', $name, $arguments);
	}

	/**
	 * Check wheather this widget is cachable
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function isCachable($name)
	{
		$response = parent::getResponse('widget', $name);
		if (isset($response['meta']['allow_caching']) && $response['meta']['allow_caching'] == 1 &&
			isset($response['meta']['cache_lifetime']) && $response['meta']['cache_lifetime'] > 0) {
			return true;
		} 
		return false; 
	}

	/**
	 * Method to return a specific widget
	 * 
	 * @param string $widget_name
	 * @param mixed $arguments
	 * @return string
	 */
	public function getWidget($widget_name, $arguments = null)
	{
		// Make sure the bridge is built
		MageBridgeModelBridge::getInstance()->build();

		// Get the response-data
		$segment = $this->getResponse('widget', $widget_name, $arguments);

		if (!isset($segment['data'])) {
			return null;
		}

		// Parse the response-data
		$widget_data = $segment['data'];
		if (!empty($widget_data)) {
			if (!isset($segment['cache'])) {
				$widget_data = self::decode($widget_data);
				$widget_data = self::filterHtml($widget_data);
			}
		}

		return $widget_data;
	}

	/**
	 * Method to decode the widget-output
	 * 
	 * @param string $widget_name
	 * @return string
	 */
	public function decode($widget_data)
	{
		$widget_data = MageBridgeEncryptionHelper::base64_decode($widget_data);
		return $widget_data;
	}

	/**
	 * Method to filter the HTML with the MageBridge URL filter but also generic Content Filters
	 * 
	 * @param string $html
	 * @return string
	 */
	public function filterHtml($html)
	{
		// Fix everything regarding URLs
		$html = MageBridgeHelper::filterContent($html);

		// Replace URLs where neccessary
		$replacement_urls = MageBridgeUrlHelper::getReplacementUrls();
		if (!empty($replacement_urls)) {
			foreach ($replacement_urls as $replacement_url) {

				$source = $replacement_url->source;
				$destination = $replacement_url->destination;

				// Prepare the source URL
				if ($replacement_url->source_type == 0) {
					$source = MageBridgeUrlHelper::route($source);
				} else {
					$source = str_replace('/', '\/', $source);
				}

				// Prepare the destination URL
				if (preg_match('/^index\.php\?option=/', $destination)) {
					$destination = JRoute::_($destination);
				}

				// Replace the actual URLs
				if ($replacement_url->source_type == 0) {
					$html = str_replace($source.'\'', $destination.'\'', $html);
					$html = str_replace($source.'"', $destination.'"', $html);
				} else {
					$html = preg_replace('/href=\"([^\"]+)'.$source.'([^\"]+)/', 'href="'.$destination, $html);
				}
			}
		}

		return $html;
	}
}
