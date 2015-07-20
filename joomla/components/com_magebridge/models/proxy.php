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

            if(is_string($data)) {
                $data = utf8_encode($data);
            }

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
            $raw = trim($raw);

            // Decode the reply
            $decoded = $this->decode($raw);

            // Increase the counter to make sure endless redirects don't happen
            $this->_count++;

            //MageBridgeModelDebug::getInstance()->trace( 'Proxy headers', $this->_head );
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
                $current_url = MageBridgeUrlHelper::getRequest();
                foreach ($direct_output_urls as $direct_output_url) {
                    $direct_output_url = trim($direct_output_url);
                    if (!empty($direct_output_url) && strstr($current_url, $direct_output_url)) {
                        MageBridgeModelDebug::getInstance()->trace( 'Detecting non-bridge output through MageBridge configuration', $direct_output_url );
                        header('Content-Encoding: none');
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
                    header('Content-Encoding: none');
                    header('Content-Length: '.YireoHelper::strlen($raw));
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
        if ($handle == false) return false;
        curl_setopt_array($handle, array(
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

        // Set additional SSL options
        $ssl_version = MagebridgeModelConfig::load('ssl_version');
        if(!empty($ssl_version) && !is_numeric($ssl_version)) {
            $ssl_version = constant('CURL_SSLVERSION_'.$ssl_version);
        }
        $ssl_ciphers = MagebridgeModelConfig::load('ssl_ciphers');
        if(!empty($ssl_version)) curl_setopt($handle, CURLOPT_SSLVERSION, $ssl_version);
        if(!empty($ssl_ciphers)) curl_setopt($handle, CURLOPT_SSL_CIPHER_LIST, $ssl_ciphers);

        // CURL HTTP-authentication
        $http_user = MagebridgeModelConfig::load('http_user');
        $http_password = MagebridgeModelConfig::load('http_password');
        if (MagebridgeModelConfig::load('http_auth') == 1) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, MagebridgeModelConfig::load('http_authtype'));
            curl_setopt($handle, CURLOPT_USERPWD, $http_user.':'.$http_password );
        }

        // Forward cookies to Magento
        if ($run_bridge == true) {
            $cookies = MageBridgeBridgeHelper::getBridgableCookies();
            $curlCookies = array();
            foreach ($cookies as $cookie_name) {
                $cookie_value = JRequest::getString($cookie_name, null, 'cookie');
                if(empty($cookie_value)) $cookie_value = JFactory::getSession()->get('magebridge.cookie.'.$cookie_name);
                if(empty($cookie_value)) continue;
                $curlCookies[] = $cookie_name.'='.$cookie_value;
            }

            if(!empty($curlCookies)) {
                curl_setopt($handle, CURLOPT_COOKIE, implode(';', $curlCookies));
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
        if(isset($_SERVER['REMOTE_ADDR'])) $http_headers[] = 'HTTP_X_REAL_IP: '.$_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['REMOTE_ADDR'])) $http_headers[] = 'HTTP_X_FORWARDED_FOR: '.$_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['SERVER_ADDR'])) $http_headers[] = 'VIA: '.$_SERVER['SERVER_ADDR'];

        // Set SSL options
        $uri = JURI::getInstance();
        if($uri->isSSL() == true) $http_headers[] = 'HTTP_FRONT_END_HTTPS: On';
        if($uri->isSSL() == true) $http_headers[] = 'HTTP_X_FORWARD_PROTO: https';

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
        curl_setopt($handle, CURLOPT_HTTPHEADER, $http_headers);

        // Set encoding to zero
        curl_setopt($handle, CURLOPT_ENCODING, '');

        // Handle direct output and bridge output
        MageBridgeModelDebug::getInstance()->notice('CURL init: '.$url.' ('.((MageBridgeUrlHelper::getRequest()) ? MageBridgeUrlHelper::getRequest() : 'no request').')');
        $this->handleFileDownloads($handle);
        $data = curl_exec($handle);
        $size = YireoHelper::strlen($data);
        if ($size > 1024) $size = round($size/1024, 2).'Kb';
        MageBridgeModelDebug::getInstance()->profiler('CURL response size: '.$size);

        // Cleanup the temporary uploads
        MageBridgeProxyHelper::cleanup($tmp_files);

        // Seperate the headers from the body
        $this->_head['last_url'] = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
        $this->_head['http_code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
        $this->_head['size'] = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $this->_head['info'] = curl_getinfo($handle);

        // Determine the seperator
        $seperator = null;
        if (strpos($data, "\r\n\r\n") > 0) {
            $seperator = "\r\n\r\n";
        } elseif (strpos($data, "\n\n") > 0) {
            $seperator = "\n\n";
        }

        // Split data into segments
        if (strpos($data, $seperator) > 0) {
            $dataSegments = explode($seperator, $data);
            foreach($dataSegments as $dataSegmentIndex => $dataSegment) {

                // Check for a segment that seems to contain HTTP-headers
                if(preg_match('/(Set-Cookie|Content-Type|Transfer-Encoding):/', $dataSegment)) {

                    // Get this segment 
                    $this->_head['headers'] = trim($dataSegment);

                    // Use the remaining segments for the body
                    unset($dataSegments[$dataSegmentIndex]);
                    $this->_body = implode("\r\n", $dataSegments);
                    break;
                }

                // Only allow for a body after a header (and ignore double headers)
                unset($dataSegments[$dataSegmentIndex]);
            }
        }

        // Statistics
        MageBridgeModelDebug::getInstance()->profiler('CURL total time: '.round(curl_getinfo($handle, CURLINFO_TOTAL_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL connect time: '.round(curl_getinfo($handle, CURLINFO_CONNECT_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL DNS-time: '.round(curl_getinfo($handle, CURLINFO_NAMELOOKUP_TIME), 4).' seconds');
        MageBridgeModelDebug::getInstance()->profiler('CURL download speed: '.round(curl_getinfo($handle, CURLINFO_SPEED_DOWNLOAD * 8 / 1024), 4).' Kb/s');
        //MageBridgeModelDebug::getInstance()->trace( "CURL information", curl_getinfo($handle));
        //MageBridgeModelDebug::getInstance()->trace( "HTTP headers", $this->_head );
        //MageBridgeModelDebug::getInstance()->trace( "HTTP body", $this->_body );

        // Handle MageBridge HTTP-messaging
        if (preg_match_all('/X-MageBridge-(Notice|Error|Warning): ([^\s]+)/', $this->_head['headers'], $matches)) {
            foreach ($matches[0] as $index => $match) {
                $type = $matches[1][$index]; 
                $message = $matches[2][$index]; 
                if (!empty($type) && !empty($message)) {
                    $message = base64_decode($message);
                    $application->enqueueMessage($message, $type);
                }
            }
        }

        // Process the X-MageBridge-Customer header
        if ($this->getHeader('X-MageBridge-Customer') != null) {
            $value = $this->getHeader('X-MageBridge-Customer');
            MageBridgeModelBridge::getInstance()->addSessionData('customer/email', $value);
            MageBridgeModelUser::getInstance()->postlogin($value, null, true, true);
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
                header('HTTP/1.0 404 Not Found');
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

        // Define which cookies to spoof
        $cookies = MageBridgeBridgeHelper::getBridgableCookies();
        $default_sessname = ini_get('session.name');
        if (empty($default_sessname)) $default_sessname = 'PHPSESSID';
        $cookies[] = $default_sessname; // Add the default session for sake of badly written Magento extensions

        // Handle cookies
        if(MagebridgeModelConfig::load('bridge_cookie_all') == 1) {
            preg_match_all('/Set-Cookie: ([a-zA-Z0-9\-\_\.]+)\=(.*)/', $this->_head['headers'], $matches);
        } else {
            preg_match_all('/Set-Cookie: ('.implode('|',$cookies).')\=(.*)/', $this->_head['headers'], $matches);
        }

        // Loop through the matches
        if (!empty($matches)) {
            $matchedCookies = array();
            foreach ($matches[0] as $index => $match) {

                // Extract the cookie-information
                $cookieName = $matches[1][$index];
                $cookieValue = $matches[2][$index];

                // Strip the meta-data from the cookie
                if(preg_match('/^([^\;]+)\;(.*)/', $cookieValue, $cookieValueMatch)) {
                    $cookieValue = $cookieValueMatch[1];
                }

                // Trim the cookie
                $cookieValue = trim($cookieValue);

                // Check if the cookie was dealt with or not
                if (in_array($cookieName, $matchedCookies)) {
                    continue;
                } else {
                    $matchedCookies[] = $cookieName;
                }

                // Set the cookie
                if (!headers_sent()) {
                    if ($cookieName == 'persistent_shopping_cart' && isset($matches[3][$index]) && preg_match('/expires=([^\;]+)/', $matches[3][$index], $paramsMatch)) {
                        $expires = strtotime($paramsMatch[1]);
                    } else {
                        $expires = 0;
                    }
                        
                    setcookie($cookieName, $cookieValue, $expires, '/', '.'.JURI::getInstance()->toString(array('host')));
                    $_COOKIE[$cookieName] = $cookieValue;
                }

                // Store this cookie also in the default Joomal! session (in case extra cookies are disabled)
                $session = JFactory::getSession();
                $session->set('magebridge.cookie.'.$cookieName, $cookieValue);
            }
        }

        // Handle the extra remember-me cookie
        $user = JFactory::getUser();
        if($user->id > 0 && !empty($_COOKIE['persistent_shopping_cart'])) {
            $app = JFactory::getApplication();

            $password = $user->password_clear;
            if(empty($password)) $password = JRequest::getString('password');
            if(empty($password)) $password = $user->password;

            if(!empty($password)) {
        
                $credentials = array('username' => $user->username, 'password' => $password);

			    // Create the encryption key, apply extra hardening using the user agent string.
			    $privateKey = JApplication::getHash(@$_SERVER['HTTP_USER_AGENT']);

			    $key = new JCryptKey('simple', $privateKey, $privateKey);
			    $crypt = new JCrypt(new JCryptCipherSimple, $key);
			    $rcookie = $crypt->encrypt(serialize($credentials));
			    $lifetime = time() + 365 * 24 * 60 * 60;

			    // Use domain and path set in config for cookie if it exists.
			    $cookie_domain = $app->getCfg('cookie_domain', '');
			    $cookie_path = $app->getCfg('cookie_path', '/');
			    setcookie(JApplication::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, $cookie_path, $cookie_domain);
            }
        }

        // Handle redirects
        preg_match('/^Location: ([^\s]+)/m', $this->_head['headers'], $matches);
        if ($this->_allow_redirects && (preg_match('/^3([0-9]+)/', $this->_head['http_code']) || !empty($matches))) {

            $originalLocation = trim(array_pop($matches));
            $location = $originalLocation;

            // Check for a location-override
            if ($this->getHeader('X-MageBridge-Location') != null) {

                // But only override the location, if there is no error present
                if (strstr($location, 'startcustomization=1') == false) {
                    MageBridgeModelDebug::getInstance()->notice('X-MageBridge-Location = '.$this->getHeader('X-MageBridge-Location'));
                    $location = $this->getHeader('X-MageBridge-Location');
                }
            }

            // Check for a location-override if the customer is logged in
            if ($this->getHeader('X-MageBridge-Location-Customer') != null && $this->getHeader('X-MageBridge-Customer') != null) {
                MageBridgeModelUser::getInstance()->postlogin($this->getHeader('X-MageBridge-Customer'), null, true, true);
                MageBridgeModelDebug::getInstance()->notice('X-MageBridge-Location-Customer = '.$this->getHeader('X-MageBridge-Location-Customer'));
                $location = $this->getHeader('X-MageBridge-Location-Customer');
            }

            // Check for the location in the CURL-information
            if (empty($location) && isset($this->_head['info']['redirect_url'])) {
                $location = $this->_head['info']['redirect_url'];
            }

            // No location could be found
            if (empty($location)) {
                MageBridgeModelDebug::getInstance()->trace('Redirect requested but no URL found', $this->head['headers']);
                return false;
            }

            // Check if the current location is the Magento homepage, and if so, override it with the Joomla!-stored referer instead
            $referer = $bridge->getHttpReferer();
            if ($location == $bridge->getJoomlaBridgeUrl()) {
                if (MagebridgeModelConfig::load('use_homepage_for_homepage_redirects') == 1) {
                    $location = JURI::base();
                } elseif (MagebridgeModelConfig::load('use_referer_for_homepage_redirects') == 1 && !empty($referer) && $referer != JURI::current()) {
                    $location = $referer;
                }
            }

            //$location = preg_replace('/magebridge\.php\//', '', $location);
            MageBridgeModelDebug::getInstance()->warning( 'Trying to redirect to new location '.$location);
            header('X-MageBridge-Redirect: '.$originalLocation);
            $this->setRedirect($location);
        }

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

    /*
     * Method to get a HTTP-header from the CURL-response
     *
     * @param string $name
     * @return string
     */
    private function getHeader($name)
    {
        if (preg_match('/'.$name.': (.*)/', $this->_head['headers'], $match)) {
            return trim($match[1]);
        }
        return null;
    }

    /*
     * Method to set a body
     *
     * @param string $handle
     * @param mixed $data
     * @return string
     */
    public function setCurlBody($handle = null, $data = null)
    {
        print $data;
        return Yireo::strlen($data);
    }

    /*
     * Method to set a HTTP-header
     *
     * @param string $handle
     * @param mixed $data
     * @return string
     */
    public function setCurlHeaders($handle = null, $data = null)
    {
        header(rtrim($data));
        return Yireo::strlen($data);
    }

    /*
     * Method to set a raw header
     *
     * @param string $handle
     * @param string $header
     * @return string
     */
    public function setRawHeader($handle, $header)
    {
        $this->rawheaders[] = $header;
        return Yireo::strlen($header);
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
        } else if (!empty($data) && preg_match('/\<\/rss\>$/', $data)) {
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

        // Replace the System URL for the site
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

