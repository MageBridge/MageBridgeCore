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
 * MageBridge class for the browse-block
 */
class Yireo_MageBridge_Block_Browse extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('magebridge/browse.phtml');
    }

    /*
     * Helper to return the header of this page
     *
     * @access public
     * @param string $title
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - '.$this->__($title);
    }

    /*
     * Helper to return the menu
     *
     * @access public
     * @param null
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /*
     * Check the API connection to Joomla!
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiResult($store)
    {
        $client = Mage::getSingleton('magebridge/client');
        $result = $client->call('magebridge.test', null, $store);

        if(empty($result)) {
            $result = 'No response';
        }

        return $result;
    }

    /*
     * Return the API-details
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiDetails()
    {
        $scope = Mage::app()->getRequest()->getParam('scope');
        $scope_id = Mage::app()->getRequest()->getParam('id');

        switch($scope) {
            case 'websites':
                $scope_name = Mage::app()->getWebsite($scope_id)->getName().' ['.$scope.']';
                $store = Mage::app()->getWebsite($scope_id)->getDefaultStore();
                break;
            case 'stores':
                $scope_name = Mage::app()->getStore($scope_id)->getName().' ['.$scope.']';
                $store = Mage::app()->getStore($scope_id)->getStoreId();
                break;
            default:
                $scope_name = 'Global';
                $store = null;
                break;
        }

        $api_host = Mage::getStoreConfig('magebridge/joomla/api_url', $store);
        $api_host = preg_replace('/index.php(.*)$/', '', $api_host);
        $api_host = preg_replace('/\/components\/magebridge(.*)$/', '', $api_host);
        $api_host = preg_replace('/\/component\/magebridge(.*)$/', '', $api_host);
        $api_host = preg_replace('/^(http|https):\/\//', '', $api_host);

        $data = array(
            'scope_name' => $scope_name,
            'api_host' => $api_host,
            'api_url' => Mage::getStoreConfig('magebridge/joomla/api_url', $store),
            'api_user' => Mage::getStoreConfig('magebridge/joomla/api_user', $store),
            'api_key' => Mage::getStoreConfig('magebridge/joomla/api_key', $store),
            'api_result' => $this->getApiResult($store),
        );

        return $data;
    }
}
