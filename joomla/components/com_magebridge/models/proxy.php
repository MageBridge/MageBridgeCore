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
 * Bridge proxy class
 */
class MageBridgeModelProxy extends MageBridgeModelProxyAbstract
{
    /**
     * Raw headers received from the proxy
     */
    public $rawheaders = [];

    /**
     * Headers received from the proxy
     */
    protected $head = [];

    /**
     * Body of content
     */
    protected $body = '';

    /**
     * Content fetched through the proxy
     */
    protected $data = '';

    /**
     * Redirect flag
     */
    protected $redirect = false;

    /**
     * Allow redirects flag
     */
    protected $allow_redirects = true;

    /**
     * Encode the data for sending through the proxy
     *
     * @param array $data
     *
     * @return array
     */
    protected function encodeData($data)
    {
        if (empty($data)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $index => $segment) {
                if (empty($segment['data'])) {
                    $data[$index] = $this->encode($segment);
                }
            }
        }

        return $data;
    }

    /**
     * Determine whether the proxy response is non-MageBridge output
     *
     * @param string $response
     *
     * @return bool
     */
    protected function isNonBridgeOutput($response)
    {
        // Check whether the Content-Type is indicating bridge output
        if (!empty($this->head['headers']) && preg_match('/Content-Type: application\/magebridge/i', $this->head['headers'])) {
            return false;
        }

        if ($this->bridge->isAjax()) {
            return true;
        }

        if ($this->isValidResponse($response) == false) {
            $this->debug->notice('Empty decoded response suggests non-bridge output');

            return true;
        }

        // Check whether the Content-Type is indicating non-bridge output
        if ($this->isContentTypeHtml() == false) {
            $this->debug->trace('Detecting non-HTML output in HTTP headers', $this->head['headers']);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isContentTypeHtml()
    {
        if (!empty($this->head['headers']) && preg_match('/Content-Type: (application|text)\/(xml|javascript|json|octetstream|pdf|x-pdf)/i', $this->head['headers'])) {
            return false;
        }

        return true;
    }

    /**
     * Send direct output URL response
     *
     * @param string $response
     */
    protected function sendDirectOutputUrlResponse($response)
    {
        $this->spoofHeaders($response);

        header('Content-Encoding: none');
        print $response;

        $this->app->close();
    }

    /**
     * Try to match one of the direct output URLs
     */
    protected function matchDirectOutputUrls()
    {
        $direct_output_urls   = MageBridgeHelper::csvToArray(MageBridgeModelConfig::load('direct_output'));
        $direct_output_urls[] = 'checkout/onepage/getAdditional';

        if (!empty($direct_output_urls)) {
            $current_url = MageBridgeUrlHelper::getRequest();

            foreach ($direct_output_urls as $direct_output_url) {
                $direct_output_url = trim($direct_output_url);

                if (!empty($direct_output_url) && strstr($current_url, $direct_output_url)) {
                    $this->debug->trace('Detecting non-bridge output through MageBridge configuration', $direct_output_url);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return null|string
     */
    protected function getContentTypeFromHeader()
    {
        if (!preg_match('/Content-Type: (.*)/i', $this->head['headers'], $match)) {
            return null;
        }

        $contentType = strtolower(trim($match[1]));

        return $contentType;
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function convertUrl($url)
    {
        if (!preg_match('/^index\.php\?option\=com/', $url)) {
            return null;
        }

        $newUrl = MageBridgeHelper::filterUrl($url);

        if (empty($newUrl)) {
            return null;
        }

        return $newUrl;
    }

    /**
     * Send non-bridge output response
     *
     * @param string $response
     * @param string $decodedResponse
     */
    protected function sendNonBridgeOutputResponse($response, $decodedResponse)
    {
        // Detect Content-Type
        $contentType = $this->getContentTypeFromHeader();
        if (!empty($contentType)) {
            $this->head['info']['content_type'] = $contentType;
        }

        // Spoof the current HTTP-headers
        $this->spoofHeaders($response);

        // Detect JSON and replace any URL-redirects
        if (is_array($decodedResponse) && isset($decodedResponse['redirect'])) {
            $url = $this->convertUrl($decodedResponse['redirect']);

            if (!empty($url)) {
                $decodedResponse['redirect'] = $url;
                $this->data                  = $this->encode($decodedResponse);
            }
        }

        // Detect HTML and parse it anyway
        if (preg_match('/<\/html>$/', $response)) {
            $response = MageBridgeHelper::filterContent($response);
        }

        // Output the raw content
        $skipContentTypes = ['application/pdf'];
        if (!in_array($contentType, $skipContentTypes)) {
            // Detect HTML and parse it anyway
            if (preg_match('/<\/html>$/', $response)) {
                $response = MageBridgeHelper::filterContent($response);
            }

            header('Content-Length: ' . strlen($response));
        }

        // Nothing is compressed with this bridge
        header('Content-Encoding: none');

        if (!empty($this->head['info']['content_type'])) {
            header('Content-Type: ' . $this->head['info']['content_type']);
        } elseif (preg_match('/^\{\"/', $response)) {
            header('Content-Type: application/javascript');
        }

        print $response;

        $this->debug->warning("Non-bridge output from Magento");
        //$this->debug->trace( "Output", $response );

        $this->app->close();
    }

    /**
     * Handle non-bridge output
     */

    /**
     * Check for a certain HTTP Status code
     *
     * @param string $code
     *
     * @return bool
     */
    protected function isHttpStatus($code)
    {
        if ($this->head['http_code'] == $code) {
            return true;
        }

        return false;
    }

    /**
     * Handle non-bridge output
     *
     * @param string $rawResponse
     * @param string $decodedResponse
     *
     * @return boolean
     */
    protected function handleNonBridgeOutput($rawResponse, $decodedResponse)
    {
        // Determine whether this is non-bridge output
        $nonBridge = $this->isNonBridgeOutput($decodedResponse);

        // Handle non-bridge output
        if ($nonBridge == false) {
            return false;
        }

        // Redirect if needed
        $this->redirectIfProxyWantsIt();

        if ($this->isHttpStatus(200) && !empty($rawResponse)) {
            $this->sendNonBridgeOutputResponse($rawResponse, $decodedResponse);
        }

        return true;
    }

    /**
     * Build the data from the registry
     *
     * @param array $data
     *
     * @return array
     */
    public function build($data)
    {
        if ($this->init != self::CONNECTION_ERROR) {
            // If the request-data is empty, there's no point in making a call
            if (empty($data)) {
                return null;
            }

            $data = $this->encodeData($data);

            // Fetch the data by using POST
            $rawResponse = $this->getRemote($this->bridge->getMagentoBridgeUrl(), $data, MageBridgeModelConfig::load('method'), true);
            $rawResponse = ltrim($rawResponse);

            // Decode the reply
            $decodedResponse = $this->decode($rawResponse);

            // Increase the counter to make sure endless redirects don't happen
            $this->count++;

            //$this->debug->trace( 'Proxy raw response', $raw );
            //$this->debug->trace( 'Proxy decoded response', $decoded );
            // Check whether the current URL is listed for direct output
            if ($this->matchDirectOutputUrls()) {
                $this->sendDirectOutputUrlResponse($rawResponse);
            }

            // Determine whether this is non-bridge output
            if ($this->handleNonBridgeOutput($rawResponse, $decodedResponse)) {
                return null;
            }

            // Set the bridge data
            $this->data = $decodedResponse;

            // Detect events and run them
            if (!empty($this->data['events']['data'])) {
                $this->bridge->setEvents($this->data['events']['data']);
            }

            // Redirect if needed
            $this->redirectIfProxyWantsIt();

            $this->init = self::CONNECTION_SUCCESS;
        }

        return $this->data;
    }

    /**
     * Method to determine whether the bridge-response is valid or not
     *
     * @param array $data
     *
     * @return bool
     */
    public function isValidResponse($data = null)
    {
        if (!empty($data['meta'])) {
            return true;
        }

        // Detect non-bridge AJAX-calls
        if ($this->app->isSite()) {
            return false;
        }

        if ($this->input->getCmd('option') == 'com_magebridge' && $this->input->getCmd('view') == 'root') {
            return false;
        }

        return true;
    }

    /**
     * Method to fetch data from a remote URL
     *
     * @param string  $url
     * @param array   $arguments
     * @param string  $requestType
     * @param boolean $runBridge
     *
     * @return string
     */
    public function getRemote($url = '', $arguments = [], $requestType = null, $runBridge = false)
    {
        // Do not continue if the URL is empty
        if (empty($url)) {
            return null;
        }

        // Take over the _POST data
        if ($requestType === null) {
            if (!empty($_POST)) {
                $requestType = 'post';
            } else {
                $requestType = 'get';
            }
        }

        // Ignore an empty POST, because this wouldn't matter anyway
        if ($requestType == 'post' && empty($arguments)) {
            $requestType = 'get';
        }

        // Convert the arguments into an URL-string
        if ($requestType == 'get' && !empty($arguments)) {
            $url .= '?' . http_build_query($arguments);
        }

        $curlResponse = $this->getCURL($url, $requestType, $arguments, $runBridge);

        return $curlResponse;
    }

    /**
     * Get default CURL arguments
     */
    protected function getCurlDefaultArguments()
    {
        return [
            CURLOPT_RETURNTRANSFER       => true,
            CURLOPT_HEADER               => true,
            CURLOPT_MAXREDIRS            => 0,
            CURLOPT_SSL_VERIFYPEER       => false,
            CURLOPT_SSL_VERIFYHOST       => false,
            CURLOPT_CONNECTTIMEOUT       => MageBridgeModelConfig::load('curl_timeout'),
            CURLOPT_TIMEOUT              => MageBridgeModelConfig::load('curl_timeout'),
            CURLOPT_DNS_CACHE_TIMEOUT    => MageBridgeModelConfig::load('curl_timeout'),
            CURLOPT_DNS_USE_GLOBAL_CACHE => true,
            CURLOPT_COOKIESESSION        => true,
            CURLOPT_FRESH_CONNECT        => false,
            CURLOPT_FORBID_REUSE         => false,
            CURLOPT_BUFFERSIZE           => 8192,
        ];
    }

    /**
     * Set CURL SSL details
     *
     * @param resource $handle
     */
    protected function setCurlSslDetails(&$handle)
    {
        // Set additional SSL options
        $sslVersion = MageBridgeModelConfig::load('ssl_version');

        if (!empty($sslVersion) && !is_numeric($sslVersion)) {
            $sslVersion = constant('CURL_SSLVERSION_' . $sslVersion);
        }

        if (!empty($sslVersion)) {
            curl_setopt($handle, CURLOPT_SSLVERSION, $sslVersion);
        }

        $sslCiphers = MageBridgeModelConfig::load('ssl_ciphers');

        if (!empty($sslCiphers)) {
            curl_setopt($handle, CURLOPT_SSL_CIPHER_LIST, $sslCiphers);
        }
    }

    /**
     * Set CURL HTTP Authentication
     *
     * @param resource $handle
     */
    protected function setCurlHttpAuthentication(&$handle)
    {
        // CURL HTTP-authentication
        $http_user     = MageBridgeModelConfig::load('http_user');
        $http_password = MageBridgeModelConfig::load('http_password');

        if (MageBridgeModelConfig::load('http_auth') == 1) {
            curl_setopt($handle, CURLOPT_HTTPAUTH, MageBridgeModelConfig::load('http_authtype'));
            curl_setopt($handle, CURLOPT_USERPWD, $http_user . ':' . $http_password);
        }
    }

    /**
     * Set CURL cookies
     *
     * @param resource $handle
     */
    protected function setCurlCookies(&$handle)
    {
        $cookies     = MageBridgeBridgeHelper::getBridgableCookies();
        $curlCookies = [];

        foreach ($cookies as $cookieName) {
            $cookieValue = (isset($_COOKIE[$cookieName])) ? $_COOKIE[$cookieName] : null;

            if (empty($cookieValue)) {
                $cookieValue = JFactory::getSession()
                    ->get('magebridge.cookie.' . $cookieName);
            }

            if (empty($cookieValue)) {
                continue;
            }

            $curlCookies[] = $cookieName . '=' . $cookieValue;
        }

        if (!empty($curlCookies)) {
            curl_setopt($handle, CURLOPT_COOKIE, implode(';', $curlCookies));
        }
    }

    /**
     * CURL-wrapper
     *
     * @param string $url
     * @param string $type
     * @param array  $arguments
     * @param bool   $runBridge
     *
     * @return string
     */
    public function getCURL($url, $type = 'get', $arguments = [], $runBridge = false)
    {
        // Load variables
        $httpHeaders = [];

        // Initialize CURL
        $handle = curl_init($url);

        if ($handle == false) {
            return null;
        }

        curl_setopt_array($handle, $this->getCurlDefaultArguments());
        $this->setCurlHeaders($handle);
        $this->setCurlHttpAuthentication($handle);

        // Forward cookies to Magento
        if ($runBridge == true) {
            $this->setCurlCookies($handle);
        }

        // Detect whether certain HTTP headers are set by the client
        foreach ($_SERVER as $header => $value) {
            if (!preg_match('/^http_/i', $header)) {
                continue;
            }

            $header = strtoupper(preg_replace('/http_/i', '', $header));

            if ($header == 'X_REQUESTED_WITH') {
                $httpHeaders[] = 'X-REQUESTED-WITH' . ': ' . $value;
            } else {
                if (preg_match('/^ACCEPT_/', $header)) {
                    $httpHeaders[] = str_replace('_', '-', $header) . ': ' . $value;
                }
            }
        }

        // Add proxy HTTP headers
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $httpHeaders[] = 'X-REAL-IP: ' . $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $httpHeaders[] = 'X-FORWARDED-FOR: ' . $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            $httpHeaders[] = 'VIA: ' . $_SERVER['SERVER_ADDR'];
        }

        // Set SSL options
        $uri = JUri::getInstance();

        if ($uri->isSSL() == true) {
            $httpHeaders[] = 'FRONT-END-HTTPS: On';
        }

        if ($uri->isSSL() == true) {
            $httpHeaders[] = 'X-FORWARD-PROTO: https';
        }

        // Add some extra HTTP headers for HTTP Keep Alive
        if (MageBridgeModelConfig::load('keep_alive') == 0) {
            $httpHeaders[] = 'Connection: close';
        } else {
            $httpHeaders[] = 'Connection: keep-alive';
        }

        // Spoof the browser
        if (MageBridgeModelConfig::load('spoof_browser') == 1) {
            if ($runBridge == true && $this->app->isSite() == 1) {
                curl_setopt($handle, CURLOPT_REFERER, MageBridgeUrlHelper::getRequest());
                curl_setopt($handle, CURLOPT_USERAGENT, ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ''));
            } else {
                curl_setopt($handle, CURLOPT_USERAGENT, $this->getUserAgentBySystem());
            }
        }

        // Automatically handle file uploads
        $tmp_files = $this->helper->upload();

        if (!empty($tmp_files)) {
            foreach ($tmp_files as $name => $tmp_file) {
                if (class_exists('CurlFile')) {
                    $arguments[$name] = new CurlFile($tmp_file['tmp_name'], $tmp_file['type']);
                } else {
                    $arguments[$name] = '@' . $tmp_file['tmp_name'];
                }
            }
        }

        // Set extra options when a POST is handled
        if ($type == 'post') {
            $arguments = (is_array($arguments) && MageBridgeModelConfig::load('curl_post_as_array') == 0) ? http_build_query($arguments) : $arguments;
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $arguments);
            $httpHeaders[] = 'Expect:';
        }

        // Add the HTTP headers
        curl_setopt($handle, CURLOPT_HTTPHEADER, $httpHeaders);

        // Set encoding to zero
        curl_setopt($handle, CURLOPT_ENCODING, '');

        // Handle direct output and bridge output
        $this->debug->notice('CURL init: ' . $url . ' (' . ((MageBridgeUrlHelper::getRequest()) ? MageBridgeUrlHelper::getRequest() : 'no request') . ')');
        $this->handleFileDownloads($handle);
        $data = curl_exec($handle);
        $size = strlen($data);

        if ($size > 1024) {
            $size = round($size / 1024, 2) . 'Kb';
        }

        $this->debug->profiler('CURL response size: ' . $size);

        // Cleanup the temporary uploads
        $this->helper->cleanup($tmp_files);

        // Separate the headers from the body
        $this->head['header_found'] = false;
        $this->head['last_url']     = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
        $this->head['http_code']    = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $this->head['size']         = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $this->head['info']         = curl_getinfo($handle);

        // Determine the separator
        $separator = null;
        if (strpos($data, "\r\n\r\n") > 0) {
            $separator = "\r\n\r\n";
        } elseif (strpos($data, "\n\n") > 0) {
            $separator = "\n\n";
        }

        // Split data into segments
        if (strpos($data, $separator) > 0) {
            $dataSegments               = explode($separator, $data);
            $this->head['header_found'] = true;

            foreach ($dataSegments as $dataSegmentIndex => $dataSegment) {
                // Check for a segment that seems to contain HTTP-headers
                if (preg_match('/(Set-Cookie|Content-Type|Transfer-Encoding):/i', $dataSegment)) {
                    // Get this segment
                    $this->head['headers'] = trim($dataSegment);

                    // Use the remaining segments for the body
                    unset($dataSegments[$dataSegmentIndex]);
                    $this->body = implode("\r\n", $dataSegments);
                    break;
                }

                // Only allow for a body after a header (and ignore double headers)
                unset($dataSegments[$dataSegmentIndex]);
            }
        }

        // Exit when no proper headers have been found
        if ($this->head['header_found'] === false) {
            $this->debug->warning('CURL contains no HTTP headers');

            return null;
        }

        if (empty($this->head['http_code'])) {
            $this->head['http_code'] = 200;
        }

        // Statistics
        $this->debug->profiler('CURL total time: ' . round(curl_getinfo($handle, CURLINFO_TOTAL_TIME), 4) . ' seconds');
        $this->debug->profiler('CURL connect time: ' . round(curl_getinfo($handle, CURLINFO_CONNECT_TIME), 4) . ' seconds');
        $this->debug->profiler('CURL DNS-time: ' . round(curl_getinfo($handle, CURLINFO_NAMELOOKUP_TIME), 4) . ' seconds');
        $this->debug->profiler('CURL download speed: ' . round(curl_getinfo($handle, CURLINFO_SPEED_DOWNLOAD * 8 / 1024), 4) . ' Kb/s');
        //$this->debug->trace( "CURL information", curl_getinfo($handle));
        //$this->debug->trace( "HTTP headers", $this->head );
        //$this->debug->trace( "HTTP body", $this->body );

        // Handle MageBridge HTTP-messaging
        if (preg_match_all('/X-MageBridge-(Notice|Error|Warning): ([^\s]+)/i', $this->head['headers'], $matches)) {
            foreach ($matches[0] as $index => $match) {
                $type    = $matches[1][$index];
                $message = $matches[2][$index];

                if (!empty($type) && !empty($message)) {
                    $message = base64_decode($message);
                    $this->app->enqueueMessage($message, $type);
                }
            }
        }

        // Process the X-MageBridge-Customer header
        if ($this->getHeader('X-MageBridge-Customer') != null) {
            $value = $this->getHeader('X-MageBridge-Customer');
            $this->bridge->addSessionData('customer/email', $value);
            $this->user->postlogin($value, null, true, true);
        }

        // Process the X-MageBridge-Form-Key header
        if ($this->getHeader('X-MageBridge-Form-Key') != null) {
            $value = $this->getHeader('X-MageBridge-Form-Key');
            $this->bridge->addSessionData('form_key', $value);
        }

        // Log other Status Codes than 200
        if ($this->head['http_code'] != 200) {
            if ($this->head['http_code'] == 500) {
                $this->debug->error('CURL received HTTP status ' . $this->head['http_code']);
            } else {
                $this->debug->warning('CURL received HTTP status ' . $this->head['http_code']);
            }
        }

        // If we receive status 0, log it
        if ($this->head['http_code'] == 0) {
            $this->head['http_error'] = curl_error($handle);
            $this->debug->trace('CURL error', curl_error($handle));
        }

        // If we receive an exception, exit the bridge
        if ($this->head['http_code'] == 0 || $this->head['http_code'] == 500) {
            $this->init  = self::CONNECTION_ERROR;
            $this->state = 'INTERNAL ERROR';

            curl_close($handle);

            return $this->body;
        }

        // If we receive a 404, log it
        if ($this->head['http_code'] == 404) {
            $this->init  = self::CONNECTION_ERROR;
            $this->state = '404 NOT FOUND';
            curl_close($handle);

            if ($this->app->isSite() == 1 && MageBridgeModelConfig::load('enable_notfound') == 1) {
                JError::raiseError(404, JText::_('Page Not Found'));

                return null;
            } else {
                header('HTTP/1.0 404 Not Found');

                return $this->body;
            }
        }

        // If we have an empty body, log it
        if (empty($this->body)) {
            $this->debug->warning('CURL received empty body');

            if (!empty($this->head['headers'])) {
                $this->debug->trace('CURL headers', $this->head['headers']);
            }
        }

        // Define which cookies to spoof
        $cookies            = MageBridgeBridgeHelper::getBridgableCookies();
        $defaultSessionName = ini_get('session.name');

        if (empty($defaultSessionName)) {
            $defaultSessionName = 'PHPSESSID';
        }

        $cookies[] = $defaultSessionName; // Add the default session for sake of badly written Magento extensions

        // Handle cookies
        if (MageBridgeModelConfig::load('bridge_cookie_all') == 1) {
            preg_match_all('/Set-Cookie: ([a-zA-Z0-9\-\_\.]+)\=(.*)/i', $this->head['headers'], $matches);
        } else {
            preg_match_all('/Set-Cookie: (' . implode('|', $cookies) . ')\=(.*)/i', $this->head['headers'], $matches);
        }

        // Loop through the matches
        if (!empty($matches)) {
            $matchedCookies = [];

            foreach ($matches[0] as $index => $match) {
                // Extract the cookie-information
                $cookieName  = $matches[1][$index];
                $cookieValue = $matches[2][$index];

                // Strip the meta-data from the cookie
                if (preg_match('/^([^\;]+)\;(.*)/', $cookieValue, $cookieValueMatch)) {
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

                    $uri = JUri::getInstance();
                    setcookie($cookieName, $cookieValue, $expires, '/', '.' . $uri->toString(['host']));
                    $_COOKIE[$cookieName] = $cookieValue;
                }

                // Store this cookie also in the default Joomal! session (in case extra cookies are disabled)
                $session = JFactory::getSession();
                $session->set('magebridge.cookie.' . $cookieName, $cookieValue);
            }
        }

        // Handle the extra remember-me cookie
        $user = JFactory::getUser();

        if ($user->id > 0 && !empty($_COOKIE['persistent_shopping_cart'])) {
            $password = $user->password_clear;

            if (empty($password)) {
                $password = $this->input->getString('password');
            }

            if (empty($password)) {
                $password = $user->password;
            }

            if (!empty($password)) {
                $credentials = ['username' => $user->username, 'password' => $password];

                // Create the encryption key, apply extra hardening using the user agent string.
                $privateKey = JApplication::getHash(@$_SERVER['HTTP_USER_AGENT']);

                $key      = new JCryptKey('simple', $privateKey, $privateKey);
                $crypt    = new JCrypt(new JCryptCipherSimple(), $key);
                $rcookie  = $crypt->encrypt(serialize($credentials));
                $lifetime = time() + 365 * 24 * 60 * 60;

                // Use domain and path set in config for cookie if it exists.
                $cookie_domain = JFactory::getConfig()
                    ->get('cookie_domain', '');
                $cookie_path   = JFactory::getConfig()
                    ->get('cookie_path', '/');
                setcookie(JApplication::getHash('JLOGIN_REMEMBER'), $rcookie, $lifetime, $cookie_path, $cookie_domain);
            }
        }

        // Handle redirects
        preg_match('/^Location: ([^\s]+)/mi', $this->head['headers'], $matches);

        if ($this->allow_redirects && (preg_match('/^3([0-9]+)/', $this->head['http_code']) || !empty($matches))) {
            $originalLocation = trim(array_pop($matches));
            $location         = $originalLocation;

            // Check for a location-override
            if ($this->getHeader('X-MageBridge-Location') != null) {
                // But only override the location, if there is no error present
                if (strstr($location, 'startcustomization=1') == false) {
                    $this->debug->notice('X-MageBridge-Location = ' . $this->getHeader('X-MageBridge-Location'));
                    $location = $this->getHeader('X-MageBridge-Location');
                }
            }

            // Check for a location-override if the customer is logged in
            if ($this->getHeader('X-MageBridge-Location-Customer') != null && $this->getHeader('X-MageBridge-Customer') != null) {
                $this->user->postlogin($this->getHeader('X-MageBridge-Customer'), null, true, true);
                $this->debug->notice('X-MageBridge-Location-Customer = ' . $this->getHeader('X-MageBridge-Location-Customer'));
                $location = $this->getHeader('X-MageBridge-Location-Customer');
            }

            // Check for the location in the CURL-information
            if (empty($location) && isset($this->head['info']['redirect_url'])) {
                $location = $this->head['info']['redirect_url'];
            }

            // No location could be found
            if (empty($location)) {
                $this->debug->trace('Redirect requested but no URL found', $this->head['headers']);

                return false;
            }

            // Check if the current location is the Magento homepage, and if so, override it with the Joomla!-stored referer instead
            $referer = $this->bridge->getHttpReferer();

            if ($location == $this->bridge->getJoomlaBridgeUrl()) {
                if (MageBridgeModelConfig::load('use_homepage_for_homepage_redirects') == 1) {
                    $location = JUri::base();
                } elseif (MageBridgeModelConfig::load('use_referer_for_homepage_redirects') == 1 && !empty($referer) && $referer != JUri::current()) {
                    $location = $referer;
                }
            }

            //$location = preg_replace('/magebridge\.php\//', '', $location);
            $this->debug->warning('Trying to redirect to new location ' . $location);
            header('X-MageBridge-Redirect: ' . $originalLocation);
            $this->setRedirect($location);
        }

        curl_close($handle);

        return $this->body;
    }

    /**
     * Determine whether handling of file downloads is required
     *
     * @return int
     */
    protected function getFileDownloadsId()
    {
        if (!preg_match('/^downloadable\/download\/link\/id\/([a-zA-Z0-9]+)/', MageBridgeUrlHelper::getRequest(), $match)) {
            return 0;
        }

        if (empty($match[1])) {
            return 0;
        }

        return $match[1];
    }

    /**
     * Method to deliver direct output
     *
     * @param resource $handle
     *
     * @return bool
     */
    protected function handleFileDownloads($handle)
    {
        // Do not continue, if we have no match
        if (!$id = $this->getFileDownloadsId()) {
            return false;
        }

        // Construct the temporary cached files to use
        $tmp_body   = $this->config->get('tmp_path') . '/' . $id;
        $tmp_header = $this->config->get('tmp_path') . '/' . $id . '_header';

        // Check whether the cached files exist, otherwise create them
        if (!file_exists($tmp_body) || !file_exists($tmp_header) || filesize($tmp_body) == 0 || filesize($tmp_header) == 0) {
            // Open the file handles
            $tmp_body_handle   = fopen($tmp_body, 'w');
            $tmp_header_handle = fopen($tmp_header, 'w');

            // Make the CURL call
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($handle, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($handle, CURLOPT_FILE, $tmp_body_handle);
            curl_setopt($handle, CURLOPT_WRITEHEADER, $tmp_header_handle);
            curl_setopt($handle, CURLOPT_HTTPHEADER, ['Expect:']);
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
            curl_setopt($handle, CURLOPT_HEADERFUNCTION, [$this, 'setRawHeader']);
            curl_setopt($handle, CURLOPT_HTTPHEADER, ['Expect:']);
            $data = curl_exec($handle);
            file_put_contents($tmp_body, $data);
        }

        // Close the handle
        curl_close($handle);

        // Construct the new HTTP header
        if (!empty($this->rawheaders)) {
            $headers = $this->rawheaders;
        } else {
            if (is_readable($tmp_header)) {
                $headers = file_get_contents($tmp_header);
            } else {
                $headers = null;
            }
        }

        // Handle redirects
        $matches = null;
        @preg_match('/Location: ([^\s]+)/i', $headers, $matches);
        $location = trim(array_pop($matches));

        if (!empty($location)) {
            @unlink($tmp_body);
            @unlink($tmp_header);
            $this->setRedirect($location);

            return false;
        }

        // Parse the headers into an usable array
        if (is_string($headers)) {
            $headers = explode("\r\n", $headers);
        }

        if (!is_array($headers) || empty($headers)) {
            $headers = explode("\n", $headers);
        }

        // Proxy the headers
        if (is_array($headers) && count($headers) > 1) {
            foreach ($headers as $header) {
                $header = trim($header);

                if (empty($header)) {
                    continue;
                }

                if ($this->allowHttpHeader($header)) {
                    header($header);
                }
            }
        } else {
            header('Expires: 0');
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($tmp_body));
            header('Content-Transfer-Encoding: binary');
        }

        header('Content-Length: ' . filesize($tmp_body), true);
        ob_end_flush();
        flush();

        // Output the body
        readfile($tmp_body);

        // Clean up the files
        @unlink($tmp_body);
        @unlink($tmp_header);
        exit;
    }

    /**
     * Method to get a HTTP-header from the CURL-response
     *
     * @param string $name
     *
     * @return string
     */
    protected function getHeader($name)
    {
        if (preg_match('/' . $name . ': (.*)/i', $this->head['headers'], $match)) {
            return trim($match[1]);
        }

        return null;
    }

    /**
     * Method to set a body
     *
     * @param string $handle
     * @param mixed  $data
     *
     * @return string
     */
    public function setCurlBody($handle = null, $data = null)
    {
        print $data;

        return strlen($data);
    }

    /**
     * Method to set a HTTP-header
     *
     * @param string $handle
     * @param mixed  $data
     *
     * @return string
     */
    public function setCurlHeaders($handle = null, $data = null)
    {
        header(rtrim($data));

        return strlen($data);
    }

    /**
     * Method to set a raw header
     *
     * @param string $handle
     * @param string $header
     *
     * @return string
     */
    public function setRawHeader($handle, $header)
    {
        $this->rawheaders[] = $header;

        return strlen($header);
    }

    /**
     * Method to check whether spoofing of HTTP headers is allowed
     *
     * @return bool
     */
    protected function canSpoofHeaders($data)
    {
        if (MageBridgeModelConfig::load('spoof_headers') == 1) {
            return true;
        }

        if (strstr(MageBridgeUrlHelper::getRequest(), 'downloadable/download/link/id')) {
            return true;
        }

        if (!empty($data) && preg_match('/\%PDF/', $data)) {
            return true;
        }

        if (!empty($data) && preg_match('/\<\/rss\>$/', $data)) {
            return true;
        }

        if (!empty($data) && strstr($data, '<?xml version')) {
            return true;
        }

        return false;
    }

    /**
     * Method to check whether a specific HTTP header can be spoofed or not
     *
     * @return bool
     */
    protected function allowHttpHeader($header)
    {
        $header = strtolower($header);

        if (preg_match('/^(http|cache|date|expires|pragma|content|etag|last-modified|x-magebridge)/', $header)) {
            return true;
        }

        return false;
    }

    /**
     * Convert a header-string to an header-array
     */
    protected function convertHeaderStringToArray($headerString)
    {
        $headers = explode("\r\n", $headerString);

        if (!count($headers) > 1) {
            $headers = explode("\n", $headerString);
        }

        return $headers;
    }

    /**
     * Method to spoof the current HTTP headers
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function spoofHeaders($data = null)
    {
        if (empty($this->head['headers'])) {
            return false;
        }

        // Split the header data into an array
        $headers = $this->convertHeaderStringToArray($this->head['headers']);

        if (count($headers) <= 1) {
            return false;
        }

        // Spoof the bridged Content-Type header anyway
        if (preg_match('/content-type: (.*)/i', $this->head['headers'], $match)) {
            header($match[0]);
        }

        // Determine whether to allow spoofing or not
        $spoof = $this->canSpoofHeaders($data);

        // Set the original HTTP headers
        if ($spoof == false) {
            return false;
        }

        foreach ($headers as $header) {
            $header = trim($header);

            if ($this->allowHttpHeader($header)) {
                header($header);
            }
        }

        return true;
    }

    /**
     * Method to get the current HTTP-status
     *
     * @return int
     */
    public function getHttpStatus()
    {
        if (isset($this->head['http_code'])) {
            return $this->head['http_code'];
        }

        return 0;
    }

    /**
     * Method to get the current proxy error
     *
     * @return string
     */
    public function getProxyError()
    {
        if (isset($this->head['http_error'])) {
            return $this->head['http_error'];
        }

        return null;
    }

    /**
     * Method to set the $_allow_redirects flag
     *
     * @param bool @bool
     */
    public function setAllowRedirects($bool = true)
    {
        $this->allow_redirects = (bool) $bool;
    }

    /**
     * Method to set a redirect for later redirection
     *
     * @param string $redirect
     * @param int    $max_redirects
     *
     * @return bool
     */
    public function setRedirect($redirect = null, $max_redirects = 1)
    {
        // Do not redirect if the maximum redirect-count is reached
        if ($this->isMaxRedirect($redirect, $max_redirects) == true) {
            $this->debug->warning('Maximum redirects of ' . $max_redirects . ' reached');

            return false;
        }

        // Strip the base-path from the URL
        $menuitem = MageBridgeUrlHelper::getRootItem();

        if (empty($menuitem)) {
            $menuitem = MageBridgeUrlHelper::getCurrentItem();
        }

        if (!empty($menuitem)) {
            $root_path = str_replace('/', '\/', $menuitem->route);
            $redirect  = preg_replace('/^\//', '', $redirect);
            $redirect  = preg_replace('/^' . $root_path . '/', '', $redirect);
        }

        // When the URL doesnt start with HTTP or HTTPS, assume it is still the original Magento request
        if (!preg_match('/^(http|https):\/\//', $redirect)) {
            $redirect = JUri::base() . 'index.php?option=com_magebridge&view=root&request=' . $redirect;
        }

        // Replace the System URL for the site
        if ($this->app->isSite() && preg_match('/index.php\?(.*)$/', $redirect, $match)) {
            $redirect = str_replace($match[0], preg_replace('/^\//', '', MageBridgeHelper::filterUrl($match[0])), $redirect);
        }

        $this->debug->warning('Redirect set to ' . $redirect);
        $this->redirectUrl = $redirect;

        return true;
    }

    /**
     * Method to maximize the number of redirects (to prevent endless loops)
     *
     * @param string $redirect
     * @param int    $max_redirects
     *
     * @return bool
     */
    public function isMaxRedirect($redirect = null, $max_redirects = 1)
    {
        // Initialize redirection statistics
        if (!isset($_SESSION['mb_redirects'])) {
            $_SESSION['mb_redirects'] = [];
        }

        // Collect all redirection statistics
        if (array_key_exists($redirect, $_SESSION['mb_redirects'])) {
            if ($_SESSION['mb_redirects'][$redirect] == 0) {
                unset($_SESSION['mb_redirects'][$redirect]);

                return true;
            } else {
                $_SESSION['mb_redirects'][$redirect] = (int) $_SESSION['mb_redirects'][$redirect] - 1;
            }
        } else {
            $_SESSION['mb_redirects'][$redirect] = $max_redirects;
        }

        return false;
    }

    /**
     * Method to actually redirect the browser
     *
     * @todo: It's not clear anymore when and why this redirect happens
     */
    public function redirectIfProxyWantsIt()
    {
        // Redirect to the new location
        if (!empty($this->redirectUrl)) {
            $this->debug->warning('Proxy redirect to ' . $this->redirectUrl);
            header('Location: ' . $this->redirectUrl);
            exit;
        } else {
            // We don't redirect so we don't need endless-loop protection anymore
            $_SESSION['mb_redirects'] = [];
        }
    }

    /**
     * Method to get a User-Agent string for MageBridge
     *
     * @return string
     */
    public function getUserAgentBySystem()
    {
        $user_agent = 'MageBridge ' . MageBridgeUpdateHelper::getComponentVersion();
        $user_agent .= ' (Joomla! ' . MageBridgeHelper::getJoomlaVersion() . ')';

        return $user_agent;
    }

    /**
     * Method to reset the proxy
     *
     * @return mixed
     */
    public function reset()
    {
        $this->init  = self::CONNECTION_FALSE;
        $this->state = null;
    }

    /**
     * Method to get the current redirect count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Method to get the proxy data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Method to get the proxy headers
     *
     * @return array
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Method to get a cookie file (deprecated)
     */
    public function getCookieFile()
    {
        return JFactory::getConfig()
                ->get('log_path') . '/magento.tmp';
    }
}
