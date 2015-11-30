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
class MageBridgeModelBridgeHeaders extends MageBridgeModelBridgeSegment
{
	/**
	 * Boolean to see if ProtoType has been loaded
	 *
	 * @access private 
	 * @var $_has_prototype
	 */
	private $_has_prototype = false;

	/**
	 * List of scripts 
	 *
	 * @access private 
	 * @var $_scripts
	 */
	private $_scripts = null;

	/**
	 * List of stylesheets
	 *
	 * @access private 
	 * @var $_stylesheets
	 */
	private $_stylesheets = null;

	/**
	 * Singleton method
	 * 
	 * @param string $name
	 * @return object
	 */
	public static function getInstance($name = null)
	{
		return parent::getInstance('MageBridgeModelBridgeHeaders');
	}

	/**
	 * Load the data from the bridge
	 *
	 * @param null
	 * @return array
	 */
	public function getResponseData()
	{
		return MageBridge::getRegister()->getData('headers');
	}

	/**
	 * Get the Base JavaScript URL from the bridge
	 *
	 * @param null
	 * @return string
	 */
	public function getBaseJsUrl()
	{
		$bridge = MageBridge::getBridge();
		$url = $bridge->getSessionData('base_js_url');
		if (empty($url)) {
			$url = $bridge->getMagentoUrl().'js/';
		}

		if (JURI::getInstance()->isSSL()) {
			$url = preg_replace('/^(http|https)\:\/\//', 'https://', $url);
		} else {
			$url = preg_replace('/^(http|https)\:\/\//', 'http://', $url);
		}

		return $url;
	}

	/**
	 * Method to set the headers
	 *
	 * @param string $type
	 * @return bool
	 */
	public function setHeaders($type = 'all')
	{
		// Make sure the bridge is built
		MageBridge::getBridge()->build();

		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {
			return false;
		}

		// Check whether the bridge is offline
		$offline = MageBridge::getBridge()->isOffline();
		if ($offline == true) {
			return false;
		}

		static $set = array();
		if (in_array($type, $set)) {
			return false;
		}

		$headers = $this->getResponseData();
		switch($type) {
			case 'css':
				$set[] = 'css';
				$this->loadCss($headers);
				break;

			case 'js':
				$set[] = 'js';
				$this->loadJs($headers);
				break;

			case 'rss':
				$set[] = 'js';
				$this->loadRss($headers);
				break;

			default:
				$set[] = 'all';
				$set[] = 'css';
				$set[] = 'js';
				$this->loadCommon($headers);
				$this->loadCss($headers);
				$this->loadJs($headers);
				$this->loadRss($headers);
				break;
		}

		return true;
	}

	/**
	 * Method to load the common headers
	 *
	 * @param array $headers
	 * @return null
	 */
	public function loadCommon($headers)
	{
		// Do not load this in the backend
		$application = JFactory::getApplication();
		if ($application->isSite() == false) {
			return false;
		}

		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {
			return false;
		}

		// Check whether the bridge is offline
		$offline = MageBridge::getBridge()->isOffline();
		if ($offline == true) {
			return false;
		}

		$document = JFactory::getDocument();

		// Add common META-tags
		if (!empty($headers['title']))
		{
			$document->setTitle( $headers['title'] );
		}

		if (!empty($headers['keywords']))
		{
			$document->setMetaData( 'keywords', $headers['keywords'] );
		}

		if (!empty($headers['description']))
		{
			$metaDescription = $headers['description'];
			$metaDescription = str_replace('&nbsp;', ' ', $metaDescription);
			$metaDescription = strip_tags($metaDescription);
			$metaDescription = htmlspecialchars($metaDescription);
			$document->setMetaData('description', $metaDescription);
		}

		if (!empty($headers['robots'])) {
			$document->setMetaData( 'robots', htmlspecialchars($headers['robots']));
			@header('X-Robots-Tag: '.$headers['robots'], true);
		}

		// Add canonical tag
		if (MagebridgeModelConfig::load('enable_canonical') == 1) { 
			if (!empty($headers['items'])) {
				foreach ($headers['items'] as $item) {
					if ($item['type'] == 'link_rel' && !empty($item['name'] && stripos($item['params'], 'rel="canonical"') !== false)) {
						$document->addHeadLink($item['name'], 'canonical');
					}
				}
			}
		}
	}

