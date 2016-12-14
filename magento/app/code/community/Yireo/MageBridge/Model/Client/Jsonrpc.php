<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com/
 */

/**
 * MageBridge model for JSON-RPC client-calls
 */
class Yireo_MageBridge_Model_Client_Jsonrpc
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var Yireo_MageBridge_Model_Debug
     */
    protected $debug;

    /**
     * Yireo_MageBridge_Model_Client_Jsonrpc constructor.
     */
    protected function __construct()
    {
        $this->debug = Mage::getSingleton('magebridge/debug');
    }

    /*
     * Method to call a JSON-RPC method
     *
     * @access public
     * @param string $method
     * @param array $auth
     * @param array $params
     * @return mixed
     */
    public function makeCall($url, $method, $auth, $params = array(), $store = null)
    {
        // Get the authentication data
        $method = preg_replace('/^magebridge\./', '', $method);

        // If these values are not set, we are unable to continue
        if (empty($url) || $auth === false) {
            return false;
        }

        // Add the $auth-array to the parameters
        $params['api_auth'] = $auth;

        // Construct the POST-data
        $post = array(
            'method' => $method,
            'params' => $params,
            'id' => md5($method),
        );

        $post = $this->mergeUrlParamsIntoPost($url, $post);
        $encodedPost = Zend_Json_Encoder::encode($post);
        $data = $this->getDataFromResource($url, $encodedPost);

        if (empty($data) || !preg_match('/^\{/', $data)) {
            $this->debug->trace('JSON-RPC: Wrong data in JSON-RPC reply', $data);
            $this->debug->trace('JSON-RPC: CURL error', $this->getError());
            return 'Wrong data in JSON-RPC reply';
        }

        // Try to decode the result
        $decoded = json_decode($data, true);
        if (empty($decoded)) {
            $this->debug->error('JSON-RPC: Empty JSON-response');
            $this->debug->trace('JSON-RPC: Actual response', $data);
            return 'Empty JSON-response';
        }

        $data = $decoded;
        if (!is_array($data)) {
            $this->debug->trace('JSON-RPC: JSON-response is not an array', $data);
            return 'JSON-response is not an array';
        }

        if (isset($data['error']) && !empty($data['error']['message'])) {
            $this->debug->trace('JSON-RPC: JSON-error', $data['error']['message']);
            return $data['error']['message'];

        }

        if (!isset($data['result'])) {
            $this->debug->error('JSON-RPC: No result in JSON-data');
            return 'No result in JSON-data';
        }

        $this->debug->trace('JSON-RPC: Result', $data['result']);
        return $data['result'];
    }

    /**
     * @param $url string
     * @param $post array
     *
     * @return array
     */
    protected function mergeUrlParamsIntoPost($url, $post)
    {
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        parse_str($urlQuery, $urlParams);

        foreach ($urlParams as $urlParamName => $urlParamValue) {
            $post = array_merge(array($urlParamName => $urlParamValue), $post);
        }

        return $post;
    }

    /**
     * @return string
     */
    protected function getError()
    {
        return curl_error($this->resource);
    }

    /**
     * @param $url
     * @param $encodedPost
     *
     * @return mixed
     */
    protected function getDataFromResource($url, $encodedPost)
    {
        // Initialize a CURL-client
        $this->resource = curl_init($url);
        curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->resource, CURLOPT_POSTFIELDS, $encodedPost);
        curl_setopt($this->resource, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->resource, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->resource, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->resource, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->resource, CURLOPT_MAXREDIRS, 2);

        // Build the CURL connection and receive feedback
        $data = curl_exec($this->resource);
        
        return $data;
    }
} 
