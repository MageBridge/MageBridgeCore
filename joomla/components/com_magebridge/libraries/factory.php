<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * Main bridge class
 */
class MageBridge
{
    /**
     * Method to get the current bridge-instance
     */
    public static function getBridge()
    {
        return MageBridgeModelBridge::getInstance();
    }

    /**
     * Method to get the MageBridge configuration
     */
    public static function getConfig()
    {
        return MageBridgeModelConfig::getSingleton();
    }

    /**
     * Method to get the current register-instance
     */
    public static function getRegister()
    {
        return MageBridgeModelRegister::getInstance();
    }

    /**
     * Method to handle Magento events
     */
    public static function setEvents($data = null)
    {
        return MageBridgeModelBridgeEvents::getInstance()->setEvents($data);
    }

    /**
     * Methot to set the breadcrumbs
     */
    public static function setBreadcrumbs()
    {
        return MageBridgeModelBridgeBreadcrumbs::getInstance()->setBreadcrumbs();
    }

    /**
     * Method to get the headers
     */
    public static function getHeaders()
    {
        return MageBridgeModelBridgeHeaders::getInstance()->getResponseData();
    }

    /**
     * Method to set the headers
     */
    public static function setHeaders()
    {
        return MageBridgeModelBridgeHeaders::getInstance()->setHeaders();
    }

    /**
     * Method to get the category tree
     */
    public static function getCatalogTree()
    {
        return MageBridge::getAPI('magebridge_category.tree');
    }

    /**
     * Method to get the products by tag
     */
    public static function getProductsByTags($tags = [])
    {
        return MageBridge::getAPI('magebridge_tag.list', $tags);
    }

    /**
     * Method to get a specific API resource
     */
    public static function getAPI($resource = null, $id = null)
    {
        MageBridgeModelDebug::getInstance()->notice('Bridge: getAPI( resource: '.$resource.', id: '.$id.')');
        return MageBridgeModelBridgeSegment::getInstance()->getResponseData('api', $resource);
    }

    /**
     * Method to get the Magento debug-instance
     */
    public static function getDebug()
    {
        return MageBridgeModelBridgeSegment::getInstance();
    }

    /**
     * Method to get the Magento debug-messages
     */
    public static function getDebugData()
    {
        return MageBridgeModelBridgeSegment::getInstance()->getResponseData('debug');
    }

    /**
     * Method to return the block-instance
     */
    public static function getBlock()
    {
        return MageBridgeModelBridgeBlock::getInstance();
    }

    /**
     * Method to return a specific block
     */
    public static function getBlockData($block_name)
    {
        return MageBridgeModelBridgeBlock::getInstance()->getBlock($block_name);
    }

    /**
     * Method to get the meta-request instance
     */
    public static function getMeta()
    {
        return MageBridgeModelBridgeMeta::getInstance();
    }

    /**
     * Method to get the meta-request data
     */
    public static function getMetaData()
    {
        return MageBridgeModelBridgeMeta::getInstance()->getRequestData();
    }

    /**
     * Method to get the user-request instance
     */
    public static function getUser()
    {
        return MageBridgeModelUser::getInstance();
    }

    /**
     * Method to get the meta-request data
     */
    public static function getUserData()
    {
        return MageBridgeModelUser::getInstance()->getRequestData();
    }

    /**
     * Method to display a link for adding Simple Products to cart
     */
    public static function addToCartUrl($product_id, $quantity = 1, $options = [], $return_url = null)
    {
        // Basic URL
        $form_key = MageBridgeModelBridge::getInstance()->getSessionData('form_key');
        $request = 'checkout/cart/add/product/'.$product_id.'/qty/'.$quantity.'/';
        if (!empty($form_key)) {
            $request .= 'form_key/'.$form_key.'/';
        }

        // Add the return URL
        if (!empty($return_url)) {
            $uenc = MageBridgeEncryptionHelper::base64_encode(JRoute::_($return_url));
            $request .= 'uenc/'.$uenc.'/';
        }

        // Add the product-options
        if (!empty($options)) {
            $request .= '?';
            foreach ($options as $name => $value) {
                $request .= 'options['.$name.']='.$value.'&';
            }
        }

        return MageBridgeUrlHelper::route($request);
    }

    /**
     * Method to load ProtoType
     */
    public static function loadPrototype()
    {
        return MageBridgeModelBridgeHeaders::getInstance()->loadPrototype();
    }

    /**
     * Method to load jQuery
     */
    public static function loadJquery()
    {
        MageBridgeTemplateHelper::load('jquery');
    }

    /**
     * Create a specific MageBridge route
     */
    public static function route($request = null, $xhtml = null)
    {
        return MageBridgeUrlHelper::route($request, $xhtml);
    }

    /**
     * Register a segment in the bridge
     */
    public static function register($type = null, $name = null, $arguments = null)
    {
        return self::getRegister()->add($type, $name, $arguments);
    }

    /**
     * Build the bridge
     */
    public static function build()
    {
        return self::getBridge()->build();
    }

    /**
     * Fetch a segment from the bridge
     */
    public static function get($id = null)
    {
        return self::getRegister()->getById($id);
    }

    /**
     * Method to encrypt a string
     */
    public static function encrypt($string)
    {
        return MageBridgeEncryptionHelper::encrypt($string);
    }

    /**
     * Method to decrypt a string
     */
    public static function decrypt($string)
    {
        return MageBridgeEncryptionHelper::decrypt($string);
    }

    /**
     * Method to detect whether the current URL is the JSON-RPC URL
     */
    public static function isApiPage()
    {
        // Detect the XML-RPC application
        $application = JFactory::getApplication();
        if ($application->getName() == 'xmlrpc') {
            return true;
        }

        // Detect the JSON-RPC application
        if (JFactory::getApplication()->input->getCmd('option') == 'com_magebridge' && (JFactory::getApplication()->input->getCmd('view') == 'jsonrpc' || JFactory::getApplication()->input->getCmd('controller') == 'jsonrpc')) {
            return true;
        }

        return false;
    }
}
