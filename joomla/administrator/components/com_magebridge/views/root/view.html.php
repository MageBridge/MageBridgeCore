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
require_once JPATH_COMPONENT . '/view.php';

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
        $title = JText::_('MageBridge') . ': ' . JText::_('Magento Admin Panel');
        $icon = 'yireo';
        $layout = new JLayoutFile('joomla.toolbar.title');
        $html   = $layout->render(['title' => $title, 'icon' => $icon]);

        $app = JFactory::getApplication();
        $app->JComponentTitle = $html;
        JFactory::getDocument()->setTitle(strip_tags($title) . ' - ' . $app->get('sitename') . ' - ' . JText::_('JADMINISTRATION'));
        parent::display($tpl);
    }
}
