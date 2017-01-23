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
 * MageBridge class for the browse-block
 */

class Yireo_MageBridge_Block_Browse extends Mage_Core_Block_Template
{
    /**
     * @var Yireo_MageBridge_Model_Client
     */
    protected $client;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $request;

    /**
     * @var Mage_Core_Model_App
     */
    protected $app;
    
    /**
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/browse.phtml');
        $this->client = Mage::getSingleton('magebridge/client');
        $this->request = Mage::app()->getRequest();
        $this->app = Mage::app();
    }

    /**
     * Helper to return the header of this page
     *
     * @param string $title
     *
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - ' . $this->__($title);
    }

    /**
     * Helper to return the menu
     *
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /**
     * Check the API connection to Joomla!
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getApiResult($store)
    {
        $result = $this->client->call('magebridge.test', null, $store);

        if (empty($result)) {
            $result = 'No response';
        }

        return $result;
    }

    /**
     * Return the API-details
     *
     * @return string
     */
    public function getApiDetails()
    {
        $scope = $this->determineScope();
        $store = $scope['store'];
        $scopeName = $scope['name'];

        $apiHost = Mage::getStoreConfig('magebridge/joomla/api_url', $store);
        $apiHost = $this->filterApiHost($apiHost);

        $data = array(
            'scope_name' => $scopeName,
            'api_host' => $apiHost,
            'api_url' => Mage::getStoreConfig('magebridge/joomla/api_url', $store),
            'api_user' => Mage::getStoreConfig('magebridge/joomla/api_user', $store),
            'api_key' => Mage::getStoreConfig('magebridge/joomla/api_key', $store),
            'api_result' => $this->getApiResult($store),
        );

        return $data;
    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    protected function determineScope()
    {
        $scope = $this->request->getParam('scope');
        $scopeId = $this->request->getParam('id');

        switch ($scope) {
            case 'websites':
                $scopeName = $this->app->getWebsite($scopeId)->getName() . ' [' . $scope . ']';
                $store = $this->app->getWebsite($scopeId)->getDefaultStore();
                break;
            case 'stores':
                $scopeName = $this->app->getStore($scopeId)->getName() . ' [' . $scope . ']';
                $store = $this->app->getStore($scopeId)->getStoreId();
                break;
            default:
                $scopeName = 'Global';
                $store = null;
                break;
        }

        return array(
            'name' => $scopeName,
            'store' => $store,
        );
    }

    /**
     * @param $apiHost
     *
     * @return mixed
     */
    protected function filterApiHost($apiHost)
    {
        $apiHost = preg_replace('/index.php(.*)$/', '', $apiHost);
        $apiHost = preg_replace('/\/components\/magebridge(.*)$/', '', $apiHost);
        $apiHost = preg_replace('/\/component\/magebridge(.*)$/', '', $apiHost);
        $apiHost = preg_replace('/^(http|https):\/\//', '', $apiHost);
        return $apiHost;
    }
}
