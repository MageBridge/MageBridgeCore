<?php
/**
 * Joomla! module MageBridge: Block
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Helper-class for the module
 */
class modMageBridgeBlockHelper
{
    /*
     * Method to be called as soon as MageBridge is loaded
     * 
     * @access public
     * @param JParameter $params
     * @return array
     */
    static public function register($params = null)
    {
        // Get the block name
        $blockName = modMageBridgeBlockHelper::getBlockName($params);
        $arguments = modMageBridgeBlockHelper::getArguments($params);
        
        // Initialize the register 
        $register = array();
        $register[] = array('block', $blockName, $arguments);

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = array('headers');
        }
        return $register;
    }

    /*
     * Build output for the AJAX-layout
     * 
     * @access public
     * @param JParameter $params
     * @return string
     */
    static public function ajaxbuild($params = null)
    {
        // Get the block name
        $blockName = modMageBridgeBlockHelper::getBlockName($params);

        // Include the MageBridge bridge
        $bridge = MageBridgeModelBridge::getInstance();

        // Load CSS if needed
        if ($params->get('load_css', 1) == 1) {
            $bridge->setHeaders('css');
        }

        // Load JavaScript if needed
        if ($params->get('load_js', 1) == 1) {
            $bridge->setHeaders('js');
        }

        // Load the Ajax script
        $script = MageBridgeAjaxHelper::getScript($blockName, 'magebridge-'.$blockName);
        $document = JFactory::getDocument();
        $document->addCustomTag( '<script type="text/javascript">'.$script.'</script>');
    }

    /*
     * Fetch the content from the bridge
     * 
     * @access public
     * @param JParameter $params
     * @return string
     */
    static public function build($params = null)
    {
        // Get the block name
        $blockName = modMageBridgeBlockHelper::getBlockName($params);
        $arguments = modMageBridgeBlockHelper::getArguments($params);

        // Include the MageBridge bridge
        $bridge = MageBridgeModelBridge::getInstance();

        // Load CSS if needed
        if ($params->get('load_css', 1) == 1) {
            $bridge->setHeaders('css');
        }

        // Load JavaScript if needed
        if ($params->get('load_js', 1) == 1) {
            $bridge->setHeaders('js');
        }

        // Get the block
        MageBridgeModelDebug::getInstance()->notice('Bridge called for block "'.$blockName.'"');
        $block = $bridge->getBlock($blockName, $arguments);

        // Return the output
        return $block;
    }

    /*
     * Helper-method to construct the blocks arguments
     * 
     * @access public
     * @param JParameter $params
     * @return array
     */
    static public function getArguments($params)
    {
        // Parse the templates
        $blockTemplate = trim($params->get('block_template'));
        $blockType = trim($params->get('block_type'));
        $blockArguments = trim($params->get('block_arguments'));
        if(!empty($blockTemplate)) $arguments['template'] = $blockTemplate;
        if(!empty($blockType)) $arguments['type'] = $blockType;
        if(!empty($blockArguments)) {
            $blockArguments = explode("\n", $blockArguments);
            foreach($blockArguments as $blockArgumentIndex => $blockArgument) {
                $blockArgument = explode('=', $blockArgument);
                if(!empty($blockArgument[1])) {
                    $blockArguments[$blockArgument[0]] = $blockArgument[1];
                    unset($blockArguments[$blockArgumentIndex]);
                }
            }
            if(!empty($blockArguments)) {
                $arguments['arguments'] = $blockArguments;
            }
        }

        return $arguments;
    }

    /*
     * Helper-method to fetch the block name from the parameters
     * 
     * @access public
     * @param JParameter $params
     * @return string
     */
    static public function getBlockName($params)
    {
        $block = trim($params->get('custom'));
        if (empty($block)) {
            $block = $params->get('block', $block);
        }

        if (empty($block)) {
            $blockTemplate = trim($params->get('block_template'));
            $blockType = trim($params->get('block_type'));
            $block = $blockType.$blockTemplate;
        }

        return $block;
    }
}
