<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge model for Joomla! API client-calls
 */
class Yireo_MageBridge_Model_Client 
{
    /*
     * Method to call a remote method
     *
     * @access public
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call($method, $params = array(), $store = null)
    {
        // Get the remote API-link from the configuration
        $url = Mage::helper('magebridge')->getApiUrl(null, $store);
        if(empty($url)) {
            return false;
        }

        // Make sure we are working with an array
        if(!is_array($params)) $params = array();

        // Initialize the API-client
        $client = Mage::getModel('magebridge/client_jsonrpc');

        // Call the remote method
        if(!empty($client)) {
            $rt = $client->makeCall($url, $method, $params);
            return $rt;
        }

        return false;
    }

    /*
     * Method that returns API-authentication-data as a basic array
     *
     * @access public
     * @param null
     * @return array
     */
    public function getAPIAuthArray() 
    {
        $api_user = Mage::helper('magebridge')->getApiUser();
        $api_key = Mage::helper('magebridge')->getApiKey();
        if(empty($api_user) || empty($api_key)) {
            Mage::getSingleton('magebridge/debug')->warning('Listener getAPIAuthArray: api_user or api_key is missing');
            Mage::getSingleton('magebridge/debug')->trace('Listener: Meta data', Mage::getSingleton('magebridge/core')->getMetaData());
            return false;
        }

        $auth = array(
            'api_user' => Mage::helper('magebridge/encryption')->encrypt($api_user),
            'api_key' => Mage::helper('magebridge/encryption')->encrypt($api_key),
        );
        return $auth;
    }
} 
