<?php
/**
 * Joomla! MageBridge - System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 * 
 * @todo: Move various helper-methods to various helper-classes
 * - plgSystemMageBridgeHelperJavascript
 * - plgSystemMageBridgeHelperSsl
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * MageBridge System Plugin
 */
class plgSystemMageBridge extends JPlugin
{
    /**
     * List of console messages
     */
    protected $console = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $subject
	 * @param array $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

    /**
     * Event onAfterInitialise
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        // NewRelic support
        $this->loadNewRelic();

        // Perform actions on the frontend
        $application = JFactory::getApplication();
        if ($application->isSite()) {

            // Hard-spoof all MageBridge SEF URLs (for sh404SEF)
            if ($this->getParam('spoof_sef', 0) == 1) {
                $current_url = preg_replace('/\.html$/', '', $_SERVER['REQUEST_URI']);
                $root_item = MageBridgeUrlHelper::getRootItem();
                $root_item_id = ($root_item) ? $root_item->id : null;
                $bridge_url = JRoute::_('index.php?option=com_magebridge&view=root&Itemid='.$root_item_id, false);
                if (substr($current_url, 0, strlen($bridge_url)) == $bridge_url) {
                    $request = substr_replace($current_url, '', 0, strlen($bridge_url));
                    JRequest::setVar('option', 'com_magebridge');
                    JRequest::setVar('view', 'root');
                    JRequest::setVar('Itemid', $root_item_id);
                    JRequest::setVar('request', $request);
                }
            }

            // Detect an user-login without remember-me tick
            if(JRequest::getVar('option') == 'com_users' && JRequest::getVar('task') == 'user.login') {
                $username = JRequest::getVar('username');
                $password = JRequest::getVar('password');
                $remember = JRequest::getVar('remember');
                if(!empty($username) && !empty($password) && empty($remember)) {
                    $_COOKIE['persistent_shopping_cart'] = null;
                }
            }
        }
    }

    /**
     * Event onAfterRoute
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRoute()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        // Load core overrides
        $this->loadOverrides();

        $application = JFactory::getApplication();
        if ($application->isSite()) {

            // Check for a different template
            $template = $this->loadConfig('template');
            if (!empty($template) && JRequest::getCmd('option') == 'com_magebridge') {
                $application->setTemplate($template); // @todo: Include the second argument "styleParams" as well, and make sure it works under RocketTheme
            }

            // Check for a different mobile-template
            $mtemplate = $this->loadConfig('mobile_joomla_theme');
            if (!empty($mtemplate) && MageBridgeTemplateHelper::isMobile()) {
                $application->setTemplate($mtemplate);
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
        } else if ($application->isAdmin()) {
            
            // Handle SSO checks
            $this->handleSsoChecks();
        }
    }

    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        // Display the component-only on specific pages
        /*
        $pages = array(
            'catalog/product/gallery/id/*',
            'catalog/product_compare/index',
        );

        if (MageBridgeTemplateHelper::isPage($pages)) {
            JRequest::setVar('tmpl', 'component');
        }
        */

