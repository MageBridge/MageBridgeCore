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
 * MageBridge class for the joomla-block
 */
class Yireo_MageBridge_Block_Settings_Joomla extends Mage_Core_Block_Template
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
        $this->setData('area','adminhtml');
        $this->setTemplate('magebridge/settings/joomla.phtml');
    }

    /*
     * Helper method to get all current API details
     *
     * @access public
     * @param null
     * @return array
     */
    public function getApiDetails()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('core/config_data');
        $query = 'SELECT * FROM `'.$table.'` WHERE path LIKE "magebridge/joomla/api%";';
        $rows = $connection->fetchAll($query);

        $details = array();
        if(!empty($rows)) { 
            foreach($rows as $row) {
                $key = $row['scope'].'-'.$row['scope_id'];
                if(!isset($details[$key])) {

                    switch($row['scope']) {
                        case 'websites':
                            $scope_name = Mage::app()->getWebsite($row['scope_id'])->getName().' ['.$row['scope'].']';
                            break;
                        case 'stores':
                            $scope_name = Mage::app()->getStore($row['scope_id'])->getName().' ['.$row['scope'].']';
                            break;
                        default:
                            $scope_name = 'Global';
                            break;
                    }

                    $details[$key] = array(
                        'scope' => $row['scope'],
                        'scope_id' => $row['scope_id'],
                        'scope_name' => $scope_name,
                    );
                }

                $path = preg_replace('/^magebridge\/joomla\/(.*)$/', '$1', $row['path']);
                $details[$key][$path] = $row['value'];

                if(!empty($details[$key]['api_url'])) {
                    $url = $details[$key]['api_url'];
                    if(strstr($url, '/xmlrpc/')) {
                        $api_host = preg_replace('/xmlrpc\/$/', '', $url);
                        $api_type = 'XML-RPC (obsolete)';
                    } else {
                        $api_host = $url;
                        $api_host = preg_replace('/index.php(.*)$/', '', $api_host);
                        $api_host = preg_replace('/\/components\/magebridge(.*)$/', '', $api_host);
                        $api_host = preg_replace('/\/component\/magebridge(.*)$/', '', $api_host);
                        $api_host = preg_replace('/^(http|https):\/\//', '', $api_host);
                        $api_type = 'JSON-RPC';
                    }

                    if(empty($details[$key]['api_user'])) $details[$key]['api_user'] = '[use parent]';
                    $details[$key]['api_host'] = $api_host;
                    $details[$key]['api_type'] = $api_type;
                }
            }
        }
        return $details;
    }

    /*
     * Helper method to get the current value of the Joomla! API URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiUrl()
    {
        return Mage::getStoreConfig('magebridge/joomla/api_url');
    }

    /*
     * Helper method to get the currently configured Joomla! API user
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiUser()
    {
        return Mage::getStoreConfig('magebridge/joomla/api_user');
    }

    /*
     * Helper method to get the currently configured Joomla! API key
     *
     * @access public
     * @param null
     * @return string
     */
    public function getApiKey()
    {
        return Mage::getStoreConfig('magebridge/joomla/api_key');
    }

    /*
     * Return the browse URL
     *
     * @access public
     * @param null
     * @return string
     */
    public function getBrowseUrl($scope, $id)
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/browse', array('scope' => $scope, 'id' => $id));
    }
}
