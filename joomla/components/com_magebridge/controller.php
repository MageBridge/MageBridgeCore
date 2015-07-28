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
 * MageBridge Controller 
 *
 * @package MageBridge
 */
class MageBridgeController extends YireoAbstractController
{
	/** 
	 * Constructor
	 *
	 * @param null
	 * @return null
	 */
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('switch', 'switchStores');
		$this->registerTask('login', 'ssoCheck');
		$this->registerTask('logout', 'ssoCheck');

		$uri = JURI::current();
		$input = JFactory::getApplication()->input;
		$post = JFactory::getApplication()->input->get('post');
		$httpReferer = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : null;
		$httpHost = isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : null;

		$checkPaths = array('customer', 'address', 'cart');
		$doCheckPost = false;

		foreach ($checkPaths as $checkPath)
		{
			if (stristr($uri, '/' . $checkPath . '/'))
			{
				$doCheckPost = true;
			}
		}

		if ($doCheckPost && !empty($post))
		{
			JSession::checkToken() or $this->forbidden('Invalid token');

			if (empty($httpReferer))
			{
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
			}

			if (preg_match('/(http|https):\/\/' . $httpHost . '/', $httpReferer) == false)
			{
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
			}
		}

		if (stristr($uri, '/customer/address/delete'))
		{
			if (empty($httpReferer))
			{
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
			}

			if (preg_match('/(http|https):\/\/' . $httpHost . '/', $httpReferer) == false)
			{
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}

	public function forbidden($message = 'Access denied')
	{
		header('HTTP/1.0 403 Forbidden');
		die($message);
	}

	/**
	 * Default method showing a JView
	 *
	 * @param boolean $cachable
	 * @param boolean $urlparams
	 * @return null
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Check if the bridge is offline
		if (MageBridge::getBridge()->isOffline()) {
			JFactory::getApplication()->input->setVar('view' , 'offline');
			JFactory::getApplication()->input->setVar('layout' , 'default');
		}

		// Set a default view
		if (JFactory::getApplication()->input->getVar('view') == '') {
			JFactory::getApplication()->input->setVar('view' , 'root');
		}

		// Check for a logout action and perform a logout in Joomla! first
		$request = MageBridgeUrlHelper::getRequest();
		if ($request == 'customer/account/logout') {
			$session = JFactory::getSession();
			$session->destroy();
		}

		// Check for an admin request
		$backend = MageBridgeModelConfig::load('backend');
		if (!empty($backend) && substr($request, 0, strlen($backend)) === $backend) {
			$request = str_replace($backend, '', $request);
			$url = MageBridgeModelBridge::getInstance()->getMagentoAdminUrl($request);
			$this->setRedirect($url);
			return;
		}

		// Redirect if the layout is not supported by the view
		if (JFactory::getApplication()->input->getVar('view') == 'catalog' && !in_array(JFactory::getApplication()->input->getVar('layout'), array('product', 'category', 'addtocart'))) {
			$url = MageBridgeUrlHelper::route('/');
			$this->setRedirect($url);
			return;
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Method to check SSO coming from Magento
	 *
	 * @param null
	 * @return null
	 */
	public function ssoCheck()
	{
		$application = JFactory::getApplication();
		$user = JFactory::getUser();
		if (!$user->guest) {
			MageBridgeModelUserSSO::checkSSOLogin();
			$application->close();
		} else {
			$this->setRedirect(JURI::base());
		}
	}

	/**
	 * Method to check SSO coming from Magento
	 *
	 * @param null
	 * @return null
	 */
	public function proxy()
	{
		$application = JFactory::getApplication();
		$url = JFactory::getApplication()->input->getVar('url');
		print file_get_contents(MageBridgeModelBridge::getMagentoUrl().$url);
		$application->close();
	}

	/**
	 * Method to switch Magento store by POST
	 *
	 * @param null
	 * @return null
	 */
	public function switchStores()
	{
		// Initialize system variables
		$application = JFactory::getApplication();

		// Read the posted value
		$store = JFactory::getApplication()->input->getString('magebridge_store');
		if (!empty($store) && preg_match('/(g|v):(.*)/', $store, $match)) {
			if ($match[1] == 'v') {
				$application->setUserState('magebridge.store.type', 'store');
				$application->setUserState('magebridge.store.name', $match[2]);
			} else if ($match[1] == 'g') {
				$application->setUserState('magebridge.store.type', 'group');
				$application->setUserState('magebridge.store.name', $match[2]);
			}
		}

		// Redirect to the previous URL
		$redirect = JFactory::getApplication()->input->getString('redirect');
		$application->redirect($redirect);
	}
}
