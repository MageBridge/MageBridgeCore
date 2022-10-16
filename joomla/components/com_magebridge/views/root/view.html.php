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
class MageBridgeViewRoot extends MageBridgeView
{
    /**
     * Method to display the requested view
     */
    public function display($tpl = null)
    {
        // Set which block to display
        $this->setBlock('content');

        // Build the bridge right away, because we need data from Magento
        $block = $this->build();

        // Determine which template to display
        if (MageBridgeTemplateHelper::isProductPage()) {
            $tpl = 'product';
        } elseif (MageBridgeTemplateHelper::isCategoryPage()) {
            $tpl = 'category';
        }

        // Output component-only pages
        $bridge = MageBridge::getBridge();
        if ($bridge->isAjax()) {
            print $block;
            JFactory::getApplication()->close();
        }

        // Add controller information
        $mageConfig = $bridge->getMageConfig();
        $mageController = (isset($mageConfig['controller'])) ? $mageConfig['controller'] : null;
        $mageAction = (isset($mageConfig['action'])) ? $mageConfig['action'] : null;

        // Assemble the page class
        $contentClass = ['magebridge-content'];
        if (!empty($mageController)) {
            $contentClass[] = 'magebridge-'.$mageController;
        }
        if (!empty($mageAction)) {
            $contentClass[] = 'magebridge-'.$mageController.'-'.$mageAction;
        }
        $this->content_class = $contentClass;

        parent::display($tpl);
    }
}
