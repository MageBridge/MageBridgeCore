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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge View Helper
 */
class MageBridgeViewHelper
{
    /**
     * Helper-method to initialize YireoCommonView-based views
     *
     * @param string $name
     * @return mixed
     */
    public static function initialize($title)
    {
        // Load important variables
        $document = JFactory::getDocument();
        $view = JFactory::getApplication()->input->getCmd('view');

        // Add CSS-code
        $document->addStyleSheet(JUri::root() . 'media/com_magebridge/css/backend.css');
        $document->addStyleSheet(JUri::root() . 'media/com_magebridge/css/backend-view-' . $view . '.css');

        if (MageBridgeHelper::isJoomla25()) {
            $document->addStyleSheet(JUri::root() . 'media/com_magebridge/css/backend-j25.css');
        }
        if (MageBridgeHelper::isJoomla35()) {
            $document->addStyleSheet(JUri::root() . 'media/com_magebridge/css/backend-j35.css');
        }

        // Page title
        $title = JText::_('MageBridge') . ': ' . JText::_('COM_MAGEBRIDGE_VIEW_' . strtoupper(str_replace(' ', '_', $title)));
        $icon = 'logo.png';
        $layout = new JLayoutFile('joomla.toolbar.title');
        $html   = $layout->render(['title' => $title, 'icon' => $icon]);

        $app = JFactory::getApplication();
        $app->JComponentTitle = $html;
        JFactory::getDocument()->setTitle(strip_tags($title) . ' - ' . $app->get('sitename') . ' - ' . JText::_('JADMINISTRATION'));

        // Add the menu
        self::addMenuItems();
    }

    /**
     * Helper-method to add all the submenu-items for this component
     *
     * @param null
     * @return null
     */
    protected static function addMenuItems()
    {
        $menu = JToolBar::getInstance('submenu');
        if (method_exists($menu, 'getItems')) {
            $currentItems = $menu->getItems();
        } else {
            $currentItems = [];
        }

        $items = [
            'home',
            'config',
            'stores',
            'products',
            'usergroups',
            'connectors',
            'urls',
            'users',
            'check',
            'logs',
            'update',
        ];

        foreach ($items as $view) {
            // @todo: Integrate this with the abstract-helper

            // Skip this view, if it does not exist on the filesystem
            if (!is_dir(JPATH_COMPONENT . '/views/' . $view)) {
                continue;
            }

            // Skip this view, if ACLs prevent access to it
            if (MageBridgeAclHelper::isAuthorized($view, false) == false) {
                continue;
            }

            // Add the view
            $active = (JFactory::getApplication()->input->getCmd('view') == $view) ? true : false;
            $url = 'index.php?option=com_magebridge&view=' . $view;
            $title = JText::_('COM_MAGEBRIDGE_VIEW_' . $view);

            $alreadySet = false;
            foreach ($currentItems as $currentItem) {
                if ($currentItem[1] == $url) {
                    $alreadySet = true;
                    break;
                }
            }

            if ($alreadySet == false) {
                $menu->appendButton($title, $url, $active);
            }
        }
        return;
    }
}
