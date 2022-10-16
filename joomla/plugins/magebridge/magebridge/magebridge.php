<?php
/**
 * Joomla! MageBridge - Magento plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge MageBridge Plugin
 */
class PlgMagebridgeMagebridge extends MageBridgePlugin
{
    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var MageBridgeModelBridge
     */
    protected $bridge;

    /**
     * Initialization function
     */
    protected function initialize()
    {
        $this->bridge = MageBridgeModelBridge::getInstance();
    }

    /**
     * Return a MageBridge configuration parameter
     *
     * @param string $name
     *
     * @return mixed $value
     */
    private function getParam($name = null)
    {
        return MageBridgeModelConfig::load($name);
    }

    /**
     * Event onBeforeDisplayBlock
     *
     * @param string $block_name
     * @param mixed  $arguments
     * @param string $block_data
     */
    public function onBeforeDisplayBlock(&$block_name, $arguments, &$block_data)
    {
    }

    /**
     * Event onBeforeBuildMageBridge
     */
    public function onBeforeBuildMageBridge()
    {
        // Get the current Magento request
        $request = MageBridgeUrlHelper::getRequest();

        // Check for the logout-page
        if ($request == 'customer/account/logoutSuccess') {
            $this->app->logout();
        }

        // When visiting the checkout/cart/add URL without a valid session, the action will fail because the session does not exist yet
        // The following workaround makes sure we first redirect to another page (to initialize the session) after which we can add the product
        if (preg_match('/checkout\/cart\/add\//', $request) && !preg_match('/redirect=1/', $request)) {
            $session = $this->bridge->getMageSession(); // Check for the Magento session-key stored in the Joomla! session

            // Session is NOT yet initialized, therefor addtocart is not working yet either
            if (empty($session) && !empty($_COOKIE)) {
                // Redirect the client to an intermediate page to properly initialize the session
                $this->bridge->setHttpReferer(MageBridgeUrlHelper::route($request . '?redirect=1'));
                MageBridgeUrlHelper::setRequest('magebridge/redirect/index/url/' . base64_encode($request));
                MageBridgeModelBridgeMeta::getInstance()->reset();
            }
        }
    }

    /**
     * Event onAfterBuildMageBridge
     */
    public function onAfterBuildMageBridge()
    {
        // Perform actions on the frontend
        if ($this->app->isSite()) {
            $this->doDelayedRedirect();
            $this->doDelayedLogin();
        }
    }

    /**
     * Perform a delayed redirect
     */
    private function doDelayedRedirect()
    {
        $redirectUrl = $this->bridge->getSessionData('redirect_url');

        if (!empty($redirectUrl)) {
            $redirectUrl = MageBridgeUrlHelper::route($redirectUrl);
            $this->app->redirect($redirectUrl);
            $this->app->close();
        }
    }

    /**
     * Perform a delayed login
     */
    private function doDelayedLogin()
    {
        $userEmail = $this->bridge->getSessionData('customer/email');
        $userId = $this->bridge->getSessionData('customer/joomla_id');
        $userModel = MageBridge::getUser();

        return $userModel->postlogin($userEmail, $userId);
    }
}
