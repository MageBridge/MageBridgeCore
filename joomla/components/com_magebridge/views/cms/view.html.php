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

// Include the parent class
require_once JPATH_COMPONENT.'/view.php';

/**
 * HTML View class
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewCms extends MageBridgeView
{
    /**
     * Method to display the requested view
     */
    public function display($tpl = null)
    {
        // Load the parameters
        $params = MageBridgeHelper::getParams();

        // Load the request
        $request = $params->get('request');
        if (empty($request)) {
            $request = JFactory::getApplication()->input->getString('request');
        }

        // Remove the dummy ID from the request
        $request = preg_replace('/^([0-9]+)\:/', '', $request);

        // Set the request
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
