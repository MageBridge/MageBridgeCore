<?php
/**
 * Joomla! MageBridge - System plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 *
 * @todo      : plgSystemMageBridgeHelperJavascript
 * @todo      : plgSystemMageBridgeHelperSsl
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge System Plugin
 */
class PlgSystemMageBridge extends MageBridgePlugin
{
    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var JDocument
     */
    protected $doc;

    /**
     * @var JInput
     */
    protected $input;

    /**
     * List of console messages
     */
    protected $console = [];

    /**
     * Initialize
     */
    public function initialize()
    {
        $this->doc = JFactory::getDocument();
        $this->input = $this->app->input;
        $this->replaceClasses();
        $this->loadLanguage();
    }

    /**
     * Event onAfterInitialise
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() === false) {
            return false;
        }

        // Include JForm elements
        $this->loadJform();

        // NewRelic support
        $this->loadNewRelic();

        // Perform actions on the frontend
        if ($this->app->isSite()) {
            // Deny iframes
            if ($this->params->get('deny_iframe')) {
                header('X-Frame-Options: DENY');
            }

            // Hard-spoof all MageBridge SEF URLs (for sh404SEF)
            if ($this->getParam('spoof_sef', 0) == 1) {
                $current_url = preg_replace('/\.html$/', '', $_SERVER['REQUEST_URI']);
                $root_item = MageBridgeUrlHelper::getRootItem();
                $root_item_id = ($root_item) ? $root_item->id : null;
                $bridge_url = JRoute::_('index.php?option=com_magebridge&view=root&Itemid=' . $root_item_id, false);

                if (substr($current_url, 0, strlen($bridge_url)) == $bridge_url) {
                    $request = substr_replace($current_url, '', 0, strlen($bridge_url));
                    $this->input->set('option', 'com_magebridge');
                    $this->input->set('view', 'root');
                    $this->input->set('Itemid', $root_item_id);
                    $this->input->set('request', $request);
                }
            }

            // Detect an user-login without remember-me tick
            if ($this->app->input->getString('option') == 'com_users' && $this->app->input->getString('task') == 'user.login') {
                $username = $this->app->input->getString('username');
                $password = $this->app->input->getString('password');
                $remember = $this->app->input->getString('remember');

                if (!empty($username) && !empty($password) && empty($remember)) {
                    $_COOKIE['persistent_shopping_cart'] = null;
                }
            }
        }
    }

    /**
     * Event onAfterRoute
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function onAfterRoute()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return;
        }

        if ($this->app->isSite()) {
            // Check for a different template
            $template = $this->loadConfig('template');

            if (!empty($template) && $this->app->input->getCmd('option') == 'com_magebridge') {
                // @todo: Include the second argument "styleParams" as well, and make sure it works under RocketTheme
                $this->app->setTemplate($template);
            }

            // Check for a different mobile-template
            $mobile_template = $this->loadConfig('mobile_joomla_theme');

            if (!empty($mobile_template) && MageBridgeTemplateHelper::isMobile() && $this->app->input->getCmd('option') == 'com_magebridge') {
                $this->app->setTemplate($mobile_template);
            }

            // Redirect to SSL or non-SSL if needed
            $this->redirectSSL();

            // Redirect to SEF or non-SEF if needed
            $this->redirectNonSef();

            // Redirect to the URL replacement
            $this->redirectUrlReplacement();

            // Redirect com_user
            $this->redirectComUser();

            // Handle any queued tasks
            $this->handleQueue();

            // Spoof the Magento login-form
            if ($this->getParam('spoof_magento_login', 0) == 1) {
                if ($this->spoofMagentoLoginForm() == true) {
                    return;
                }
            }

        // Backend actions
        } else {
            if ($this->app->isAdmin()) {
                // Handle SSO checks
                $this->handleSsoChecks();
            }
        }
    }

    /**
     * Event onAfterDispatch
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return;
        }

        // Display the component-only on specific pages
        /*
        $pages = array(
            'catalog/product/gallery/id/*',
            'catalog/product_compare/index',
        );

        if (MageBridgeTemplateHelper::isPage($pages)) {
            $this->input->set('tmpl', 'component');
        }
        */

