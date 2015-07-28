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
require_once JPATH_COMPONENT.'/libraries/view/form.php';

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewCheck extends YireoView
{
	protected $loadToolbar = false;

	/**
	 * List of all checks
	 */
	private $_checks = array();

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		if (JFactory::getApplication()->input->getCmd('layout') == 'browser') {
			$this->displayBrowser($tpl);

		} elseif (JFactory::getApplication()->input->getCmd('layout') == 'product') {
			$this->displayProduct($tpl);

		} elseif (JFactory::getApplication()->input->getCmd('layout') == 'result') {
			$this->displayResult($tpl);

		} else {
			$this->displayDefault($tpl);
		}
	}

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function displayDefault($tpl)
	{
		// Initalize common elements
		MageBridgeViewHelper::initialize('Check');

		// Load libraries
		JHTML::_('behavior.tooltip');

		JToolBarHelper::custom( 'refresh', 'preview.png', 'preview_f2.png', 'Refresh', false );

		$this->checks = $this->get('checks');

		parent::display($tpl);
	}

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function displayProduct($tpl)
	{
		// Load the form if it's there
		$this->getModel()->setFormName('check_product');
		$this->_viewParent = 'form';
		$this->form = $this->get('Form');

		// Initalize common elements
		MageBridgeViewHelper::initialize('PRODUCT_RELATION_TEST');

		JToolBarHelper::custom('check_product', 'preview.png', 'preview_f2.png', 'Run', false);

		parent::display('product');
	}

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function displayBrowser($tpl)
	{
		// Initalize common elements
		MageBridgeViewHelper::initialize('Internal Browse Test');

		JToolBarHelper::custom( 'refresh', 'preview.png', 'preview_f2.png', 'Browse', false );

		$this->url = MagebridgeModelConfig::load('url').'magebridge.php';
		$this->host = MagebridgeModelConfig::load('host');

		parent::display('browser');
	}

	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function displayResult($tpl)
	{
		// Fetch configuration data
		$url = MagebridgeModelConfig::load('url').'magebridge.php';
		$host = MagebridgeModelConfig::load('host');

		// Do basic resolving on the host if it is not an IP-address
		if (preg_match('/^([0-9\.]+)$/', $host) == false) {
			$host = preg_replace('/\:[0-9]+$/', '', $host);
			if (gethostbyname($host) == $host) {
				die('ERROR: Failed to resolve hostname "'.$host.'" in DNS');
			}
		}

		// Try to open a socket to port 80
		if (fsockopen($host, 80, $errno, $errmsg, 5) == false) {
			die('ERROR: Failed to open a connection to host "'.$host.'" on port 80. Perhaps a firewall is in the way?');
		}

		// Fetch content through the proxy
		$responses = array();

		// Fetch various responses
		$responses[] = $this->fetchContent('Basic bridge connection succeeded', $url, array('mbtest' => 1));
		$responses[] = $this->fetchContent('API authentication succeeded', $url, array('mbauthtest' => 1));
		echo implode('<br/>', $responses);
		exit;
	}

	protected function fetchContent($label, $url, $params)
	{
		// Initialize the proxy
		$proxy = MageBridgeModelProxy::getInstance();
		$proxy->setAllowRedirects(false);
		$content = $proxy->getRemote($url, $params, 'post');

		// Detect proxy errors
		$proxy_error = $proxy->getProxyError();
		if (!empty($proxy_error)) {
			die('ERROR: Proxy error: '.$proxy_error);
		}

		// Detect the HTTP status
		$http_status = $proxy->getHttpStatus();
		if ($http_status != 200) {
			die('ERROR: Encountered a HTTP Status '.$http_status);
		}

		// Parse the content
		if (!empty($content)) {

			// Detect HTML-page
			if (preg_match('/\<\/html\>$/', $content)) {
				die('ERROR: Data contains HTML not JSON');
			}

			$data = json_decode($content, true);
			if (!empty($data)) {
				if (array_key_exists('meta', $data)) {
					return 'SUCCESS: '.$label;
				}
				die('ERROR: JSON response contains unknown data: '.var_export($data, true));

			} else {
				die('ERROR: Failed to decode JSON');
			}
		} else {
			die('ERROR: Empty content');
		}
	}
}
