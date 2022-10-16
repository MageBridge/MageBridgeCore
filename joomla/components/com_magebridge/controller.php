<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
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
     * @var MageBridgeModelBridge
     */
    protected $bridge;

    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->registerTask('switch', 'switchStores');
        $this->registerTask('login', 'ssoCheck');
        $this->registerTask('logout', 'ssoCheck');

        $this->bridge = MageBridgeModelBridge::getInstance();

        $this->app   = JFactory::getApplication();
        $input       = $this->app->input;
        $post        = $input->post->getArray();
        $doCheckPost = $this->doCheckPost();

        $httpReferer = $this->getHttpReferer();
        $httpHost    = $this->getHttpHost();

        if ($doCheckPost && !empty($post)) {
            JSession::checkToken() or $this->forbidden('Invalid token');

            if (empty($httpReferer)) {
                $this->returnToRequestUri();
            }

            if (preg_match('/(http|https):\/\/' . $httpHost . '/', $httpReferer) == false) {
                $this->returnToRequestUri();
            }
        }

        $this->handleCustomerAddressDelete();
    }

    /**
     *
     */
    protected function handleCustomerAddressDelete()
    {
        $uri         = JUri::current();
        $httpReferer = $this->getHttpReferer();

        if (stristr($uri, '/customer/address/delete')) {
            if (empty($httpReferer)) {
                $this->returnToRequestUri();
            }

            if (preg_match('/(http|https):\/\/' . $this->getHttpHost() . '/', $httpReferer) == false) {
                $this->returnToRequestUri();
            }
        }
    }

    /**
     * @return string
     */
    protected function getHttpReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '';
    }

    /**
     * @return string
     */
    protected function getHttpHost()
    {
        return isset($_SERVER['HTTP_HOST']) ? trim($_SERVER['HTTP_HOST']) : '';
    }

    /**
     * @return bool
     */
    protected function doCheckPost()
    {
        $uri        = JUri::current();
        $checkPaths = ['customer', 'address', 'cart'];

        foreach ($checkPaths as $checkPath) {
            if (stristr($uri, '/' . $checkPath . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method to redirect back to the request URI itself
     */
    public function returnToRequestUri()
    {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    /**
     * Forbidden task
     *
     * @param string $message
     */
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
     *
     * @return null
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Check if the bridge is offline
        if ($this->bridge->isOffline()) {
            $this->app->input->set('view', 'offline');
            $this->app->input->set('layout', 'default');
        }

        // Set a default view
        if ($this->app->input->get('view') == '') {
            $this->app->input->set('view', 'root');
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
            $url     = $this->bridge->getMagentoAdminUrl($request);

            $this->setRedirect($url);

            return;
        }

        // Redirect if the layout is not supported by the view
        if ($this->app->input->get('view') == 'catalog' && !in_array($this->app->input->get('layout'), [
                'product',
                'category',
                'addtocart',
            ])
        ) {
            $url = MageBridgeUrlHelper::route('/');
            $this->setRedirect($url);

            return;
        }

        parent::display($cachable, $urlparams);
    }

    /**
     * Method to check SSO coming from Magento
     *
     * @return null
     */
    public function ssoCheck()
    {
        $user = JFactory::getUser();

        if (!$user->guest) {
            MageBridgeModelUserSSO::getInstance()
                ->checkSSOLogin();
            $this->app->close();
        } else {
            $this->setRedirect(JUri::base());
        }
    }

    /**
     * Method to check SSO coming from Magento
     *
     * @return null
     */
    public function proxy()
    {
        $url = $this->app->input->get('url');
        print file_get_contents($this->bridge->getMagentoUrl() . $url);
        $this->app->close();
    }

    /**
     * Method to switch Magento store by POST
     *
     * @return null
     */
    public function switchStores()
    {
        // Read the posted value
        $store = $this->app->input->getString('magebridge_store');

        if (!empty($store) && preg_match('/(g|v):(.*)/', $store, $match)) {
            if ($match[1] == 'v') {
                $this->app->setUserState('magebridge.store.type', 'store');
                $this->app->setUserState('magebridge.store.name', $match[2]);
            }

            if ($match[1] == 'g') {
                $this->app->setUserState('magebridge.store.type', 'group');
                $this->app->setUserState('magebridge.store.name', $match[2]);
            }
        }

        // Redirect to the previous URL
        $redirect = $this->app->input->getString('redirect');
        $this->app->redirect($redirect);
        $this->app->close();
    }
}
