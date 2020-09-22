<?php
/**
 * Magento Bridge
 *
 * @author Yireo
 * @package Magento Bridge
 * @copyright Copyright 2017
 * @license Open Source License
 * @link https://www.yireo.com
 */

/**
 * MageBridge-class that acts like proxy between bridge-classes and the API
 */
class MageBridge
{
    /**
     * The current request
     */
    private $request = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        // Decode all the POST-values with JSON
        if (!empty($_POST)) {
            foreach ($_POST as $index => $post) {
                $this->request[$index] = $this->getJson($post);
            }
        }

        // Decode extra string values with Base64
        if (!empty($this->request['meta']['arguments']) && is_array($this->request['meta']['arguments'])) {
            foreach ($this->request['meta']['arguments'] as $name => $value) {
                if (is_string($value)) {
                    $this->request['meta']['arguments'][$name] = base64_decode(strtr($value, '-_,', '+/='));
                }
            }
        }
    }

    /**
     * Decode a JSON-message
     *
     * @param string $string
     * @return array
     */
    public function getJson($string)
    {
        if (empty($string)) {
            return $string;
        }

        if (get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }

        $data = json_decode($string, true);
        if ($data == null) {
            $data = json_decode(stripslashes($string), true);
        }

        return $data;
    }

    /**
     * Get the request-data
     *
     * @access public
     * @param null
     * @return array
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get a segment from the request-data
     *
     * @param string $name
     * @return array
     */
    public function getSegment($name = '')
    {
        if (empty($name)) {
            return [];
        }

        if (empty($this->request)) {
            return [];
        }

        if (!isset($this->request[$name])) {
            return [];
        }

        return $this->request[$name];
    }

    /**
     * Helper-function to get the meta-data from the request
     *
     * @param string $name
     * @return array
     */
    public function getMeta($name = null)
    {
        if (empty($this->request['meta']['arguments'])) {
            return [];
        }

        if (!empty($name) && isset($this->request['meta']['arguments'][$name])) {
            return $this->request['meta']['arguments'][$name];
        }

        return $this->request['meta']['arguments'];
    }

    /**
     * Mask this request by using the data sent along with this request
     *
     * @return bool
     */
    public function premask()
    {
        // Fetch the meta-data from the bridge-request
        $data = $this->getMeta();
        if (empty($data)) {
            return false;
        }

        // Mask the POST
        if (!empty($data['post'])) {
            $_POST = array();
            foreach ($data['post'] as $name => $value) {
                if ($name == 'Itemid') continue;
                if ($name == 'option') continue;
                $_POST[$name] = $value;
            }
        } elseif (!isset($_POST['mbtest'])) {
            $_POST = array();
        }

        // Mask the REQUEST_URI and the GET
        if (!empty($data['request_uri']) && strlen($data['request_uri']) > 0) {

            // Determine the REQUEST_URI
            $request_uri = explode('?', $data['request_uri']);
            $request_uri = $request_uri[0];
            $request_uri = '/' . preg_replace('/^\//', '', $request_uri);

            // Add backslash to some URLs
            if (preg_match('/^\/checkout\/onepage/', $request_uri)) {
                $request_uri = preg_replace('/\/$/', '', $request_uri) . '/';
            }

            // Very ugly dirty copy of the core-hack of vendorms
            if (stripos($request_uri, 'vendorms')) {
                $vms = $request_uri;
                $is_name_append = str_replace('/', '', substr($vms, stripos($vms, 'vendorms') + 8, strlen($vms)));
                if (isset($is_name_append) && strlen($is_name_append) > 0) {
                    $fp = substr($vms, 0, strrpos($vms, '/') + 1);
                    $lp = substr($vms, strrpos($vms, '/') + 1, strlen($vms));
                    $request_uri = $fp . 'all/index/name/' . $lp;
                }
            }

            // Set the GET variables
            $data['request_uri'] = preg_replace('/^\//', '', $data['request_uri']);
            $query = preg_replace('/^([^\?]*)\?/', '', $data['request_uri']);
            if ($query != $data['request_uri']) {
                parse_str(rawurldecode($query), $parts);
                foreach ($parts as $name => $value) {
                    if ($name == 'Itemid') continue;
                    if ($name == 'option') continue;
                    $_GET[$name] = $value;
                }
            }

            // Add the GET variables to the REQUEST_URI
            if (!empty($_GET)) {
                $request_uri .= '?' . http_build_query($_GET);
            }

            // Set the REQUEST_URI
            $_SERVER['REQUEST_URI'] = $request_uri;
            $_SERVER['HTTP_X_REWRITE_URL'] = $request_uri;
            $_SERVER['HTTP_X_ORIGINAL_URL'] = $request_uri;


            // Set defaults otherwise
        } else {
            $_SERVER['REQUEST_URI'] = '/';
            $_SERVER['HTTP_X_REWRITE_URL'] = '/';
            $_SERVER['HTTP_X_ORIGINAL_URL'] = '/';
        }

        // Mask the HTTP_USER_AGENT
        if (!empty($data['user_agent'])) {
            $_SERVER['HTTP_USER_AGENT'] = $data['user_agent'];
        }

        // Mask the HTTP_REFERER
        if (!empty($data['http_referer'])) {
            if (isset($_SERVER['HTTP_REFERER'])) $_SERVER['ORIGINAL_HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
            $_SERVER['HTTP_REFERER'] = $data['http_referer'];
        }

        // Mask the REMOTE_ADDR
        if (!empty($data['remote_addr'])) {
            $_SERVER['REMOTE_ADDR'] = $data['remote_addr'];
        }

        // Mask the REQUEST_METHOD
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
        }

        // Make sure all globals are arrays
        if (empty($_GET)) $_GET = array();
        if (empty($_POST)) $_POST = array();
        if (empty($_COOKIE)) $_COOKIE = array();

        // Fix the request
        $_REQUEST = array_merge($_GET, $_POST);

        // Set the cookie lifetime
        if (!empty($data['joomla_conf_lifetime']) && $data['joomla_conf_lifetime'] > 60) {
            session_set_cookie_params($data['joomla_conf_lifetime']);
            ini_set('session.gc_maxlifetime', $data['joomla_conf_lifetime']);
        }

        // Initialize the Magento session by SID-parameter
        if (isset($_GET['SID']) && self::isValidSessionId($_GET['SID'])) {
            session_name('frontend');
            session_id($_GET['SID']);
            setcookie('frontend', $_GET['SID']);
            $_COOKIE['frontend'] = $_GET['SID'];

            // Initialize the Magento session by the session-ID tracked by MageBridge
        } elseif (!empty($data['magento_session']) && self::isValidSessionId($data['magento_session'])) {
            session_name('frontend');
            session_id($data['magento_session']);
            setcookie('frontend', $data['magento_session']);
            $_COOKIE['frontend'] = $data['magento_session'];

            // Initialize Single Sign On
        } elseif (!empty($_GET['sso']) && !empty($_GET['app'])) {
            if ($_GET['app'] == 'admin' && isset($_COOKIE['adminhtml']) && self::isValidSessionId($_COOKIE['adminhtml'])) {
                session_name('adminhtml');
                session_id($_COOKIE['adminhtml']);
            } elseif (isset($_COOKIE['frontend']) && self::isValidSessionId($_COOKIE['frontend'])) {
                session_name('frontend');
                session_id($_COOKIE['frontend']);
            }
        }

        // Initialize the Persistent Shopping Cart
        if (!empty($data['magento_persistent_session']) && self::isValidSessionId($data['magento_persistent_session'])) {
            setcookie('persistent_shopping_cart', $data['magento_persistent_session']);
            $_COOKIE['persistent_shopping_cart'] = $data['magento_persistent_session'];
        }

        // Initialize the allowed_save-cookie
        if (!empty($data['magento_user_allowed_save_cookie'])) {
            setcookie('user_allowed_save_cookie', $data['magento_user_allowed_save_cookie']);
            $_COOKIE['user_allowed_save_cookie'] = $data['magento_user_allowed_save_cookie'];
        }

        // Recheck cookies
        foreach ($_COOKIE as $cookieName => $cookieValue) {
            $cookieValue = trim($cookieValue);
            if (self::isValidSessionId($cookieValue) == false) {
                $_COOKIE[$cookieName] = null;
            }
        }

        // Check for a correct cookie
        if ((isset($_COOKIE['frontend']) && self::isValidSessionId($_COOKIE['frontend']) == false)) {
            $_COOKIE['frontend'] = null;
        }

        // Set the SID paramater
        $_GET['SID'] = session_id();

        return true;
    }

    /**
     * Run the bridge-core
     *
     * @param string $sessionId
     * @param string $sessionName
     * @return bool
     */
    public function isValidSessionId($sessionId, $sessionName = null)
    {
        $forbidden = ['deleted'];
        $allowedSessions = ['adminhtml', 'frontend', 'SID', 'magento_session'];

        $sessionId = trim($sessionId);

        if (in_array($sessionId, $forbidden)) {
            return false;
        }

        if (in_array($sessionName, $allowedSessions) && empty($sessionId)) {
            return false;
        }

        if (in_array($sessionName, $allowedSessions) && !preg_match('/^([a-zA-Z0-9\-\_\,]{10,100})$/', $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * Run the bridge-core
     */
    public function run()
    {
        Mage::getSingleton('magebridge/debug')->notice('Session: ' . session_id());
        Mage::getSingleton('magebridge/debug')->notice('Request: ' . $_SERVER['REQUEST_URI']);
        Mage::getSingleton('magebridge/debug')->trace('FILES', $_FILES);

        // Handle SSO
        if (Mage::getSingleton('magebridge/user')->doSSO() == true) {
            Mage::getSingleton('magebridge/debug')->notice('Handling SSO');
            exit;
        }

        // Now Magento is initialized, we can load the MageBridge core-class
        $bridge = Mage::getSingleton('magebridge/core');

        // Initialize the bridge
        $bridge->init($this->getMeta(), $this->getRequest());
        yireo_benchmark('MB_Core::init()');

        // Handle tests
        if (Mage::app()->getRequest()->getQuery('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'get');
            print $bridge->output(false);
            exit;

        } elseif (Mage::app()->getRequest()->getPost('mbtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'post');
            print $bridge->output(false);
            exit;
        }

        // Check for the meta-data
        if (!count($this->getMeta()) > 0) {
            $bridge->setMetaData('state', 'empty metadata');
            print $bridge->output(false);
            exit;
        }

        // Match the supportkey
        if ($this->getMeta('supportkey') != $bridge->getLicenseKey() && $this->getMeta('license') != $bridge->getLicenseKey()) {
            yireo_benchmark('MageBridge supportkey failed');
            $bridge->setMetaData('state', 'supportkey failed');
            $bridge->setMetaData('extra', $bridge->getLicenseKey());
            print $bridge->output(false);
            exit;
        }

        // Authorize this request using the API credentials (set in the meta-data)
        if ($this->authenticate() == false) {
            yireo_benchmark('MageBridge authentication failed');
            $bridge->setMetaData('state', 'authentication failed');
            print $bridge->output(false);
            exit;
        }

        // Handle authentication tests
        if (Mage::app()->getRequest()->getQuery('mbauthtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'get');
            print $bridge->output(false);
            exit;

        } elseif (Mage::app()->getRequest()->getPost('mbauthtest') == 1) {
            $bridge->setMetaData('state', 'test');
            $bridge->setMetaData('extra', 'post');
            print $bridge->output(false);
            exit;
        }

        // Check if there's any output already set (for instance JSON, AJAX, XML, PDF) and output it right away
        if ($bridge->preoutput() == true) {
            session_write_close();
            exit;
        }

        // Fetch the actual request
        $data = $bridge->getRequestData();
        if (is_array($data) && !empty($data)) {

            // Dispatch the request to the appropriate classes 
            Mage::getSingleton('magebridge/debug')->notice('Dispatching the request');
            $data = $this->dispatch($data);

            // Set the completed request as response
            $bridge->setResponseData($data);

        } else {
            Mage::getSingleton('magebridge/debug')->notice('Empty request');
        }

        Mage::getSingleton('magebridge/debug')->notice('Done with session: ' . session_id());
        //Mage::getSingleton('magebridge/debug')->trace('Response data', $data);
        //Mage::getSingleton('magebridge/debug')->trace('Session dump', $_SESSION);
        //Mage::getSingleton('magebridge/debug')->trace('Cookie dump', $_COOKIE);
        Mage::getSingleton('magebridge/debug')->trace('GET dump', $_GET);
        //Mage::getSingleton('magebridge/debug')->trace('POST dump', $_POST);
        Mage::getSingleton('magebridge/debug')->trace('PHP memory', round(memory_get_usage() / 1024));
        yireo_benchmark('MB_Core::output()');

        $bridge->setMetaData('state', null);

        $output = $bridge->output();

        header('Content-Length: ' . strlen($output));
        header('Content-Type: application/magebridge');

        echo $output;
        session_write_close();
        exit;
    }

    /**
     * Authorize access to the bridge
     *
     * @return bool
     */
    public function authenticate()
    {
        $bridge = Mage::getSingleton('magebridge/core');

        if ($this->isAllowed() === false) {
            $ip = gethostbyname($_SERVER['HTTP_VIA']);
            Mage::getSingleton('magebridge/debug')->error(sprintf("IP: %s not allowed to connect",$ip));
            return false;
        }

        // Authorize against the bridge-core
        if ($bridge->authenticate($bridge->getMetaData('api_user'), $bridge->getMetaData('api_key')) == false) {
            session_regenerate_id();
            Mage::getSingleton('magebridge/debug')->error('API authorization failed for user ' . $bridge->getMetaData('api_user') . ' / ' . $bridge->getMetaData('api_key'));
            return false;

        } else {
            Mage::getSingleton('magebridge/debug')->notice('API authorization succeeded');
        }

        return true;
    }

    /**
     * Determine whether this remote host is allowed to connect
     *
     * @return bool
     */
    protected function isAllowed()
    {
        /** @var Yireo_MageBridge_Model_Config_AllowedIps $allowedIps */
        $allowedIps = Mage::getModel('magebridge/config_allowedIps', Mage::app()->getStore());
        if (empty($allowedIps)) {
            return true;
        }

        if ($allowedIps->isHostAllowed($_SERVER['HTTP_VIA']) === true) {
            return true;
        }

        return false;
    }

    /**
     * Dispatch the bridge-request to the appropriate classes
     *
     * @param array $data
     * @return array $data
     */
    public function dispatch($data)
    {
        // Loop through the posted data, complete it and send it back
        $profiler = false;
        foreach ($data as $index => $segment) {

            switch ($segment['type']) {

                case 'version':
                    $segment['data'] = Mage::getSingleton('magebridge/update')->getCurrentVersion();
                    break;

                case 'authenticate':
                    $segment['data'] = Mage::getSingleton('magebridge/user')->authenticate($segment['arguments']);
                    break;

                case 'login':
                    $segment['data'] = Mage::getSingleton('magebridge/user')->login($segment['arguments']);
                    break;

                case 'logout':
                    $segment['data'] = Mage::getSingleton('magebridge/user')->logout($segment['arguments']);
                    break;

                case 'urls':
                    $segment['data'] = Mage::getSingleton('magebridge/url')->getData($segment['name']);
                    break;

                case 'block':

                    // Skip the profiler for now
                    if ($segment['name'] == 'core_profiler') {
                        $profilerId = $index;
                        $profiler = $segment;
                        break;
                    }

                    $segment['data'] = Mage::getSingleton('magebridge/block')->getOutput($segment['name'], $segment['arguments']);
                    $segment['meta'] = Mage::getSingleton('magebridge/block')->getMeta($segment['name']);
                    break;

                case 'widget':
                    $segment['data'] = Mage::getSingleton('magebridge/widget')->getOutput($segment['name'], $segment['arguments']);
                    $segment['meta'] = Mage::getSingleton('magebridge/block')->getMeta($segment['name']);
                    break;

                case 'filter':
                    $segment['data'] = Mage::getSingleton('magebridge/block')->filter($segment['arguments']);
                    break;

                case 'breadcrumbs':
                    $segment['data'] = Mage::getSingleton('magebridge/breadcrumbs')->getBreadcrumbs();
                    break;

                case 'api':
                    $segment['data'] = Mage::getSingleton('magebridge/api')->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'event':
                    $segment['data'] = Mage::getSingleton('magebridge/dispatcher')->getResult($segment['name'], $segment['arguments']);
                    break;

                case 'headers':
                    $segment['data'] = Mage::getSingleton('magebridge/headers')->getHeaders();
                    break;
            }

            $data[$index] = $segment;
        }

        // Parse the profiler
        if (is_array($profiler)) {
            $profiler['data'] = Mage::getSingleton('magebridge/block')->getOutput($profiler['name'], $profiler['arguments']);
            $profiler['meta'] = Mage::getSingleton('magebridge/block')->getMeta($profiler['name']);
            //echo Mage::helper('magebridge/encryption')->base64_decode($profiler['data']);exit;
            $data[$profilerId] = $profiler;
        }

        return $data;
    }
}

// End
