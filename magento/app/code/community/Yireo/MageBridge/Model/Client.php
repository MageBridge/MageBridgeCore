<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */
/*
 * MageBridge model for Joomla! API client-calls
 */

class Yireo_MageBridge_Model_Client
{
    /**
     * @var Yireo_MageBridge_Helper_Data
     */
    protected $helper;

    /**
     * @var Yireo_MageBridge_Helper_Encryption
     */
    protected $encryptionHelper;

    /**
     * @var Yireo_MageBridge_Model_Client_Jsonrpc
     */
    protected $client;

    /**
     * @var Yireo_MageBridge_Model_Debug
     */
    protected $debug;

    /**
     * Yireo_MageBridge_Model_Client constructor.
     */
    public function __construct()
    {
        $this->helper = Mage::helper('magebridge');
        $this->encryptionHelper = Mage::helper('magebridge/encryption');
        $this->client = Mage::getModel('magebridge/client_jsonrpc');
        $this->coreModel = Mage::getSingleton('magebridge/core');
        $this->debug = Mage::getSingleton('magebridge/debug');
    }

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
        if (empty($url)) {
            return false;
        }

        // Make sure we are working with an array
        if (!is_array($params)) {
            $params = array();
        }

        // Initialize the API-client
        $auth = $this->getApiAuthArray($store);

        // Call the remote method
        $rt = $this->client->makeCall($url, $method, $auth, $params, $store);
        return $rt;
    }

    /*
     * Method that returns API-authentication-data as a basic array
     *
     * @access public
     * @param null
     * @return array
     */
    public function getApiAuthArray($store = null)
    {
        $apiUser = $this->helper->getApiUser($store);
        $apiKey = $this->helper->getApiKey($store);

        if (empty($apiUser) || empty($apiKey)) {
            $this->debug->warning('Listener getApiAuthArray: api_user or api_key is missing');
            $this->debug->trace('Listener: Meta data', $this->coreModel->getMetaData());
            return false;
        }

        $auth = array(
            'api_user' => $this->encryptionHelper->encrypt($apiUser),
            'api_key' => $this->encryptionHelper->encrypt($apiKey),
        );

        return $auth;
    }
} 
