<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Define the connection constants
define('MAGEBRIDGE_PROXY_FALSE', 0);
define('MAGEBRIDGE_PROXY_SUCCESS', 1);
define('MAGEBRIDGE_PROXY_ERROR', -1);

/*
 * Bridge proxy class
 */
class MageBridgeModelProxy
{
    /*
     * Raw headers received from the proxy
     */
    public $rawheaders = array();

    /*
     * Counter for how many times we connect to Magento
     */
    private $_count = 2;

    /*
     * Headers received from the proxy
     */
    private $_head = array();

    /*
     * Content fetched through the proxy
     */
    private $_data = '';

    /*
     * Initialization flag
     */
    private $_init = MAGEBRIDGE_PROXY_FALSE;

    /*
     * State of connection
     */
    private $_state = '';

    /*
     * Redirect flag
     */
    private $_redirect = false;

    /*
     * Allow redirects flag
     */
    private $_allow_redirects = true;

    /*
     * Method to fetch the data
     *
     */
    public static function getInstance()
    {
        static $instance;

        if ($instance === null) {
            $instance = new MageBridgeModelProxy();
        }

        return $instance;
    }

    /*
     * Encode data for transmission
     */
    public function encode($data)
    {
        $rt = json_encode($data);
        if ($rt == false) {
            $data = utf8_encode($data);
            $rt = json_encode($data);          
            if ($rt == false) {
                if (function_exists('json_last_error')) {
                    $json_error = json_last_error();
                    if ($json_error == JSON_ERROR_UTF8) $json_error = "Malformed UTF-8";
                    if ($json_error == JSON_ERROR_SYNTAX) $json_error = "Syntax error";
                } else {
                    $json_error = 'unknown';
                }
                MageBridgeModelDebug::getInstance()->error('PHP Error: json_encode failed with error "'.$json_error.'"');
                MageBridgeModelDebug::getInstance()->trace('Data before json_encode', $data);
            }
        }
        return $rt;
    }

