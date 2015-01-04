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
 * MageBridge model for API-calls
 */
class Yireo_MageBridge_Model_Api 
{
    /*
     * Method to get the result of a specific API-call
     * 
     * @access public
     * @param string $resourcePath
     * @param mixed $arguments
     * @return mixed
     */
    public function getResult($resourcePath, $arguments = null)
    {
        if(empty($resourcePath)) {
            Mage::getSingleton('magebridge/debug')->warning('Empty API resource-path');
            return null;
        }

        try {
            // Parse the resource
            $resourceArray = explode( '.', $resourcePath);
            $apiClass = $resourceArray[0];
            $apiMethod = $resourceArray[1];

            $resources = Mage::getSingleton('api/config')->getResources();
            if(isset($resources->$apiClass)) {
                $resource = $resources->$apiClass;
                $apiClass = (string)$resource->model;
                if(isset($resource->methods->$apiMethod)) {
                    $method = $resource->methods->$apiMethod;
                    $apiMethod = (string)$method->method;
                    if(empty($apiMethod)) $apiMethod = $resourceArray[1];
                }
            } else {
                $apiClass = str_replace('_', '/', $resourceArray[0]).'_api';
            }

            Mage::getSingleton('magebridge/debug')->notice('Calling API '.$apiClass.'::'.$apiMethod);
            //Mage::getSingleton('magebridge/debug')->trace('API arguments', $arguments);

            try {
                $apiModel = Mage::getModel($apiClass);
            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Failed to instantiate API-class '.$apiClass.': '.$e->getMessage());
                return false;
            }

            if(empty($apiModel)) {
                Mage::getSingleton('magebridge/debug')->notice('API class returns empty object: '.$apiClass);
                return false;

            } elseif(method_exists($apiModel, $apiMethod)) {
                return call_user_func(array($apiModel, $apiMethod), $arguments);

            } elseif($apiMethod == 'list' && method_exists($apiModel, 'items')) {
                return $apiModel->items($arguments);

            } else {
                Mage::getSingleton('magebridge/debug')->notice('API class "'.$apiClass.'" has no method '.$apiMethod);
                return false;
            }

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to call API: '.$resourcePath.': '.$e->getMessage());
            return false;
        }
    }
}
