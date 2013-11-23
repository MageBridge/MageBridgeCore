<?php
/**
 * Joomla! MageBridge - Content plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

// Import the MageBridge autoloader
include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Content Plugin
 */
class plgContentMageBridge extends JPlugin
{
    /**
     * Event onContentPrepare
     * 
     * @access public
     * @param string $context
     * @param object $row
     * @param JParameter $params
     * @param mixed $page
     * @return null
     */
    public function onContentPrepare($context, $row, $params, $page)
    {
        // Do not continue if not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Check for Magento CMS-tags
        if (!empty($row->text) && preg_match('/{{([^}]+)}}/', $row->text)) {

            // Get system variables
            $bridge = MageBridgeModelBridge::getInstance();

            // Include the MageBridge register
            $key = md5(var_export($row, true)).':'.JRequest::getCmd('option');
            $text = MageBridgeEncryptionHelper::base64_encode($row->text);

            // Conditionally load CSS
            if ($this->getParams()->get('load_css') == 1 || $this->getParams()->get('load_js') == 1) {
                $bridge->register('headers');
            }

            // Build the bridge
            $segment_id = $bridge->register('filter', $key, $text);
            $bridge->build();
        
            // Load CSS if needed
            if ($this->getParams()->get('load_css') == 1) {
                $bridge->setHeaders('css');
            }

            // Load JavaScript if needed
            if ($this->getParams()->get('load_js') == 1) {
                $bridge->setHeaders('js');
            }

            // Get the result from the bridge
            $result = $bridge->getSegmentData($segment_id);
            $result = MageBridgeEncryptionHelper::base64_decode($result);
            
            // Only replace the original if the new content exists
            if (!empty($result)) {
                $row->text = $result;
            }
        }
    }

    /**
     * Joomla! 1.5 alias
     * 
     * @access public
     * @param object $article
     * @param JParameter $params
     * @param mixed $limitstart
     * @return null
     */
    public function onPrepareContent(&$article, &$params, $limitstart)
    {
        $this->onContentPrepare('content', $article, $params, $limitstart);
    }

    /**
     * Load the parameters
     * 
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        if (!MageBridgeHelper::isJoomla15()) {
            return $this->params;
        } else {
            jimport('joomla.html.parameter');
            $plugin = JPluginHelper::getPlugin('content', 'magebridge');
            $params = new JParameter($plugin->params);
            return $params;
        }
    }

    /**
     * Return whether MageBridge is available or not
     * 
     * @access private
     * @param null
     * @return mixed $value
     */
    private function isEnabled()
    {
        if (class_exists('MageBridgeModelBridge')) {
            if (MageBridgeModelBridge::getInstance()->isOffline() == false) {
                return true;
            }
        }
        return false;
    }
}