        // Perform actions on the frontend
        $application = JFactory::getApplication();
        $document = JFactory::getDocument();
        if ($application->isSite() && $document->getType() == 'html') {

            // Handle JavaScript conflicts
            $disable_js_mootools = $this->loadConfig('disable_js_mootools');
            if ($disable_js_mootools == 1) {
                $headdata = $document->getHeadData();
                if (isset($headdata['script'])) {
                    foreach ($headdata['script'] as $index => $headscript) {
                        if (preg_match('/window\.addEvent/', $headscript)) {
                            //$this->console[] = 'MageBridge removed inline MooTools scripts';
                            //unset($headdata['script'][$index]); // @todo: Make sure this does NOT remove all custom-tags
                            continue;
                        }
                    }
                    $document->setHeadData($headdata);
                }
            }

            // Add the debugging bar if configured
            MageBridgeDebugHelper::addDebug(); 
        }
    }


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

        // Perform actions on the frontend
        $application = JFactory::getApplication();
        $document = JFactory::getDocument();
        if ($application->isSite() || ($application->isAdmin() && $document->getType() == 'html' && JRequest::getCmd('option') == 'com_magebridge' && JRequest::getCmd('view') == 'root')) {

            // Handle JavaScript conflicts
            $this->handleJavaScript();

        }

        // Store the HTTP-referer
        $bridge = MageBridge::getBridge();
        if (method_exists($bridge, 'storeHttpReferer')) {
            MageBridge::getBridge()->storeHttpReferer();
        }
    }

    /*
     * Event onPrepareModuleList (used by Advanced Module Manager)
     *
     * @access public
     * @param array $modules
     * @return null
     */
    public function onPrepareModuleList(&$modules)
    {
        foreach ($modules as $id => $module) {
            if (MageBridgeTemplateHelper::allowPosition($module->position) == false) {
                unset($modules[$id]);
                continue;
            } 
        }
    }

    /**
     * Add some functions for NewRelic
     * 
     * @access private
     * @param null
     * @return null
     */
    private function loadNewRelic()
    {
        if (extension_loaded('newrelic_add_custom_tracer')) {
            newrelic_add_custom_tracer('MageBridgeModelProxy::getCURL');
        }
    }

    /**
     * Load overrides of the Joomla! core
     * 
     * @access private
     * @param null
     * @return bool
     */
    private function loadOverrides()
    {
        $application = JFactory::getApplication();
        if ($application->isSite()) {

            // Detect whether we can load the module-helper
            $classes = get_declared_classes();
            if (!in_array('JModuleHelper', $classes) && !in_array('jmodulehelper', $classes)) {
                $loadModuleHelper = true;
            } else {
                $loadModuleHelper = false;
            }

            // Import the custom module helper - this is needed to make it possible to flush certain positions 
            if ($this->getParam('override_modulehelper', 1) == 1 && $loadModuleHelper == true) {
                $component_path = JPATH_SITE.'/components/com_magebridge/';
                if (MageBridgeHelper::isJoomlaVersion('2.5')) {
                    @include_once($component_path.'rewrite/25/joomla/application/module/helper.php');
                } else if (MageBridgeHelper::isJoomlaVersion('3.0')) {
                    @include_once($component_path.'rewrite/30/joomla/application/module/helper.php');
                } else if (MageBridgeHelper::isJoomlaVersion('3.1')) {
                    @include_once($component_path.'rewrite/31/cms/application/module/helper.php');
                } else {
                    @include_once($component_path.'rewrite/32/cms/application/module/helper.php');
                }
            }
        }
    }
    /**
     * Method to redirect non-SEF URLs if enabled
     *
     * @access private
     * @param null
     * @return null
     */
    private function redirectNonSef()
    {
        // Initialize variables
        $application = JFactory::getApplication();
        $uri = JURI::getInstance();
        $post = JRequest::get('post');
        $enabled = $this->getParam('enable_nonsef_redirect', 1);

        // Redirect non-SEF URLs to their SEF-equivalent
        if ($enabled == 1 && empty($post) && $application->getCfg('sef') == 1 && JRequest::getCmd('option') == 'com_magebridge') {

            $request = str_replace( $uri->base(), '', $uri->toString());

            // Detect the MageBridge component
            if (preg_match('/^index.php\?option=com_magebridge/', $request)) {

                $view = JRequest::getCmd('view');
                $controller = JRequest::getCmd('controller');
                $task = JRequest::getCmd('task');
                if ($request != JRoute::_($request) && $view != 'ajax' && $view != 'jsonrpc' && $view != 'block' && $controller != 'jsonrpc' && $task != 'login') {
                    $request = MageBridgeUrlHelper::getSefUrl($request);
                    $application->redirect($request);
                    $application->close();
                }

            } else if ($this->loadConfig('enforce_rootmenu') == 1 && !empty($request)) {

                $url = MageBridgeUrlHelper::route(MageBridgeUrlHelper::getRequest());
                if (!preg_match('/^\//', $request)) $request = '/'.$request;
                if ($request != $url && JRequest::getCmd('view') != 'ajax' && !preg_match('/\/?/', $url)) {
                    $application->redirect($url);
                    $application->close();
                }
            }
        }
    }

    /**
     * Method to redirect to URL replacements
     *
     * @access private
     * @param null
     * @return null
     */
    private function redirectUrlReplacement()
    {
        // Initialize variables
        $enabled = $this->getParam('enable_urlreplacement_redirect', 1); 
        $post = JRequest::get('post');

        // Exit if disabled or if we are not within the MageBridge component
        if ($enabled == 0 || !empty($post) || JRequest::getCmd('option') != 'com_magebridge') {
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

                // Prepare the destination URL
                if (preg_match('/^index\.php\?option=/', $destination)) {
                    $destination = JRoute::_($destination);
                }

                // Fix the destination URL to be a FQDN
                if (!preg_match('/^(http|https)\:\/\//', $destination)) {
                    $destination = JURI::base().$destination;
                }

                if ($replacement_url->source_type == 1 && preg_match('/'.$source.'/', JURI::current())) {
                    header('Location: '.$destination);
                    exit;
                } else if ($replacement_url->source_type == 0 && preg_match('/'.$source.'$/', JURI::current())) {
                    header('Location: '.$destination);
                    exit;
                }
            }
        }
    }

    /**
     * Method to redirect com_user if enabled
     *
     * @access private
     * @param null
     * @return null
     */
    private function redirectComUser()
    {
        // Initialize variables
        $enabled = $this->getParam('enable_comuser_redirect', 0); 
        $post = JRequest::get('post');
        $option = JRequest::getCmd('option');

        // Redirect com_user links
        if ($enabled == 1 && empty($post) && in_array($option, array('com_user', 'com_users'))) {
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
     * @param null
     * @return string
     */
    private function getBaseUrl()
    {
        $url = MageBridge::getBridge()->getMagentoUrl();
        return preg_replace('/^(https|http):\/\//', '', $url);
    }

    /**
     * Get the Magento Base JS URL
     *
     * @access private
     * @param null
     * @return string
     */
    private function getBaseJsUrl()
    {
        $url = MageBridge::getBridge()->getSessionData('base_js_url');
        $url = preg_replace('/^(https|http):\/\//', '', $url);
        $url = preg_replace('/(js|js\/)$/', '', $url);
        return $url;
    }

    /**
    /**
     * Method to determine which JavaScript to use and which not
     *
     * @access private
     * @param null
     * @return null
     */
    private function handleJavaScript()
    {
        // Get MageBridge variables
        $disable_js_mootools = $this->loadConfig('disable_js_mootools');
        $disable_js_footools = $this->loadConfig('disable_js_footools');
        $disable_js_frototype = $this->loadConfig('disable_js_frototype');
        $disable_js_jquery = $this->loadConfig('disable_js_jquery');
        $disable_js_prototype = $this->loadConfig('disable_js_prototype');
        $disable_js_custom = $this->loadConfig('disable_js_custom');
        $disable_js_all = $this->loadConfig('disable_js_all');
        $magento_js = MageBridgeModelBridgeHeaders::getInstance()->getScripts();

        $uri = JURI::getInstance();
        $foo_script = JURI::root(true).'/media/com_magebridge/js/foo.js';
        $footools_script = JURI::root(true).'/media/com_magebridge/js/footools.min.js';
        $frototype_script = JURI::root(true).'/media/com_magebridge/js/frototype.min.js';
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
        $whitelist = JFactory::getConfig()->get('magebridge.script.whitelist');
        if (!is_array($whitelist)) {
            $whitelist = array();
        }

        // Add some items to the whitelist
        if($disable_js_all == false && $disable_js_jquery == false) {
            $whitelist[] = 'media/system/js/calendar.js';
            $whitelist[] = 'media/system/js/calendar-setup.js';
            $whitelist[] = '/com_jce/';
            $whitelist[] = '/footools.js';
            $whitelist[] = 'www.googleadservices.com';
            $whitelist[] = 'media/jui/js';
        }

        // Load the blacklist
        $blacklist = JFactory::getConfig()->get('magebridge.script.blacklist');

        // Only parse the body, if MageBridge has loaded the ProtoType library and only if configured
        if ($has_prototype == true && ($disable_js_all > 0 || $disable_js_mootools == 1 || !empty($disable_js_custom))) {

            // Disable MooTools (and caption) and replace it with FooTools
            if ($disable_js_mootools == 1 && $disable_js_footools == 0) {
                $this->console[] = 'MageBridge removed MooTools core and replaced it with FooTools';
                $footools_tag = '<script type="text/javascript" src="'.$footools_script.'"></script>';
                $body = preg_replace('/\<script/', $footools_tag."\n".'<script ', $body, 1);
            }

            // Find all script tags
            preg_match_all('/\<script([^<]+)\>\<\/script\>/', $body, $tags);
            $commented = array();
            foreach ($tags[0] as $tag) {

                // Filter the src="" attribute
                preg_match( '/src=([\"\']{1})([^\"\']+)/', $tag, $src);
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
                    if ($match == true) continue;
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
                if (!empty($base_url) && (strstr($script, 'http://'.$base_url) || strstr($script, 'https://'.$base_url))) {
                    continue;
                } else if (!empty($base_js_url) && (strstr($script, 'http://'.$base_js_url) || strstr($script, 'https://'.$base_js_url))) {
                    continue;

                // Skip Magento frontend scripts
                } else if (preg_match('/\/skin\/frontend\//', $script)) {
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
                    if ($skip == true) continue;

                    // Loop through the known Magento scripts
                    if (!empty($magento_js)) {
                        foreach ($magento_js as $js) {
                            if (strstr($script, $js)) {
                                $skip = true;
                                break;   
                            }
                        }
                    }
                    if ($skip == true) continue;
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
                        if (preg_match('/'.str_replace('/', '\/', $js).'$/', $b)) {
                            $remove = true;
                            break;
                        }
                    }
                }

                // Scan for exceptions
                if ($disable_js_all > 1 && !empty($disable_js_custom)) {
                    foreach ($disable_js_custom as $js) {
                        if (preg_match('/'.str_replace('/', '\/', $js).'$/', $script)) {
                            $remove = ($disable_js_all == 2) ? true : false;
                            break;
                        }
                    }

                // Disable MooTools
                } else if ($disable_js_mootools == 1) {

                    $mootools_scripts = array(
                        'media/system/js/modal.js',
                        'media/system/js/validate.js',
                        'beez_20/javascript/hide.js',
                        'md_stylechanger.js',
                        'media/com_finder/js/autocompleter.js',
                    );
    
                    if (MageBridgeHelper::isJoomla25()) {
                        $mootools_scripts[] = 'media/system/js/caption.js';
                    }

                    if (preg_match('/mootools/', $script)) {
                        $remove = true;
                    }
                    foreach ($mootools_scripts as $js) {
                        if (preg_match('/'.str_replace('/', '\/', $js).'$/', $script)) {
                            $remove = true;
                        }
                    }
                }

                // Remove this script
                if ($remove) {

                    // Decide how to remove the scripts
                    $filter = $this->getParam('filter_js', 'foo');

                    // Remove the script entirely from the page
                    if ($filter == 'remove') {
                        $body = str_replace($tag."\n", '', $body);
                        $body = str_replace($tag, '', $body);

                    // Comment the tag
                    } else if ($filter == 'comment') {
                        
                        if (!in_array($tag, $commented)) {
                            $commented[] = $tag;
                            $body = str_replace($tag, '<!-- MB: '.$tag.' -->', $body);
                        }

                    // Replace the script with the foo-script
                    } else {
                        $this->console[] = 'MageBridge removed '.$script;
                        $body = str_replace($script, $foo_script, $body);
                    }
                }
            }

            // Log to the JavaScript Console
            if (MagebridgeModelDebug::isDebug() == true && $this->loadConfig('debug_console') == 1) {
                $console = '';
                foreach ($this->console as $c) {
                    $console .= 'console.warn("'.$c.'");';
                }
                $script = "<script type=\"text/javascript\">\n".$console."\n</script>";
                $body = str_replace('<head>', '<head>'.$script, $body);
            }

            // Set the body
            JResponse::setBody($body);

        } else {

            // Add FrotoType to the page
            if ($disable_js_frototype == 0) {

                $body = JResponse::getBody();

                $frototype_tag = '<script type="text/javascript" src="'.$frototype_script.'"></script>';
                $body = preg_replace('/\<script/', $frototype_tag."\n".'<script ', $body, 1);

                JResponse::setBody($body);
            }

        }
    }

    /**
     * Handle SSO checks
     * 
     * @access private
     * @param null
     * @return null
     */
    private function handleSsoChecks()
    {
        return;
        if (JRequest::getCmd('task') == 'login') {
            $application =& JFactory::getApplication();
            $user =& JFactory::getUser();
            if (!$user->guest) {
                MageBridgeModelUserSSO::checkSSOLogin();
                $application->close();
            }
        }
    }

    /**
     * Handle task queues
     * 
     * @access private
     * @param null
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
            if (JRequest::getCmd('option') == 'com_comprofiler' 
                && JRequest::getCmd('task') == 'saveUserEdit'
                && JFactory::getUser()->id == JRequest::getInt('id', 0, 'post')) {

                $tasks[] = 'cnsync';
            }
        }
            
        // Add a JomSocial profile-sync 
        if ($this->getParam('spoof_jomsocial_events')) {
            if (JRequest::getCmd('option') == 'com_community' 
                && JRequest::getCmd('view') == 'profile' 
                && in_array(JRequest::getCmd('task'), array('edit', 'editDetails')) 
                && JRequest::getCmd('action', null, 'post') == 'save') {

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
     * @param null
     * @return null
     */
    private function redirectSSL()
    {
        // Get system variables
        $application = JFactory::getApplication();
        $uri = JFactory::getURI();
        $enforce_ssl = $this->loadConfig('enforce_ssl');
        $from_http_to_https = $this->getParam('enable_ssl_redirect', 1);
        $from_https_to_http = $this->getParam('enable_nonssl_redirect', 1);
        $post = JRequest::get('post');

        // Match situation where we don't want to redirect
        if (!empty($post)) {
            return false;
        } else if (in_array(JRequest::getCmd('view'), array('ajax', 'jsonrpc'))) {
            return false;
        } else if (in_array(JRequest::getCmd('task'), array('ajax', 'json'))) {
            return false;
        } else if (in_array(JRequest::getCmd('controller'), array('ajax', 'jsonrpc'))) {
            return false;
        }

        // Check if SSL should be forced
        if ($uri->isSSL() == false && $this->getParam('enable_ssl_redirect', 1) == 1) {

            // Determine whether to do a redirect
            $redirect = false;

            // Do not redirect if SSL is disabled
            if ($enforce_ssl == 0) {
                $redirect = false;

            // Set the redirect for the entire Joomla! site
            } else if ($enforce_ssl == 1) {
                $redirect = true;

            // Set the redirect for MageBridge only
            } else if ($enforce_ssl == 2 && JRequest::getCmd('option') == 'com_magebridge') {

                // Prevent redirection when doing Single Sign On
                if (JRequest::getCmd('task') != 'login') {
                    $redirect = true;
                }

            // Set the redirect for specific MageBridge pages which should be served through SSL
            } else if ($enforce_ssl == 3 && JRequest::getCmd('option') == 'com_magebridge') {
                $redirect = (MageBridgeUrlHelper::isSSLPage()) ? true : false;
            }

            // Redirect to SSL
            if ($redirect == true) {
                $uri->setScheme('https');
                $application->redirect($uri->toString());
                $application->close();
            }

        // Check if non-SSL should be forced
        } else if ($uri->isSSL() == true && $this->getParam('enable_nonssl_redirect', 1) == 1) {

            // Determine whether to do a redirect
            $redirect = false;
            $components = array('com_magebridge', 'com_scriptmerge');

            // Set the redirect if SSL is disabled
            if ($enforce_ssl == 0) {
                $redirect = true;

            // Do not redirect if SSL is set for the entire site
            } else if ($enforce_ssl == 1) {
                $redirect = false; 

            // Do redirect if SSL is set for the shop only
            } else if ($enforce_ssl == 2 && !in_array(JRequest::getCmd('option'), $components)) {
                $redirect = true;

            // Set the redirect if SSL is only enabled for MageBridge
            } else if ($enforce_ssl == 3 && JRequest::getCmd('option') == 'com_magebridge') {
                $redirect = (MageBridgeUrlHelper::isSSLPage()) ? false : true;
            }

            if ($redirect == true) {
                $uri->setScheme('http');
                $application->redirect($uri->toString());
                $application->close();
            }
        }
    }

    /**
     * Spoof the Magento login-form
     *
     * @access private
     * @param null
     * @return bool
     */
    private function spoofMagentoLoginForm()
    {
        // Fetch important variables
        $application = JFactory::getApplication();
        $login = JRequest::getVar('login', array(), 'post', 'array');
        $option = JRequest::getCmd('option');

        // Detect a Magento login-POST
        if ($option == 'com_magebridge' && !empty($login['username']) && !empty($login['password'])) {

            // Convert the Magento POST into Joomla! POST-credentials
            $credentials = array(
                'username' => $login['username'],
                'password' => $login['password'],
            );

            // Try to login into the Joomla! application
            $rt = $application->login($credentials);

            // If the login is succesfull, we do not submit build the bridge any further, but redirect right away
            if ($rt == true) {
                $url = MageBridgeUrlHelper::route('customer/account');
                $application->redirect($url);
                $application->close();
                return true;
            }
        }

        return false;
    }

    /**
     * Load a specific parameter
     *
     * @access private
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getParam($name, $default = null)
    {
        return $this->getParams()->get($name, $default);
    }

    /**
     * Load the parameters
     *
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        if (!MageBridgeHelper::isJoomla15()) {
            return $this->params;
        } else {
            jimport('joomla.html.parameter');
            $plugin = JPluginHelper::getPlugin('system', 'magebridge');
            $params = new JParameter($plugin->params);
            return $params;
        }
    }

    /**
     * Redirect a specific URL
     *
     * @access private
     * @param string $name
     * @param string $value
     * @param string $redirect
     * @return null
     */
    private function doRedirect($name = '', $value = '', $redirect = null)
    {
        if (JRequest::getCmd($name) == $value) {
            $return = base64_decode(JRequest::getString('return'));
            if(!empty($return)) {
                $return = MageBridgeEncryptionHelper::base64_encode($return);
                $redirect .= '/referer/'.$return.'/';
            }
            header('Location: '.MageBridgeUrlHelper::route($redirect));
            exit;
        }
    }

    /**
     * Load a configuration value
     *
     * @access private
     * @param string $name
     * @return null
     */
    private function loadConfig($name)
    {
        return MagebridgeModelConfig::load($name);
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
        // Import the MageBridge autoloader
        include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

        // Check if the MageBridge class exists
        if (class_exists('MageBridgeModelBridge')) {
            return true;
        }
        return false;
    }
}

