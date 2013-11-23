<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
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
    /*
     * Method to display the requested view
     */
    public function display($tpl = null)
    {
        // Set which block to display
        $this->setBlock('content');

        // Build the bridge right away, because we need data from Magento
        $this->build();

        // Determine which template to display
        if (MageBridgeTemplateHelper::isProductPage()) {
            $tpl = 'product';
        } else if (MageBridgeTemplateHelper::isCategoryPage()) {
            $tpl = 'category';
        }

        // Output component-only pages
        $bridge = MageBridge::getBridge();
        if ($bridge->isAjax()) {
            print $this->block;
            JFactory::getApplication()->close();
        }

        parent::display($tpl);
    }
}
