<?php
/**
 * Joomla! component MageBridge
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent class
require_once JPATH_COMPONENT . '/view.php';

/**
 * HTML View class
 *
 * @package MageBridge
 */
class MageBridgeViewCatalog extends MageBridgeView
{
    /**
     * Method to display the requested view
     */
    public function display($tpl = null)
    {
        // Load the bridge
        $bridge = MageBridgeModelBridge::getInstance();

        // Load the parameters
        $layout = $this->getLayout();
        $params = MageBridgeHelper::getParams();

        // Set the request based upon the choosen category
        $request = ($params->get('request', false)) ? $params->get('request') : MageBridgeUrlHelper::getRequest();
        $prefix = preg_replace('/\?(.*)/', '', $request);
        $suffix = preg_replace('/(.*)\?/', '', $request);

        // Check if this a non-URL-optimized request
        if (is_numeric($prefix)) {
            $request = MageBridgeUrlHelper::getLayoutUrl($layout, $prefix);
            if (!empty($request)) {
                $request .= '?' . $suffix;
            }
        } else {
            // Determine the suffix
            if ($layout == 'product') {
                $suffix = $bridge->getSessionData('catalog/seo/product_url_suffix');
            } else {
                if ($layout == 'category') {
                    $suffix = $bridge->getSessionData('catalog/seo/category_url_suffix');
                }
            }

            // Add the suffix, if this is set in the Magento configuration
            if (!empty($suffix) && !preg_match('/' . $suffix . '$/', $request)) {
                $request .= $suffix;
            }
        }

        // Add the qty parameter
        $qty = JFactory::getApplication()->input->getInt('qty');
        if (!empty($qty)) {
            $request .= 'qty/'.$qty.'/';
        }

        // Check for the redirect parameter
        $redirect = $this->input->getString('redirect');

        if ($layout == 'addtocart' && empty($redirect)) {
            $redirect = 'checkout/cart';
        }

        // Add the redirect parameter
        if (!empty($redirect)) {
            $redirect = MageBridgeUrlHelper::route($redirect);

            if (!empty($redirect)) {
                $request .= 'uenc/' . MageBridgeEncryptionHelper::base64_encode($redirect) . '/';
            }

            $form_key = MageBridgeModelBridge::getInstance()->getSessionData('form_key');

            if (!empty($form_key)) {
                $request .= 'form_key/' . $form_key;
            }
        }

        // Add the mode (for catalog)
        $mode = $params->get('mode');

        if (!empty($mode)) {
            $request .= '?mode=' . $mode;
        }

        // Set the request in the bridge and wait for the response
        $this->setRequest($request);

        // Reuse this request to set the Canonical URL
        if (MageBridgeModelConfig::load('enable_canonical') == 1) {
            $uri = MageBridgeUrlHelper::route($request);
            $document = JFactory::getDocument();
            $document->setMetaData('canonical', $uri);
        }

        // Set which block to display
        $this->setBlock('content');

        parent::display($tpl);
    }
}