	/**
	 * Method to load the CSS headers
	 * 
	 * @param array $headers
	 * @return null
	 */
	public function loadCss($headers)
	{
		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {
			return false;
		}

		// Check whether the bridge is offline
		$offline = MageBridge::getBridge()->isOffline();
		if ($offline == true) {
			return false;
		}

		// Initialize the internal array
		$this->_stylesheets = array();

		// Get system variables
		$document = JFactory::getDocument();

		// Load the CSS from the MageBridge component
		$this->loadDefaultCss();

		// Fetch the value of "disable_css_all"
		// * 0 = Do not disable any Magento CSS
		// * 1 = Disable all Magento CSS (so we just skip this step
		// * 2 = Disable only the CSS listed under "disable_css_custom"
		// * 3 = Disable all CSS except for the CSS listed under "disable_css_custom"
		$disable_css_all = MagebridgeModelConfig::load('disable_css_all');

		if ($disable_css_all != 1 && !empty($headers['items'])) {
			foreach ($headers['items'] as $item) {
				if ($item['type'] == 'skin_css' || $item['type'] == 'css') {

					if ($disable_css_all != 0 && MageBridgeHelper::cssIsDisabled($item['name']) == true) {
						continue;
					}

					$this->_stylesheets[] = $item['name'];

					$css = '';

					if ( !empty($item['if']) && strpos($item['if'], "><!-->") !== false ) {
						$css .= $item['if'] . "\n";
					} elseif (!empty($item['if'])) {
						$css .= '<!--[if '.$item['if'].' ]>'."\n";
					}

					$css .= '<link rel="stylesheet" href="'.$item['path'].'" type="text/css" '.$item['params'].'/>'."\n";

					if (!empty($item['if']) && strpos($item['if'], "><!-->") !== false) {
						$css .= '<!--<![endif]-->' . "\n";
					} elseif (!empty($item['if'])) {
						$css .= '<![endif]-->'."\n";
					}

					$document->addCustomTag($css);

					continue;
				}
			}
		}
	}

