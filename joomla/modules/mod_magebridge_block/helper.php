<?php
/**
 * Joomla! module MageBridge: Block
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper-class for the module
 */

class ModMageBridgeBlockHelper
{
    /**
     * Method to be called as soon as MageBridge is loaded
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function register($params = null)
    {
        // Get the block name
        $blockName = ModMageBridgeBlockHelper::getBlockName($params);
        $arguments = ModMageBridgeBlockHelper::getArguments($params);

        // Initialize the register
        $register = [];
        $register[] = ['block', $blockName, $arguments];

        if ($params->get('load_css', 1) == 1 || $params->get('load_js', 1) == 1) {
            $register[] = ['headers'];
        }

        return $register;
    }

    /**
     * Build output for the AJAX-layout
     *
     * @access public
     * @param JRegistry $params
     * @return void
     */
    public static function ajaxbuild($params = null)
    {
        // Get the block name
        $blockName = ModMageBridgeBlockHelper::getBlockName($params);

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
        $script = MageBridgeAjaxHelper::getScript($blockName, 'magebridge-' . $blockName);
        $document = JFactory::getDocument();
        $document->addCustomTag('<script type="text/javascript">' . $script . '</script>');
    }

    /**
     * Fetch the content from the bridge
     *
     * @access public
     * @param JRegistry $params
     * @return string
     */
    public static function build($params = null)
    {
        // Get the block name
        $blockName = ModMageBridgeBlockHelper::getBlockName($params);
        $arguments = ModMageBridgeBlockHelper::getArguments($params);

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
        MageBridgeModelDebug::getInstance()->notice('Bridge called for block "' . $blockName . '"');
        $block = $bridge->getBlock($blockName, $arguments);

        // Return the output
        return $block;
    }

    /**
     * Helper-method to construct the blocks arguments
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function getArguments($params)
    {
        // Initial array
        $arguments = [];

        // Fetch parameters
        $blockTemplate = trim($params->get('block_template'));
        $blockType = trim($params->get('block_type'));
        $blockArguments = trim($params->get('block_arguments'));

        // Parse the parameters
        if (!empty($blockTemplate)) {
            $arguments['template'] = $blockTemplate;
        }

        if (!empty($blockType)) {
            $arguments['type'] = $blockType;
        }

        // Parse INI-style arguments into array
        if (!empty($blockArguments)) {
            $blockArguments = explode("\n", $blockArguments);

            foreach ($blockArguments as $blockArgumentIndex => $blockArgument) {
                $blockArgument = explode('=', $blockArgument);

                if (!empty($blockArgument[1])) {
                    $blockArguments[$blockArgument[0]] = $blockArgument[1];
                    unset($blockArguments[$blockArgumentIndex]);
                }
            }
            if (!empty($blockArguments)) {
                $arguments['arguments'] = $blockArguments;
            }
        }

        if (empty($arguments)) {
            return null;
        }

        return $arguments;
    }

    /**
     * Helper-method to fetch the block name from the parameters
     *
     * @access public
     * @param JRegistry $params
     * @return string
     */
    public static function getBlockName($params)
    {
        $block = trim($params->get('custom'));

        if (empty($block)) {
            $block = $params->get('block', $block);
        }

        if (empty($block)) {
            $blockTemplate = trim($params->get('block_template'));
            $blockType = trim($params->get('block_type'));
            $block = $blockType . $blockTemplate;
        }

        return $block;
    }
}