        // Perform actions on the frontend
        if ($this->app->isSite() && $this->doc->getType() == 'html') {
            // Handle JavaScript conflicts
            $disableJsMootools = $this->loadConfig('disable_js_mootools');

            if ($disableJsMootools == 1) {
                $headData = $this->doc->getHeadData();

                if (isset($headData['script'])) {
                    foreach ($headData['script'] as $index => $headScript) {
                        if (preg_match('/window\.addEvent/', $headScript)) {
                            //$this->console[] = 'MageBridge removed inline MooTools scripts';
                            //unset($headdata['script'][$index]); // @todo: Make sure this does NOT remove all custom-tags
                            continue;
                        }
                    }

                    $this->doc->setHeadData($headData);
                }
            }

            // Add the debugging bar if configured
            $debugHelper = new MageBridgeDebugHelper();
            $debugHelper->addDebug();
        }
    }


    /**
     * Event onAfterRender
     *
     * @access public
     *
     * @param null
     *
     * @return null
     */
    public function onAfterRender()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Perform actions on the frontend
        if ($this->allowHandleJavaScript()) {
            // Handle JavaScript conflicts
            $this->handleJavaScript();
        }

        $this->storeHttpReferer();
    }

    private function storeHttpReferer()
    {
        // Store the HTTP-referer
        $bridge = MageBridge::getBridge();

        if (method_exists($bridge, 'storeHttpReferer')) {
            MageBridge::getBridge()
                ->storeHttpReferer();
        }
    }

    private function allowHandleJavaScript()
    {
        if ($this->app->isSite()) {
            return true;
        }

        if ($this->app->isAdmin() && $this->doc->getType() == 'html' && $this->input->getCmd('option') == 'com_magebridge' && $this->input->getCmd('view') == 'root') {
            return true;
        }

        return false;
    }

    /**
     * Include JForm namespace
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function loadJform()
    {
        if ($this->app->isSite()) {
            return;
        }

        jimport('joomla.form.form');
        JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_magebridge/fields');
    }

    /**
     * Add some functions for NewRelic
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function loadNewRelic()
    {
        if (extension_loaded('newrelic_add_custom_tracer')) {
            newrelic_add_custom_tracer('MageBridgeModelProxy::getCURL');
        }
    }

    /**
     * Method to redirect non-SEF URLs if enabled
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function redirectNonSef()
    {
        // Initialize variables
        $uri = JUri::getInstance();
        $post = $this->input->post->getArray();
        $enabled = $this->getParam('enable_nonsef_redirect', 1);

        // Redirect non-SEF URLs to their SEF-equivalent
        if ($enabled == 1 && empty($post) && JFactory::getConfig()
                ->get('sef') == 1 && $this->input->getCmd('option') == 'com_magebridge'
        ) {
            $request = str_replace($uri->base(), '', $uri->toString());

            // Detect the MageBridge component
            if (preg_match('/^index.php\?option=com_magebridge/', $request)) {
                $view = $this->app->input->getCmd('view');
                $controller = $this->app->input->getCmd('controller');
                $task = $this->app->input->getCmd('task');

                if ($request != JRoute::_($request) && $view != 'ajax' && $view != 'jsonrpc' && $view != 'block' && $controller != 'jsonrpc' && $task != 'login') {
                    $request = MageBridgeUrlHelper::getSefUrl($request);
                    $this->app->redirect($request);
                    $this->app->close();
                }
            } else {
                if ($this->loadConfig('enforce_rootmenu') == 1 && !empty($request)) {
                    $url = MageBridgeUrlHelper::route(MageBridgeUrlHelper::getRequest());

                    if (!preg_match('/^\//', $request)) {
                        $request = '/' . $request;
                    }

                    if ($request != $url && $this->app->input->getCmd('view') != 'ajax' && !preg_match('/\/?/', $url)) {
                        $this->app->redirect($url);
                        $this->app->close();
                    }
                }
            }
        }
    }

    /**
     * Method to redirect to URL replacements
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function redirectUrlReplacement()
    {
        // Initialize variables
        $enabled = $this->getParam('enable_urlreplacement_redirect', 1);
        $post = $this->input->post->getArray();

        // Exit if disabled or if we are not within the MageBridge component
        if ($enabled == 0 || !empty($post) || $this->input->getCmd('option') != 'com_magebridge') {
            return;
        }

        // Fetch the replacements and check whether the current URL is part of it
        $replacement_urls = MageBridgeUrlHelper::getReplacementUrls();

        if (!empty($replacement_urls)) {
            foreach ($replacement_urls as $replacement_url) {
                $source = $replacement_url->source;
                $destination = $replacement_url->destination;

                // Prepare the source URL
                if ($replacement_url->source_type == 0) {
                    $source = MageBridgeUrlHelper::route($source);
                    $source = preg_replace('/\/$/', '', $source);
                }

                $source = str_replace('/', '\/', $source);
                $source = preg_replace('/^(http|https)/', '', $source);

                // Prepare the destination URL
                if (preg_match('/^index\.php\?option=/', $destination)) {
                    $destination = JRoute::_($destination);
                }

                // Fix the destination URL to be a FQDN
                if (!preg_match('/^(http|https)\:\/\//', $destination)) {
                    $destination = JUri::base() . $destination;
                }

                if ($replacement_url->source_type == 1 && preg_match('/' . $source . '/', JUri::current())) {
                    header('Location: ' . $destination);
                    exit;
                } else {
                    if ($replacement_url->source_type == 0 && preg_match('/' . $source . '$/', JUri::current())) {
                        header('Location: ' . $destination);
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Method to redirect com_user if enabled
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function redirectComUser()
    {
        // Initialize variables
        $enabled = $this->getParam('enable_comuser_redirect', 0);
        $post = $this->input->post->getArray();
        $option = $this->input->getCmd('option');

        // Redirect com_user links
        if ($enabled == 1 && empty($post) && in_array($option, ['com_user', 'com_users'])) {
            $this->doRedirect('view', 'login', 'customer/account/login');
            $this->doRedirect('view', 'register', 'customer/account/login');
            $this->doRedirect('task', 'register', 'customer/account/login');
            $this->doRedirect('view', 'remind', 'customer/account/forgotpassword');
            $this->doRedirect('view', 'reset', 'customer/account/forgotpassword');
            $this->doRedirect('view', 'user', 'customer/account');
            $this->doRedirect('task', 'edit', 'customer/account');
        }
    }

    /**
     * Get the Magento Base URL
     *
     * @access private
     *
     * @param null
     *
     * @return string
     */
    private function getBaseUrl()
    {
        $url = MageBridge::getBridge()
            ->getMagentoUrl();

        return preg_replace('/^(https|http):\/\//', '', $url);
    }

    /**
     * Get the Magento Base JS URL
     *
     * @access private
     *
     * @param null
     *
     * @return string
     */
    private function getBaseJsUrl()
    {
        $url = MageBridge::getBridge()
            ->getSessionData('base_js_url');
        $url = preg_replace('/^(https|http):\/\//', '', $url);
        $url = preg_replace('/(js|js\/)$/', '', $url);

        return $url;
    }

    /**
     * /**
     * Method to determine which JavaScript to use and which not
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function handleJavaScript()
    {
        // Get MageBridge variables
        $disableJsMootools = $this->loadConfig('disable_js_mootools');
        $disableJsFootools = $this->loadConfig('disable_js_footools');
        $disableJsFrototype = $this->loadConfig('disable_js_frototype');
        $disable_js_jquery = $this->loadConfig('disable_js_jquery');
        $disable_js_prototype = $this->loadConfig('disable_js_prototype');
        $disable_js_custom = $this->loadConfig('disable_js_custom');
        $disable_js_all = $this->loadConfig('disable_js_all');
        $magento_js = MageBridgeModelBridgeHeaders::getInstance()
            ->getScripts();

        $uri = JUri::getInstance();
        $foo_script = JUri::root(true) . '/media/com_magebridge/js/foo.js';
        $footools_script = JUri::root(true) . '/media/com_magebridge/js/footools.min.js';
        $frototype_script = JUri::root(true) . '/media/com_magebridge/js/frototype.min.js';
        $base_url = $this->getBaseUrl();
        $base_js_url = $this->getBaseJsUrl();

        // Parse the disable_js_custom string into an array
        $disable_js_custom = explode(',', $disable_js_custom);

        foreach ($disable_js_custom as $index => $script) {
            $script = trim($script);

            if (!empty($script)) {
                $disable_js_custom[$index] = $script;
            }
        }

        // Fetch the body
        $body = JResponse::getBody();

        // Determine whether ProtoType is loaded
        $has_prototype = MageBridgeTemplateHelper::hasPrototypeJs();

        if ($has_prototype == false) {
            if (stristr($body, '/js/protoaculous') || stristr($body, '/js/protoculous') || stristr($body, '/prototype.js')) {
                $has_prototype = true;
            }
        }

        // Load the whitelist
        $whitelist = JFactory::getConfig()
            ->get('magebridge.script.whitelist');

        if (!is_array($whitelist)) {
            $whitelist = [];
        }

        // Add some items to the whitelist
        if ($disable_js_jquery == false) {
            $whitelist[] = 'media/system/js/calendar.js';
            $whitelist[] = 'media/system/js/calendar-setup.js';
            $whitelist[] = 'media/system/js/caption.js';
            $whitelist[] = '/com_jce/';
            $whitelist[] = '/footools.js';
            $whitelist[] = 'www.googleadservices.com';
            $whitelist[] = 'media/jui/js';
            $whitelist[] = 'protostar/js/template.js';
            $whitelist[] = 'js/core-uncompressed.js';
        }

        // Load the blacklist
        $blacklist = JFactory::getConfig()
            ->get('magebridge.script.blacklist');

        // Only parse the body, if MageBridge has loaded the ProtoType library and only if configured
        if ($has_prototype == true && ($disable_js_all > 0 || $disableJsMootools == 1 || !empty($disable_js_custom))) {
            // Disable MooTools (and caption) and replace it with FooTools
            if ($disableJsMootools == 1 && $disableJsFootools == 0) {
                $this->console[] = 'MageBridge removed MooTools core and replaced it with FooTools';
                $footools_tag = '<script type="text/javascript" src="' . $footools_script . '"></script>';
                $body = preg_replace('/\<script/', $footools_tag . "\n" . '<script ', $body, 1);
            }

            // Find all script tags
            preg_match_all('/\<script([^<]+)\>\<\/script\>/', $body, $tags);
            $commented = [];

            foreach ($tags[0] as $tag) {
                // Filter the src="" attribute
                preg_match('/src=([\"\']{1})([^\"\']+)/', $tag, $src);
                if (is_array($src) && !empty($src[2])) {
                    $script = $src[2];
                } else {
                    continue;
                }

                // Load the whitelist
                if (!empty($whitelist) && is_array($whitelist)) {
                    $match = false;

                    foreach ($whitelist as $w) {
                        if (stristr($script, $w)) {
                            $match = true;
                            break;
                        }
                    }

                    if ($match == true) {
                        continue;
                    }
                }

                // If this looks like a jQuery script, skip it
                if (stristr($script, 'jquery') && $disable_js_jquery == 0) {
                    continue;
                }

                // If this looks like a ProtoType script, skip it
                if ((stristr($script, 'scriptaculous') || stristr($script, 'prototype')) && $disable_js_prototype == 0) {
                    continue;
                }

                // If this looks like a MageBridge script, skip it
                if (stristr($script, 'com_magebridge')) {
                    continue;
                }

                // Skip URLs that seem to belong to Magento
                if (!empty($base_url) && (strstr($script, 'http://' . $base_url) || strstr($script, 'https://' . $base_url))) {
                    continue;
                } else {
                    if (!empty($base_js_url) && (strstr($script, 'http://' . $base_js_url) || strstr($script, 'https://' . $base_js_url))) {
                        continue;

                    // Skip Magento frontend scripts
                    } else {
                        if (preg_match('/\/skin\/frontend\//', $script)) {
                            continue;
                        } else {
                            // Do some more complex tests
                            $skip = false;

                            // Loop through the whitelist
                            if (!empty($magento_js)) {
                                foreach ($magento_js as $js) {
                                    if (strstr($script, $js)) {
                                        $skip = true;
                                        break;
                                    }
                                }
                            }
                            if ($skip == true) {
                                continue;
                            }

                            // Loop through the known Magento scripts
                            if (!empty($magento_js)) {
                                foreach ($magento_js as $js) {
                                    if (strstr($script, $js)) {
                                        $skip = true;
                                        break;
                                    }
                                }
                            }
                            if ($skip == true) {
                                continue;
                            }
                        }
                    }
                }

                // Decide whether to remove this script by default
                if ($disable_js_all == 1 || $disable_js_all == 3) {
                    $remove = true;
                } else {
                    $remove = false;
                }

                // Load the blacklist
                if (!empty($blacklist) && is_array($blacklist)) {
                    foreach ($blacklist as $b) {
                        if (preg_match('/' . str_replace('/', '\/', $js) . '$/', $b)) {
                            $remove = true;
                            break;
                        }
                    }
                }

                // Scan for exceptions
                if ($disable_js_all > 1 && !empty($disable_js_custom)) {
                    foreach ($disable_js_custom as $js) {
                        if (preg_match('/' . str_replace('/', '\/', $js) . '$/', $script)) {
                            $remove = ($disable_js_all == 2) ? true : false;
                            break;
                        }
                    }

                // Disable MooTools
                } else {
                    if ($disableJsMootools == 1) {
                        $mootools_scripts = [
                            'media/system/js/modal.js',
                            'media/system/js/validate.js',
                            'beez_20/javascript/hide.js',
                            'md_stylechanger.js',
                            'media/com_finder/js/autocompleter.js',];

                        if (MageBridgeHelper::isJoomla25()) {
                            $mootools_scripts[] = 'media/system/js/caption.js';
                        }

                        if (preg_match('/mootools/', $script)) {
                            $remove = true;
                        }
                        foreach ($mootools_scripts as $js) {
                            if (preg_match('/' . str_replace('/', '\/', $js) . '$/', $script)) {
                                $remove = true;
                            }
                        }
                    }
                }

                // Remove this script
                if ($remove) {
                    // Decide how to remove the scripts
                    $filter = $this->getParam('filter_js', 'foo');

                    // Remove the script entirely from the page
                    if ($filter == 'remove') {
                        $body = str_replace($tag . "\n", '', $body);
                        $body = str_replace($tag, '', $body);

                    // Comment the tag
                    } else {
                        if ($filter == 'comment') {
                            if (!in_array($tag, $commented)) {
                                $commented[] = $tag;
                                $body = str_replace($tag, '<!-- MB: ' . $tag . ' -->', $body);
                            }

                        // Replace the script with the foo-script
                        } else {
                            $this->console[] = 'MageBridge removed ' . $script;
                            $body = str_replace($script, $foo_script, $body);
                        }
                    }
                }
            }

            // Log to the JavaScript Console
            if (MageBridgeModelDebug::isDebug() == true && $this->loadConfig('debug_console') == 1) {
                $console = '';
                foreach ($this->console as $c) {
                    $console .= 'console.warn("' . $c . '");';
                }
                $script = "<script type=\"text/javascript\">\n" . $console . "\n</script>";
                $body = str_replace('<head>', '<head>' . $script, $body);
            }

            // Set the body
            JResponse::setBody($body);
        } else {
            // Add FrotoType to the page
            if ($disableJsFrototype == 0) {
                $body = JResponse::getBody();

                $frototype_tag = '<script type="text/javascript" src="' . $frototype_script . '"></script>';
                $body = preg_replace('/\<script/', $frototype_tag . "\n" . '<script ', $body, 1);

                JResponse::setBody($body);
            }
        }
    }

    /**
     * Handle SSO checks
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function handleSsoChecks()
    {
        return;

        if ($this->input->getCmd('task') == 'login') {
            $user = JFactory::getUser();

            if (!$user->guest) {
                MageBridgeModelUserSSO::getInstance()->checkSSOLogin();
                $this->app->close();
            }
        }
    }

    /**
     * Handle task queues
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function handleQueue()
    {
        // Get the current session
        $session = JFactory::getSession();

        // Check whether some request is in the queue
        $tasks = $session->get('com_magebridge.task_queue');

        /*
        // @todo: Remove deprecated code
        if (!empty($tasks) && is_array($tasks)) {
            foreach ($tasks as $task) {

                if ($task == 'cbsync' || $task == 'jomsocialsync') {
                    $cb = MageBridgeConnectorProfile::getInstance()->getConnector('cb');
                    $cb->synchronize(JFactory::getUser()->id);
                }

                if ($task == 'jomsocialsync') {
                    $jomsocial = MageBridgeConnectorProfile::getInstance()->getConnector('jomsocial');
                    $jomsocial->synchronize(JFactory::getUser()->id);
                }
            }
        }

        // Add things to the queue, because some bastard extensions do not use events properly
        $tasks = array();

        // Add a CB profile-sync
        if ($this->getParam('spoof_cb_events')) {
            if ($this->input->getCmd('option') == 'com_comprofiler'
                && $this->input->getCmd('task') == 'saveUserEdit'
                && JFactory::getUser()->id == $this->input->getInt('id', 0, 'post')) {

                $tasks[] = 'cnsync';
            }
        }

        // Add a JomSocial profile-sync
        if ($this->getParam('spoof_jomsocial_events')) {
            if ($this->input->getCmd('option') == 'com_community'
                && $this->input->getCmd('view') == 'profile'
                && in_array($this->input->getCmd('task'), array('edit', 'editDetails'))
                && $this->input->getCmd('action', null, 'post') == 'save') {

                $tasks[] = 'jomsocialsync';
            }
        }
        */

        // Save the task queue in the session
        $session->set('com_magebridge.task_queue', $tasks);
    }

    /**
     * Method to set SSL if needed
     *
     * @access private
     *
     * @param null
     *
     * @return null
     */
    private function redirectSSL()
    {
        // Get system variables
        $uri = JUri::getInstance();
        $enforce_ssl = $this->loadConfig('enforce_ssl');
        $from_http_to_https = $this->getParam('enable_ssl_redirect', 1);
        $from_https_to_http = $this->getParam('enable_nonssl_redirect', 1);
        $post = $this->input->post->getArray();

        // Match situation where we don't want to redirect
        if (!empty($post)) {
            return false;
        }

        if (in_array($this->input->getCmd('view'), ['ajax', 'jsonrpc'])) {
            return false;
        }

        if (in_array($this->input->getCmd('task'), ['ajax', 'json'])) {
            return false;
        }

        if (in_array($this->input->getCmd('controller'), ['ajax', 'jsonrpc'])) {
            return false;
        }

        // Check the Menu-Item settings
        $menu = $this->app->getMenu();
        $active = $menu->getActive();

        if (!empty($active)) {
            $secureMenuItem = ($active->params->get('secure', 0) == 1) ? true : false;
        } else {
            $secureMenuItem = false;
        }

        // Check if SSL should be forced
        if ($uri->isSSL() == false && $this->getParam('enable_ssl_redirect', 1) == 1) {
            // Determine whether to do a redirect
            $redirect = false;

            // Do not redirect if SSL is disabled
            if ($enforce_ssl == 0) {
                $redirect = false;

            // Set the redirect for the entire Joomla! site
            } else {
                if ($enforce_ssl == 1) {
                    $redirect = true;

                // Set the redirect for MageBridge only
                } else {
                    if ($enforce_ssl == 2) {
                        // MageBridge links
                        if ($this->input->getCmd('option') == 'com_magebridge') {
                            // Prevent redirection when doing Single Sign On
                            if ($this->input->getCmd('task') != 'login') {
                                $redirect = true;
                            }
                        } else {
                            if ($secureMenuItem == 1) {
                                $redirect = true;
                            }
                        }
                    } else {
                        if ($enforce_ssl == 3) {
                            if ($this->input->getCmd('option') == 'com_magebridge') {
                                $redirect = (MageBridgeUrlHelper::isSSLPage()) ? true : false;
                            } else {
                                if ($secureMenuItem == 1) {
                                    $redirect = true;
                                }
                            }
                        }
                    }
                }
            }

            // Redirect to SSL
            if ($redirect == true) {
                $uri->setScheme('https');
                $this->app->redirect($uri->toString());
                $this->app->close();
            }

        // Check if non-SSL should be forced
        } else {
            if ($uri->isSSL() == true && $this->getParam('enable_nonssl_redirect', 1) == 1) {
                // Determine whether to do a redirect
                $redirect = false;
                $components = ['com_magebridge', 'com_scriptmerge'];

                // Set the redirect if SSL is disabled
                if ($enforce_ssl == 0) {
                    $redirect = true;

                // Do not redirect if SSL is set for the entire site
                } else {
                    if ($enforce_ssl == 1) {
                        $redirect = false;

                    // Do redirect if SSL is set for the shop only
                    } else {
                        if ($enforce_ssl == 2) {
                            if (!in_array($this->input->getCmd('option'), $components)) {
                                $redirect = true;
                                if ($secureMenuItem == 1) {
                                    $redirect = false;
                                }
                            }

                        // Set the redirect if SSL is only enabled for MageBridge
                        } else {
                            if ($enforce_ssl == 3) {
                                if ($this->input->getCmd('option') == 'com_magebridge') {
                                    $redirect = (MageBridgeUrlHelper::isSSLPage()) ? false : true;
                                } else {
                                    $redirect = true;
                                    if ($secureMenuItem == 1) {
                                        $redirect = false;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($redirect == true) {
                    $uri->setScheme('http');
                    $this->app->redirect($uri->toString());
                    $this->app->close();
                }
            }
        }
    }

    /**
     * Spoof the Magento login-form
     *
     * @access private
     *
     * @param null
     *
     * @return bool
     */
    private function spoofMagentoLoginForm()
    {
        // Fetch important variables
        $login = $this->input->getVar('login', [], 'post', 'array');
        $option = $this->input->getCmd('option');

        // Detect a Magento login-POST
        if ($option == 'com_magebridge' && !empty($login['username']) && !empty($login['password'])) {
            // Convert the Magento POST into Joomla! POST-credentials
            $credentials = [
                'username' => $login['username'],
                'password' => $login['password'],];

            // Try to login into the Joomla! application
            $rt = $this->app->login($credentials);

            // If the login is succesfull, we do not submit build the bridge any further, but redirect right away
            if ($rt == true) {
                $url = MageBridgeUrlHelper::route('customer/account');
                $this->app->redirect($url);
                $this->app->close();

                return true;
            }
        }

        return false;
    }

    /**
     * Load a specific parameter
     *
     * @access private
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getParam($name, $default = null)
    {
        return $this->params->get($name, $default);
    }

    /**
     * Redirect a specific URL
     *
     * @access private
     *
     * @param string $name
     * @param string $value
     * @param string $redirect
     *
     * @return null
     */
    private function doRedirect($name = '', $value = '', $redirect = null)
    {
        if ($this->input->getCmd($name) == $value) {
            $return = base64_decode($this->input->getString('return'));

            if (!empty($return)) {
                $return = MageBridgeEncryptionHelper::base64_encode($return);
                $redirect .= '/referer/' . $return . '/';
            }

            header('Location: ' . MageBridgeUrlHelper::route($redirect));
            exit;
        }
    }

    /**
     * Load a configuration value
     *
     * @access private
     *
     * @param string $name
     *
     * @return null
     */
    private function loadConfig($name)
    {
        return MageBridgeModelConfig::load($name);
    }

    /**
     * Simple check to see if MageBridge exists
     *
     * @access private
     *
     * @param null
     *
     * @return bool
     */
    private function isEnabled()
    {
        // Import the MageBridge autoloader
        include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

        // Check if the MageBridge class exists
        if (class_exists('MageBridgeModelBridge')) {
            return true;
        }

        return false;
    }

    /**
     * Method to override existing classes with MageBridge customized classes
     *
     * @return bool
     */
    protected function replaceClasses()
    {
        if ($this->params->get('override_core', 1) == 0) {
            return false;
        }

        if ($this->app->isSite() == false) {
            return false;
        }

        $overrides = [
            'JHtmlBehavior' => [
                'original' => JPATH_LIBRARIES . '/cms/html/behavior.php',
                'override' => __DIR__ . '/overrides/html/behavior.php',],];

        foreach ($overrides as $originalClass => $override) {
            if (file_exists($override['original']) == false) {
                continue;
            }

            if (file_exists($override['override']) == false) {
                continue;
            }

            $originalContent = file_get_contents($override['original']);

            if (empty($originalContent)) {
                continue;
            }

            $originalContent = str_replace('<?php', "namespace Joomla;", $originalContent);
            $originalContent = preg_replace('/J([a-zA-Z0-9]+)::/', "\\J$1::", $originalContent);
            eval($originalContent);

            if (class_exists('\Joomla\\' . $originalClass) == false) {
                continue;
            }

            require_once $override['override'];
        }

        return true;
    }
}
