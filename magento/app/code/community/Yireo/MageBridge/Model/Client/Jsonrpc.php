<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com/
 */

/*
 * MageBridge model for JSON-RPC client-calls
 */
class Yireo_MageBridge_Model_Client_Jsonrpc extends Yireo_MageBridge_Model_Client
{
    /*
     * Method to call a JSON-RPC method
     *
     * @access public
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function makeCall($url, $method, $params = array(), $store = null)
    {
        // Get the authentication data
        $auth = $this->getApiAuthArray($store);
        $method = preg_replace('/^magebridge\./', '', $method);

        // If these values are not set, we are unable to continue
        if(empty($url ) || $auth == false) {
            return false;
        }

        // Add the $auth-array to the parameters
        $params['api_auth'] = $auth;

        // Construct an ID
        $id = md5($method);

        // Construct the POST-data
        $post = array(
            'method' => $method,
            'params' => $params,
            'id' => $id,
        );
        $post = Zend_Json_Encoder::encode($post);

        // Initialize a CURL-client
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

        // Build the CURL connection and receive feedback
        $data = curl_exec($ch);

        if(empty($data) || !preg_match('/^\{/', $data)) {
            Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: Wrong data in JSON-RPC reply', $data);
            Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: CURL error', curl_error($ch));
            return 'Wrong data in JSON-RPC reply';
        }

        // Try to decode the result
        $decoded = json_decode($data, true);
        if(empty($decoded)) {
            Mage::getSingleton('magebridge/debug')->error('JSON-RPC: Empty JSON-response');
            Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: Actual response', $data);
            return 'Empty JSON-response';
        }

        $data = $decoded;
        if(!is_array($data)) {
            Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: JSON-response is not an array', $data);
            return 'JSON-response is not an array';

        } else {
            if(isset($data['error']) && !empty($data['error']['message'])) {
                Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: JSON-error', $data['error']['message']);
                return $data['error']['message'];

            } elseif(!isset($data['result'])) {
                Mage::getSingleton('magebridge/debug')->error('JSON-RPC: No result in JSON-data');
                return 'No result in JSON-data';
            }
        }

        Mage::getSingleton('magebridge/debug')->trace('JSON-RPC: Result', $data['result']);
        return $data['result'];
    }
} 
