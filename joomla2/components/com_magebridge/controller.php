<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
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
    /* 
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
    }

    /*
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
            JRequest::setVar('view' , 'offline');
            JRequest::setVar('layout' , 'default');
        }

        // Set a default view
        if (JRequest::getVar('view') == '') {
            JRequest::setVar('view' , 'root');
        }

        // Check for a admin action and redirect to admin
        // @todo: This also partially matches short custom admin-URLs
        //if (is_numeric(stripos(MageBridgeUrlHelper::getRequest(), MagebridgeModelConfig::load('backend')))) {
        //    $link = MagebridgeModelConfig::load('url').'index.php/'.MagebridgeModelConfig::load('backend');
        //    return $this->setRedirect($link);
        //}

        // Check for a logout action and perform a logout in Joomla! first
        if (MageBridgeUrlHelper::getRequest() == 'customer/account/logout') {
            $session = JFactory::getSession();
            $session->destroy();
        }

        // Redirect if the layout is not supported by the view
        if (JRequest::getVar('view') == 'catalog' && !in_array(JRequest::getVar('layout'), array('product', 'category', 'addtocart'))) {
            $url = MageBridgeUrlHelper::route('/');
            $this->setRedirect($url);
            return;
        }

        parent::display($cachable, $urlparams);
    }

    /*
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

    /*
     * Method to check SSO coming from Magento
     *
     * @param null
     * @return null
     */
    public function proxy()
    {
        $application = JFactory::getApplication();
        $url = JRequest::getVar('url');
        print file_get_contents(MageBridgeModelBridge::getMagentoUrl().$url);
        $application->close();
    }

    /*
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
        $store = JRequest::getString('magebridge_store');
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
        $redirect = JRequest::getString('redirect');
        $application->redirect($redirect);
    }
}
