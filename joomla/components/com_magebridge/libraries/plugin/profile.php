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

/**
 * Parent plugin-class
 */
class MageBridgePluginProfile extends MageBridgePlugin
{
	/**
	 * Constants
	 */
	const CONVERT_TO_JOOMLA = 1;
	const CONVERT_TO_MAGENTO = 2;

	/**
	 * Short name of this plugin
	 */
	protected $pluginName = null;

	/**
	 * Constructor
	 *
	 * @access	  protected
	 * @param	   object  $subject The object to observe
	 * @param	   array   $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->db = JFactory::getDBO();
	}

	/**
	 * Method to check whether this plugin is enabled or not
	 *
	 * @param null
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Convert a specific field 
	 *
	 * @param string $field
	 * @param int $type
	 * @return string
	 */
	public function convertField($field, $type = self::CONVERT_TO_JOOMLA) 
	{
		// Stop if we don't have a proper name set
		if (empty($this->pluginName)) {
			return null;
		}

		// Get the conversion-array
		$conversion = $this->getConversionArray();

		// Loop through the conversion to find the right match
		if (!empty($conversion)) {
			foreach ($conversion as $joomla => $magento) {
				if ($field == $magento && $type == self::CONVERT_TO_JOOMLA) {
					return $joomla;
				} else if ($field == $joomla && $type == self::CONVERT_TO_MAGENTO) {
					return $magento;
				}
			}
		}
		return null;
	}

	/**
	 * Get the configuration file
	 *
	 * @param null
	 * @return string
	 */
	public function getConfigFile()
	{
		// Determine the conversion-file
		$params = $this->getParams();
		$custom = $this->getPath($params->get('file', 'map').'.php');
		$default = $this->getPath('map.php');

		if ($custom == true) {
			return $custom;
		} else if ($default == true) {
			return $default;
		} else {
			return false;
		}
	}

	/**
	 * Get the conversion-array
	 * 
	 * @param null
	 * @return array
	 */
	public function getConversionArray()
	{
		static $conversion = null;
		if (!is_array($conversion)) {

			// Determine the conversion-file
			$config_file = $this->getConfigFile();
			MageBridgeModelDebug::getInstance()->trace('Config file', $config_file);

			// If the conversion-file can't be read, use an empty conversion array
			if ($config_file == false) {
				$conversion = array();
			} else {
				// Include the conversion-file
				include $config_file;
			}
		}

		return $conversion;
	}

	/**
	 * Get the right path to a file
	 *
	 * @param string $type
	 * @param string $filename
	 * @return string
	 */
	protected function getPath($filename)
	{
		$path = JPATH_SITE.'/plugins/magebridgeprofile/'.$this->pluginName.'/'.$filename;
		if (file_exists($path) && is_file($path)) {
			return $path;
		} else {
			return false;
		}
	}
}
