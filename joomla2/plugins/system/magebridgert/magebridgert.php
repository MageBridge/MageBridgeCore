<?php
/**
 * Joomla! MageBridge - RocketTheme System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
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
 * MageBridge System Plugin
 */
class plgSystemMageBridgeRt extends JPlugin
{
    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        // Load the application
        $application = JFactory::getApplication();

        // Don't do anything in other applications than the frontend
        if ($application->isSite() == false) return false;

        // Load the blacklist settings
        $blacklist = JFactory::getConfig()->get('magebridge.script.blacklist');
        if (empty($whitelist)) $whitelist = array();
        $blacklist[] = '/rokbox.js';
        $blacklist[] = 'gantry/js/browser-engines.js';
        JFactory::getConfig()->set('magebridge.script.blacklist', $blacklist);

        // Read the template-related files
        $ini = JPATH_THEMES.'/'.$application->getTemplate().'/params.ini';
        $ini_content = @file_get_contents($ini);
        $xml = JPATH_THEMES.'/'.$application->getTemplate().'/templateDetails.xml';

        // WARP-usage of "config" file
        if (!empty($ini_content)) {

            // Create the parameters object
            jimport('joomla.html.parameter');
            $params = new JParameter($ini_content, $xml);

            // Load a specific stylesheet per color
            $color = $params->get('colorStyle');
            if (!empty($color)) {
                MageBridgeTemplateHelper::load('css', 'color-'.$color.'.css');
            }
        }

        // Check whether ProtoType is loaded, and add some fixes
        if (MageBridgeTemplateHelper::hasPrototypeJs()) {
            $document = JFactory::getDocument();
            if ($this->getParams()->get('fix_submenu_wrapper', 1)) $document->addStyleDeclaration('div.fusion-submenu-wrapper { margin-top: -12px !important; }');
            if ($this->getParams()->get('fix_body_zindex', 1)) $document->addStyleDeclaration('div#rt-body-surround { z-index:0 !important; }');
            $document->addStyleDeclaration('div.style-panel-container {left: -126px;}');
        }
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
            $plugin = JPluginHelper::getPlugin('system', 'magebridgert');
            $params = new JParameter($plugin->params);
            return $params;
        }
    }

    /**
     * Simple check to see if MageBridge exists
     * 
     * @access private
     * @param null
     * @return bool
     */
    private function isEnabled()
    {
        if (is_file(JPATH_SITE.'/components/com_magebridge/models/config.php')) {
            return true;
        }
        return false;
    }
}