    /*
     * Decode data after transmission
     */
    public function decode($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if ($decoded == false || $decoded == 1 || $decoded == $data) {
                return false;
            } else {
                return $decoded;
            }
        } else {
            return $data;
        }
    }

    /*
     * Build the data from the registry
     */
    public function build($data)
    {
        if ($this->_init != MAGEBRIDGE_PROXY_ERROR) {

            $application = JFactory::getApplication();
            $bridge = MageBridgeModelBridge::getInstance();

            // If the request-data is empty, there's no point in making a call
            if (empty($data)) {
                return null;
            }

            // Loop through the registry and encode it for transferral
            if (!empty($data)) {
                foreach ($data as $index => $segment) {
                    if (empty($segment['data'])) {
                        $data[$index] = $this->encode($segment);
                    }
                }
            }

            // Fetch the data by using POST
            $raw = $this->getRemote($bridge->getMagentoBridgeUrl(), $data, MagebridgeModelConfig::load('method'), true);

            // Decode the reply
            $decoded = $this->decode($raw);

            // Increase the counter to make sure endless redirects don't happen
            $this->_count++;

            //MageBridgeModelDebug::getInstance()->trace( 'Proxy raw response', $raw );
            //MageBridgeModelDebug::getInstance()->trace( 'Proxy decoded response', $decoded );

            // Determine whether this is non-bridge output
            $nonbridge = false;
            if ($this->isValidResponse($decoded) == false) {
                MageBridgeModelDebug::getInstance()->notice( 'Empty decoded response suggests non-bridge output' );
                $nonbridge = true;
            }

            // Check whether the Content-Type is indicating non-bridge output
            if (!empty($this->_head['headers']) && preg_match('/Content-Type: (application|text)\/(xml|javascript|octetstream)/', $this->_head['headers'])) {
                MageBridgeModelDebug::getInstance()->trace( 'Detecting non-bridge output in HTTP headers', $this->_head['headers'] );
                $nonbridge = true;
            }

            // Check whether the current URL is listed for direct output
            $direct_output_urls = MageBridgeHelper::csvToArray(MagebridgeModelConfig::load('direct_output'));
            if (!empty($direct_output_urls)) {
                foreach ($direct_output_urls as $direct_output_url) {
                    $current_url = MageBridgeUrlHelper::getRequest();
                    if (!empty($direct_output_url) && strstr($current_url, $direct_output_url)) {
                        MageBridgeModelDebug::getInstance()->trace( 'Detecting non-bridge output through MageBridge configuration', $direct_output_url );
                        print $raw;
                        return $application->close();
                    }
                }
            }

            // Handle non-bridge output
            if ($nonbridge == true) {

                // Redirect if needed
                $this->redirect();

                if (empty($this->_head['http_code'])) $this->_head['http_code'] = 200;

                if ($this->_head['http_code'] == 200 && !empty($raw)) {

                    // Spoof the current HTTP-headers
                    $this->spoofHeaders($raw);

                    // Detect JSON and replace any URL-redirects
                    if (is_array($decoded) && isset($decoded['redirect'])) {
                        $url = $decoded['redirect'];
                        if (preg_match('/^index\.php\?option\=com/', $url)) {
                            $newurl = MageBridgeHelper::filterUrl($url);
                            if (!empty($newurl)) $decoded['redirect'] = $newurl;
                            $data = $this->encode($decoded);
                        }
                    }

                    // Detect HTML and parse it anyway
                    if (preg_match('/<\/html>$/', $raw)) {
                        $raw = MageBridgeHelper::filterContent($raw);
                    }

                    // Output the raw content
                    print $raw;

                    MageBridgeModelDebug::getInstance()->warning( "Non-bridge output from Magento" );
                    //MageBridgeModelDebug::getInstance()->trace( "Output", $raw );

                    $application->close();

                } else {
                    return null;
                }
            } else {
                $data = $decoded;
            }

            // Detect events and run them
            if (!empty($data['events']['data'])) {
                $bridge->setEvents($data['events']['data']);
            }

            // Redirect if needed
            $this->redirect();

            $this->_data = $data;
            $this->_init = MAGEBRIDGE_PROXY_SUCCESS;

        }
        return $this->_data;
    }

    /*
     * Method to determine whether the bridge-response is valid or not
     */
    public function isValidResponse($data = null)
    {
        // Detect non-bridge AJAX-calls
        if (empty($data['meta'])) {
            if (JFactory::getApplication()->isSite()) {
                return false;
            } else if (JRequest::getCmd('option') == 'com_magebridge' && JRequest::getCmd('view') == 'root') {
                return false; 
            }
        }

        return true;
    }

    /*
     * Method to fetch data from a remote URL
     */
    public function getRemote($url = '', $arguments = array(), $type = null, $run_bridge = false)
    {
        // Do not continue if the URL is empty
        if (empty($url)) {
            return null;
        }

        // Take over the _POST data
        if ($type == null) {
            if (!empty($_POST)) {
                $type = 'post';
            } else {
                $type = 'get';
            }
        }

        // Ignore an empty POST, because this wouldn't matter anyway
        if ($type == 'post' && empty($arguments)) {
            $type = 'get';
        }

        // Convert the arguments into an URL-string
        if ($type == 'get' && !empty($arguments)) {
            $url .= '?'.http_build_query($arguments);
        } 

        return $this->getCURL($url, $type, $arguments, $run_bridge);
    }

    /*
     * CURL-wrapper
     */
    public function getCURL($url, $type = 'get', $arguments = null, $run_bridge = false)
    {
        // Load variables
        $bridge = MageBridgeModelBridge::getInstance();
        $application = JFactory::getApplication();
        $helper = new MageBridgeProxyHelper();
        $http_headers = array();

        // Initialize CURL
        $handle = curl_init($url);
        curl_setopt_array( $handle, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => MagebridgeModelConfig::load('curl_timeout'),
            CURLOPT_TIMEOUT => MagebridgeModelConfig::load('curl_timeout'),
            CURLOPT_DNS_CACHE_TIMEOUT => MagebridgeModelConfig::load('curl_timeout'),
            CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_BUFFERSIZE => 8192,
        ));

        // CURL HTTP-authentication
        $http_user = MagebridgeModelConfig::load('http_user');
        $http_password = MagebridgeModelConfig::load('http_password');
        if (MagebridgeModelConfig::load('http_auth') == 1) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, MagebridgeModelConfig::load('http_authtype'));
            curl_setopt($handle, CURLOPT_USERPWD, $http_user.':'.$http_password );
        }

        // Set COOKIE-settings
        if ($run_bridge == true) {
            $cookie_expires = date('r', $application->getCfg('lifetime',60)*60);
            if ($application->isSite() == 1) {
                curl_setopt($handle, CURLOPT_COOKIE, 'frontend='.$bridge->getMageSession().'; Expires='.$cookie_expires);
            } else {
                curl_setopt($handle, CURLOPT_COOKIE, 'admin='.$bridge->getMageSession().'; Expires='.$cookie_expires);
            }
        }

        // Detect whether certain HTTP headers are set by the client
        foreach ($_SERVER as $header => $value) {
            if (!preg_match('/^http_/i', $header)) continue;
            $header = strtoupper(preg_replace('/http_/i', '', $header));
            if ($header == 'X_REQUESTED_WITH') {
                $http_headers[] = $header.': '.$value;
            } else if (preg_match('/^ACCEPT_/', $header)) {
                $http_headers[] = $header.': '.$value;
            }
        }

        // Add proxy HTTP headers
        if ($_SERVER['REMOTE_ADDR']) {
            $http_headers[] = 'HTTP_X_REAL_IP: '.$_SERVER['REMOTE_ADDR'];
            $http_headers[] = 'HTTP_X_FORWARDED_FOR: '.$_SERVER['REMOTE_ADDR'];
        }

        // Add some extra HTTP headers for HTTP Keep Alive
        if (MagebridgeModelConfig::load('keep_alive') == 0) {
            $http_headers[] = 'Connection: close';
        } else {
            $http_headers[] = 'Connection: keep-alive';
        }

        // Spoof the browser
        if (MagebridgeModelConfig::load('spoof_browser') == 1) {
            if ($run_bridge == true && $application->isSite() == 1) {
                curl_setopt($handle, CURLOPT_REFERER, MageBridgeUrlHelper::getRequest());
                curl_setopt($handle, CURLOPT_USERAGENT, ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ''));
            } else {
                curl_setopt($handle, CURLOPT_USERAGENT, $this->getUserAgentBySystem());
            }
        }

        // Automatically handle file uploads
        $tmp_files = MageBridgeProxyHelper::upload();
        if (!empty($tmp_files)) {
            foreach ($tmp_files as $name => $tmp_file) {
                $arguments[$name] = '@'.$tmp_file;
            }
        }
        
        // Set extra options when a POST is handled
        if ($type == 'post') {
            $arguments = (is_array($arguments) && MagebridgeModelConfig::load('curl_post_as_array') == 0) ? http_build_query($arguments) : $arguments ;
            curl_setopt( $handle, CURLOPT_POST, true );
            curl_setopt( $handle, CURLOPT_POSTFIELDS, $arguments);       
            $http_headers[] = 'Expect:';
        }

        // Add the HTTP headers
        curl_setopt( $handle, CURLOPT_HTTPHEADER, $http_headers);

        // Handle direct output and bridge output
        MageBridgeModelDebug::getInstance()->notice('CURL init: '.$url.' ('.((MageBridgeUrlHelper::getRequest()) ? MageBridgeUrlHelper::getRequest() : 'no request').')');
        $this->handleFileDownloads($handle);
        $data = curl_exec($handle);
        $size = (function_exists('mb_strlen')) ? mb_strlen($data) : strlen($data);
        if ($size > 1024) $size = round($size/1024, 2).'Kb';
        MageBridgeModelDebug::getInstance()->profiler('CURL response size: '.$size);

        // Cleanup the temporary uploads
        MageBridgeProxyHelper::cleanup($tmp_files);

        // Seperate the headers from the body
        $this->_head['last_url'] = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
        $this->_head['http_code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        $this->_head['size'] = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $this->_head['headers'] = substr($data, 0, $this->_head['size'] - 4);
        $this->_body = substr($data, $this->_head['size']);

        MageBridgeModelDebug::getInstance()->profiler('CURL total time: '.round(curl_getinfo($handle, CURLINFO_TOTAL_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL connect time: '.round(curl_getinfo($handle, CURLINFO_CONNECT_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL DNS-time: '.round(curl_getinfo($handle, CURLINFO_NAMELOOKUP_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL download speed: '.round(curl_getinfo($handle, CURLINFO_SPEED_DOWNLOAD * 8 / 1024), 4).' Kb/s');
        //MageBridgeModelDebug::getInstance()->trace( "CURL information", curl_getinfo($handle));
        //MageBridgeModelDebug::getInstance()->trace( "HTTP headers", $this->_head );
        //MageBridgeModelDebug::getInstance()->trace( "HTTP body", $this->_body );

        // Handle MageBridge HTTP headers
        preg_match_all('/X-Mage(b|B)ridge-([a-sA-Z0-9]+): ([^\s]+)/', $this->_head['headers'], $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $index => $match) {
                $type = $matches[2][$index]; 
                $message = $matches[3][$index]; 
                if (!empty($type) && !empty($message)) {
                    $message = base64_decode($message);
                    $application->enqueueMessage($message, $type);
                }
            }
        }

        // Log other Status Codes than 200
        if ($this->_head['http_code'] != 200) {
            if ($this->_head['http_code'] == 500) {
                MageBridgeModelDebug::getInstance()->error('CURL received HTTP status '.$this->_head['http_code']);
            } else {
                MageBridgeModelDebug::getInstance()->warning('CURL received HTTP status '.$this->_head['http_code']);
            }
        }

        // If we receive status 0, log it
        if ($this->_head['http_code'] == 0) {
            $this->_head['http_error'] = curl_error($handle);
            MageBridgeModelDebug::getInstance()->trace( 'CURL error', curl_error($handle));
        }

        // If we receive an exception, exit the bridge
        if ($this->_head['http_code'] == 0 || $this->_head['http_code'] == 500) {
            $this->_init = MAGEBRIDGE_PROXY_ERROR;
            $this->_state = 'INTERNAL ERROR';

            curl_close($handle);
            return $this->_body; 
        }

        // If we receive a 404, log it 
        if ($this->_head['http_code'] == 404) {

            $this->_init = MAGEBRIDGE_PROXY_ERROR;
            $this->_state = '404 NOT FOUND';
            curl_close($handle);

            if ($application->isSite() == 1 && MagebridgeModelConfig::load('enable_notfound') == 1) {
                JError::raiseError(404, JText::_('Page Not Found'));
                return null;
            } else {
                return $this->_body;
            }
        }

        // If we have an empty body, log it
        if (empty($this->_body)) {
            MageBridgeModelDebug::getInstance()->warning( 'CURL received empty body' );
            if (!empty($this->_head['headers'])) {
                MageBridgeModelDebug::getInstance()->trace( 'CURL headers', $this->_head['headers'] );
            }
        }

        // Handle cookies
        $default_sessname = ini_get('session.name');
        if (empty($default_sessname)) $default_sessname = 'PHPSESSID';
        preg_match_all('/Set-Cookie: ('.$default_sessname.'|frontend|persistent_shopping_cart)\=([a-zA-Z0-9\_\-]{12,64})/', $this->_head['headers'], $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $index => $match) {

                $sessionId = $matches[2][$index];
                $sessionName = $matches[1][$index];

                if (!headers_sent()) setcookie($sessionName, $sessionId, 0);

                $session = JFactory::getSession();
                if ($sessionName == 'frontend') { 
                    $session->set('magento_session', $sessionId);
                } else if ($sessionName == $default_sessname) { 
                    $session->set('magento_session', $sessionId);
                    $session->set('magento_php_session', $sessionId);
                } else if ($sessionName == 'persistent_shopping_cart') { 
                    $session->set('magento_persistent_session', $sessionId);
                }
            }
        }

        // Handle redirects
        preg_match('/Location: ([^\s]+)/', $this->_head['headers'], $matches);
        if ($this->_allow_redirects && ($this->_head['http_code'] == 301 || $this->_head['http_code'] == 302 || !empty($matches))) {

            $location = trim(array_pop($matches));
            if ($matches == null) {
                MageBridgeModelDebug::getInstance()->trace('Redirect requested but no URL found', $this->head['headers']);
                return false;
            }

            if (empty($location)) {
                $location = $this->_head['last_url'];
            }

            //$location = preg_replace('/magebridge\.php\//', '', $location);
            MageBridgeModelDebug::getInstance()->warning( 'Trying to redirect to new location '.$location);
            $this->setRedirect($location);
        }

        // Ugly workaround: In MB, the addresses are initialized on this page, but the address-items do not match?
        //if (MageBridgeUrlHelper::getRequest() == 'checkout/multishipping/addresses') {
        //    $this->setRedirect('checkout/multishipping/addresses');
        //}

        curl_close($handle);
        return $this->_body;
    }

    /*
     * Method to deliver direct output
     *
     * @param resource $handle
     * @return bool
     */
    private function handleFileDownloads($handle)
    {
        // Determine whether to deliver direct output or not
        $match = false;
        if (preg_match('/^downloadable\/download\/link\/id\/([a-zA-Z0-9]+)/', MageBridgeUrlHelper::getRequest(), $id)) {
            $match = true;
        }

        // Do not continue, if we have no match
        if ($match == false || empty($id[1])) return false;

        // Get system variables
        $application = JFactory::getApplication();

        // Construct the temporary cached files to use
        $tmp_body = JFactory::getApplication()->getCfg('tmp_path').'/'.$id[1];
        $tmp_header = JFactory::getApplication()->getCfg('tmp_path').'/'.$id[1].'_header';

        // Check whether the cached files exist, otherwise create them
        if (!file_exists($tmp_body) || !file_exists($tmp_header) || filesize($tmp_body) == 0 || filesize($tmp_header) == 0) {

            // Open the file handles
            $tmp_body_handle = fopen($tmp_body, 'w');
            $tmp_header_handle = fopen($tmp_header, 'w');

            // Make the CURL call
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($handle, CURLOPT_FILE, $tmp_body_handle);
            curl_setopt($handle, CURLOPT_WRITEHEADER, $tmp_header_handle);
            curl_setopt($handle, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_exec($handle);

            // Close the file handles
            fclose($tmp_body_handle);
            fclose($tmp_header_handle);
        }

        // Recheck whether the header is empty, and if so, fetch the body and header independantly
        if (!file_exists($tmp_header) || filesize($tmp_header) == 0) {

            // Fetch the body
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_NOBODY, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($handle, CURLOPT_HEADERFUNCTION, array($this, 'setRawHeader'));
            curl_setopt($handle, CURLOPT_HTTPHEADER, array('Expect:'));
            $data = curl_exec($handle);
            file_put_contents($tmp_body, $data);
        }

        // Close the handle
        curl_close($handle);

        // Construct the new HTTP header
        if (!empty($this->rawheaders)) {
            $headers = $this->rawheaders;
        } else if (is_readable($tmp_header)) {
            $headers = file_get_contents($tmp_header);
        } else {
            $headers = null;
        }

        // Handle redirects
        $matches = null;
        @preg_match('/Location: ([^\s]+)/', $headers, $matches);
        $location = trim(array_pop($matches));
        if (!empty($location)) {
            @unlink($tmp_body);
            @unlink($tmp_header);
            $this->setRedirect($location);
            return;
        }

        // Parse the headers into an usable array
        if (is_string($headers)) $headers = explode( "\r\n", $headers);
        if (!is_array($headers) || empty($headers)) {
            $headers = explode( "\n", $headers );
        }

        // Proxy the headers
        if (is_array($headers) && count($headers) > 1) {
            foreach ($headers as $header) {
                $header = trim($header);
                if (preg_match('/^HTTP/', $header)) header($header);
                if (preg_match('/^Content/', $header)) header($header);
                if (preg_match('/^Cache/', $header)) header($header);
                if (preg_match('/^Pragma/', $header)) header($header);
                if (preg_match('/^Expires/', $header)) header($header);
                if (preg_match('/^ETag/', $header)) header($header);
                if (preg_match('/^Last-Modified/', $header)) header($header);
            }
        } else {
            header('Expires: 0');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($tmp_body));
            header('Content-Transfer-Encoding: binary');
        }

        header('Content-Length: '.filesize($tmp_body), true);
        ob_end_flush();
        flush();

        // Output the body
        readfile($tmp_body);

        // Clean up the files
        @unlink($tmp_body);
        @unlink($tmp_header);
        exit;
    }

    public function setCurlBody($handle = null, $data = null)
    {
        print $data;
        return strlen($data);
    }

    public function setCurlHeaders($handle = null, $data = null)
    {
        header(rtrim($data));
        return strlen($data);
    }

    public function setRawHeader($handle, $header)
    {
        $this->rawheaders[] = $header;
        return strlen($header);
    }

    /*
     * Method to spoof the current HTTP headers
     *
     * @param mixed $data
     * @return bool
     */
    private function spoofHeaders($data = null)
    {
        // Determine whether to allow spoofing or not
        $spoof = false;
        if (MagebridgeModelConfig::load('spoof_headers') == 1) {
            $spoof = true;
        } else if (preg_match('/^downloadable\/download\/link\/id/', MageBridgeUrlHelper::getRequest())) {
            $spoof = true;
        } else if (!empty($data) && preg_match('/\%PDF/', $data)) {
            $spoof = true;
        }

        // Set the original HTTP headers
        if ($spoof == true) {
            if (!empty($this->_head['headers'])) {
                $headers = explode( "\r\n", $this->_head['headers'] );
                if (!count($headers) > 1) {
                    $headers = explode( "\n", $this->_head['headers'] );
                }

                if (count($headers) > 1) {
                    foreach ($headers as $header) {
                        $header = trim($header);
                        if (preg_match('/^HTTP/', $header)) header($header);
                        if (preg_match('/^Cache/', $header)) header($header);
                        if (preg_match('/^Expires/', $header)) header($header);
                        if (preg_match('/^Pragma/', $header)) header($header);
                        if (preg_match('/^Content/', $header)) header($header);
                        if (preg_match('/^ETag/', $header)) header($header);
                        if (preg_match('/^Last-Modified/', $header)) header($header);
                        //if (preg_match('/^Set-Cookie/', $header)) header($header);
                    }
                }
            }
        }
    }

    /*
     * Method to get the current HTTP-status
     *
     * @param null
     * @return int
     */
    public function getHttpStatus()
    {
        if (isset($this->_head['http_code'])) {
            return $this->_head['http_code'];
        }
        return 0;
    }

    /*
     * Method to get the current proxy error
     *
     * @param null
     * @return string
     */
    public function getProxyError()
    {
        if (isset($this->_head['http_error'])) {
            return $this->_head['http_error'];
        }
        return null;
    }

    /*
     * Method to set the $_allow_redirects flag
     *
     * @param bool @bool
     * @return null
     */
    public function setAllowRedirects($bool = true)
    {
        $this->_allow_redirects = (bool)$bool;
    }

    /*
     * Method to set a redirect for later redirection
     *
     * @param string $redirect
     * @param int $max_redirects
     * @return bool
     */
    public function setRedirect($redirect = null, $max_redirects = 1)
    {
        // Do not redirect if the maximum redirect-count is reached
        if ($this->isMaxRedirect($redirect, $max_redirects) == true) {
            MageBridgeModelDebug::getInstance()->warning('Maximum redirects of '.$max_redirects.' reached');
            return false;
        }

        // Strip the base-path from the URL
        $menuitem = MageBridgeUrlHelper::getRootItem();
        if (empty($menuitem)) {
            $menuitem = MageBridgeUrlHelper::getCurrentItem();
        }
        if (!empty($menuitem)) {
            $root_path = str_replace('/', '\/', $menuitem->route);
            $redirect = preg_replace('/^\//', '', $redirect);
            $redirect = preg_replace('/^'.$root_path.'/', '', $redirect);
        } 

        // When the URL doesnt start with HTTP or HTTPS, assume it is still the original Magento request
        if (!preg_match('/^(http|https):\/\//', $redirect)) {
            $redirect = JURI::base().'index.php?option=com_magebridge&view=root&request='.$redirect;
        }

        // Replace the System URL for the frontend
        $application = JFactory::getApplication();
        if ($application->isSite() && preg_match('/index.php\?(.*)$/', $redirect, $match)) {
            $redirect = str_replace( $match[0], preg_replace( '/^\//', '', MageBridgeHelper::filterUrl($match[0])), $redirect);
        }

        MageBridgeModelDebug::getInstance()->warning( 'Redirect set to '.$redirect );
        $this->_redirect = $redirect;
        return true;
    }

    /*
     * Method to maximize the number of redirects (to prevent endless loops)
     *
     * @param string $redirect
     * @param int $max_redirects
     * @return bool
     */
    public function isMaxRedirect($redirect = null, $max_redirects = 1)
    {
        // Initialize redirection statistics
        if (!isset($_SESSION['mb_redirects'])) {
            $_SESSION['mb_redirects'] = array();
        }

        // Collect all redirection statistics
        if (array_key_exists($redirect, $_SESSION['mb_redirects'])) {
            if ($_SESSION['mb_redirects'][$redirect] == 0) {
                unset($_SESSION['mb_redirects'][$redirect]);
                return true;
            } else {
                $_SESSION['mb_redirects'][$redirect] = (int)$_SESSION['mb_redirects'][$redirect] - 1;
            }
        } else {
            $_SESSION['mb_redirects'][$redirect] = $max_redirects;
        }

        return false;
    }


    /*
     * Method to actually redirect the browser
     *
     * @param null
     * @return null
     */
    public function redirect()
    {
        // Redirect to the new location
        if (!empty($this->_redirect)) {
            MageBridgeModelDebug::getInstance()->warning( 'Proxy redirect to '.$this->_redirect);
            header('Location: '.$this->_redirect);
            exit;

        // We don't redirect so we don't need endless-loop protection anymore
        } else {
            $_SESSION['mb_redirects'] = array();
        }
    }

    /*
     * Method to get a User-Agent string for MageBridge
     *
     * @param null
     * @return string
     */
    public function getUserAgentBySystem()
    {
        $user_agent = 'MageBridge '.MageBridgeUpdateHelper::getComponentVersion();
        $user_agent .= ' (Joomla! '.MageBridgeHelper::getJoomlaVersion().')';
        return $user_agent;
    }

    /*
     * Method to reset the proxy
     *
     * @param null
     * @return mixed
     */
    public function reset()
    {
        $this->_init = MAGEBRIDGE_PROXY_FALSE;
        $this->_state = null;
    }

    /*
     * Method to get the current proxy state
     *
     * @param null
     * @return mixed
     */
    public function getState()
    {
        return $this->_state;
    }

    /*
     * Method to get the current redirect count
     *
     * @param null
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /*
     * Method to get the proxy data
     *
     * @param null
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /*
     * Method to get the proxy headers
     *
     * @param null
     * @return array
     */
    public function getHead()
    {
        return $this->_head;
    }

    /*
     * Method to get a cookie file (deprecated)
     *
     * @param null
     * @return null
     */
    public function getCookieFile()
    {
        $app = JFactory::getApplication();
        return $app->getCfg('log_path').'/magento.tmp';
    }
}