	/**
	 * Method to load the CSS headers
	 *
	 * @param null
	 * @return null
	 */
	public function loadDefaultCss()
	{
		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();

		if ($document->getType() != 'html') {
			return false;
		}

		// Check whether the bridge is offline
		$offline = MageBridge::getBridge()->isOffline();

		if ($offline == true) {
			return false;
		}

		// Determine whether to load the default CSS or not
		if (MagebridgeModelConfig::load('disable_default_css') == 0) { 

			// Load common stylesheets
			MageBridgeTemplateHelper::load('css', 'default.css');
			MageBridgeTemplateHelper::load('css', 'custom.css');

			// Load specific stylesheets per page
			if (MageBridgeTemplateHelper::isHomePage()) {
				MageBridgeTemplateHelper::load('css', 'homepage.css');
			}

			if (MageBridgeTemplateHelper::isProductPage()) {
				MageBridgeTemplateHelper::load('css', 'product.css');
			}

			if (MageBridgeTemplateHelper::isCategoryPage()) {
				MageBridgeTemplateHelper::load('css', 'category.css');
			}

			// Determine browser-specific stylesheets
			jimport('joomla.environment.browser');
			$browser = JBrowser::getInstance();
			if ($browser->getBrowser() == 'msie') MageBridgeTemplateHelper::load('css', 'default-ie.css');
			if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '6.0') MageBridgeTemplateHelper::load('css', 'default-ie6.css');
			if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '7.0') MageBridgeTemplateHelper::load('css', 'default-ie7.css');
			if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '8.0') MageBridgeTemplateHelper::load('css', 'default-ie8.css');
		}
	}

	/**
	 * Method to load the JavaScript headers
	 *
	 * @param array $headers
	 * @return null
	 */
	public function loadJs($headers)
	{
		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {
			return false;
		}

		// Check whether all scripts are disabled
		$disable_js = MagebridgeModelConfig::load('disable_js_mage');
		if ($disable_js == 'all') {
			return false;
		}

		// Check whether the bridge is offline
		$offline = MageBridge::getBridge()->isOffline();
		if ($offline == true) {
			return false;
		}

		// Initialize the internal array
		$this->_scripts = array();

		// Get system variables
		$bridge = MageBridge::getBridge();

		$html = "<script type=\"text/javascript\">\n"
			. "//<![CDATA[\n"
			. "var BLANK_URL = '".$this->getBaseJsUrl()."blank.html';\n"
			. "var BLANK_IMG = '".$this->getBaseJsUrl()."spacer.gif';\n"
			. "//]]>\n"
			. "</script>\n"
		;
		$document->addCustomTag($html);

		// Load Prototype 
		if ($this->loadPrototype() == true) {
			$this->_has_prototype = true;
		}

		// Loop through all the header-items fetched from Magento
		if (!empty($headers['items'])) {

			$jslist = array();
			$jstags = array();

			foreach ($headers['items'] as $item) {
				if ($item['type'] == 'skin_js' || $item['type'] == 'js') {

					if (MageBridgeHelper::jsIsDisabled($item['name']) == true) {
						continue;
					}

					$this->_stylesheets[] = $item['name'];
					$this->_scripts[] = $item['name'];

					if (empty($item['name'])) {
						continue;
					}

					// If this is a skin-script, construct the tag but add it later to the HTML-header
					if ($item['type'] == 'skin_js') {

						if (!preg_match('/^http/', $item['path'])) $item['path'] = $bridge->getMagentoUrl().$item['path'];
						$tag = '<script type="text/javascript" src="'.$item['path'].'"></script>'."\n";
						$jstags[] = $tag;
						continue;
					}

					// If this is a conditional script, construct the tag but add it later to the HTML-header
					if (!empty($item['if'])) {

						if (!preg_match('/^http/', $item['path'])) $item['path'] = $bridge->getMagentoUrl().$item['path'];

						$tag = '<script type="text/javascript" src="'.$item['path'].'"></script>'."\n";
						$tag = '<!--[if '.$item['if'].' ]>'."\n".$tag.'<![endif]-->'."\n";

						$jstags[] = $tag;
						continue;
					}

					// Detect Prototype
					if (strstr($item['path'], 'prototype') || strstr($item['path'], 'scriptaculous')) {

						$this->_has_prototype = true;

						// Load an optimized Prototype/script.acul.us version
						if (MagebridgeModelConfig::load('use_protoaculous') == 1 || MagebridgeModelConfig::load('use_protoculous') == 1) {
							$skip_scripts = array(
								'prototype/prototype.js',
								'scriptaculous/builder.js',
								'scriptaculous/effects.js',
								'scriptaculous/dragdrop.js',
								'scriptaculous/controls.js',
								'scriptaculous/slider.js',
							);

							if (in_array($item['name'], $skip_scripts)) {
								continue;
							}
						}

						// Skip these, if the Google API is already loaded
						if (MagebridgeModelConfig::load('use_google_api') == 1) {
							if (preg_match('/prototype.js$/', $item['name'])) continue;
							if (preg_match('/scriptaculous.js$/', $item['name'])) continue;
						}
					}

					// Detect jQuery and replace it
					if (preg_match('/jquery-([0-9]+)\.([0-9]+)\.([0-9]+)/', $item['path']) || 
						preg_match('/jquery.js$/', $item['path']) || 
						preg_match('/jquery.min.js$/', $item['path'])
						) { 
						if (MagebridgeModelConfig::load('replace_jquery') == 1) {
							MageBridgeTemplateHelper::load('jquery');
							continue;
						}
					}

					// Detect the translation script
					if (strstr($item['name'], 'translate.js')) {
						$translate = true;
					}

					// Load this script through JS merging or not
					if (MagebridgeModelConfig::load('merge_js') == 1) {
						$jslist[] = $item['name'];

					} else if (MagebridgeModelConfig::load('merge_js') == 2 && !empty($headers['merge_js'])) {
						// Don't do anything here yet

					} else {

						if (!preg_match('/^http/', $item['path'])) $item['path'] = $bridge->getMagentoUrl().$item['path'];
						$item['path'] = $this->convertUrl($item['path']);
						$tag = '<script type="text/javascript" src="'.$item['path'].'"></script>'."\n";
						$jstags[] = $tag;
					}
				}
			}

			if (MagebridgeModelConfig::load('merge_js') == 2 && !empty($headers['merge_js'])) {
				$this->addScript($headers['merge_js']);
			
			} else if (!empty($jslist)) {
				$this->addScript($this->getBaseJsUrl().'index.php?c=auto&amp;f=,'.implode(',', $jslist));
			}

			if (!empty($jstags)) {
				foreach ($jstags as $tag) {
					if (!empty($tag)) {
						$document->addCustomTag($tag);
					}
				}
			}
		}

		// Load some extra JavaScript tags
		if (isset($headers['custom'])) {
			foreach ($headers['custom'] as $custom) {
				$custom = MageBridgeEncryptionHelper::base64_decode($custom);
				$custom = preg_replace('/Mage.Cookies.domain([^;]+)\;/m', 'Mage.Cookies.domain = null;', $custom);
				$document->addCustomTag($custom);
			}
		} else if (isset($translate) && $translate == true) {
			$html = '<script type="text/javascript">var Translator = new Translate([]);</script>';
			$document->addCustomTag($html);
		}

		return;
	}

	/**
	 * Method to load feeds
	 *
	 * @param array $headers
	 * @return null
	 */
	public function loadRss($headers)
	{
		// Dot not load if this is not the right document-class
		$document = JFactory::getDocument();
		if ($document->getType() != 'html') {
			return false;
		}

		// Loop through the items
		if (!empty($headers['items'])) {
			foreach ($headers['items'] as $item) {

				// Fetch RSS-items
				if ($item['type'] == 'rss') {

					$url = $item['name'];
					$url = preg_replace('/(.*)index.php?option=com_magebridge/', '', $url );
					$url = MageBridgeHelper::filterUrl($url);
					$document->addHeadLink($url, 'alternate', 'rel', array('type' => 'application/rss+xml', 'title' => 'RSS 2.0'));
				}
			}
		}
	}

	/**
	 * Return the list of scripts
	 * 
	 * @param null
	 * @return array
	 */
	public function getScripts()
	{
		return $this->_scripts;
	}

	/**
	 * Add script
	 * 
	 * @param string
	 * @return null
	 */
	private function addScript($url)
	{
		$url = $this->convertUrl($url);
		$html = '<script type="text/javascript" src="'.$url.'"></script>';
		$document = JFactory::getDocument();
		$document->addCustomTag($html);
		return null;
	}

	/**
	 * Return the list of stylesheets
	 * 
	 * @param null
	 * @return array
	 */
	public function getStylesheets()
	{
		if ($this->_stylesheets == null) {
			$this->setHeaders('css');
		}

		return $this->_stylesheets;
	}

	/**
	 * Return whether ProtoType has been loaded or not
	 * 
	 * @param null
	 * @return bool
	 */
	public function hasProtoType()
	{
		return $this->_has_prototype;
	}

	/**
	 * Method to load ProtoType
	 */
	public function loadPrototype()
	{
		// Load Prototype through Google API
		if (MagebridgeModelConfig::load('use_google_api') == 1) {
			$this->addScript('http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js');
			$this->addScript('http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js');
			return true;

		// Load Protoaculous
		} else if (MagebridgeModelConfig::load('use_protoaculous') == 1) {
			$this->addScript('media/com_magebridge/js/protoaculous.1.9.0.min.js');
			return true;

		// Load Protoculous
		} else if (MagebridgeModelConfig::load('use_protoculous') == 1) {
			$this->addScript('media/com_magebridge/js/protoculous-1.0.2-packed.js');
			return true;
		}

		return false;
	}

	/**
	 * Guarantee a script or stylesheet loaded through SSL is also loaded through SSL
	 * 
	 * @param null
	 * @return bool
	 */
	public function convertUrl($url)
	{
		if(JURI::getInstance()->isSSL()) {
			$url = str_replace('http://', 'https://', $url);
		}
		return $url;
	}
}
