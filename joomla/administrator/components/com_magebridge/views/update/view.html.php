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

// Require the parent view
require_once JPATH_COMPONENT . '/view.php';

// Define constants
define('MAGEBRIDGE_UPDATE_NOTAVAILABLE', 0);
define('MAGEBRIDGE_UPDATE_NOTNEEDED', 1);
define('MAGEBRIDGE_UPDATE_AVAILABLE', 2);

/**
 * HTML View class
 *
 * @static
 * @package	MageBridge
 */
class MageBridgeViewUpdate extends YireoView
{
    protected $loadToolbar = false;

    /**
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // ???
        MageBridgeViewHelper::initialize('UPDATE');

        // Filters - type
        $options = [
            ['value' => '', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_NONE')],
            ['value' => 'module', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE')],
            ['value' => 'module-site', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE_SITE')],
            ['value' => 'module-admin', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_MODULE_ADMIN')],
            ['value' => 'plugin', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN')],
            ['value' => 'plugin-other', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_OTHER')],
            ['value' => 'plugin-magebridgeproduct', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_PRODUCT')],
            ['value' => 'plugin-magebridgestore', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_STORE')],
            ['value' => 'plugin-magebridgeprofile', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_PROFILE')],
            ['value' => 'plugin-magebridgenewsletter', 'text' => JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FILTER_TYPE_PLUGIN_NEWSLETTER')],
        ];
        $filter_type = $this->getFilter('type');
        $javascript = 'onchange="document.adminForm.submit();"';
        $this->lists['type'] = JHtml::_('select.genericlist', $options, 'filter_type', $javascript, 'value', 'text', $filter_type);

        // Filters - search
        $filter_search = $this->getFilter('search');

        // Toolbar options
        $bar = JToolbar::getInstance('toolbar');
        $bar->appendButton('Standard', 'back', 'LIB_YIREO_VIEW_TOOLBAR_HOME', 'home', false);
        $bar->appendButton('Standard', 'loop', 'LIB_YIREO_VIEW_TOOLBAR_REFRESH', 'refresh', false);
        $bar->appendButton('Standard', 'archive', 'LIB_YIREO_VIEW_TOOLBAR_DBUPGRADE', 'updateQueries', false);

        // Add jQuery for selection effects
        MageBridgeTemplateHelper::load('jquery');
        $this->addJs('backend-update.js');

        // Load the packages
        $data = MageBridgeUpdateHelper::getData();

        // Add filtering
        if (!empty($filter_type) || !empty($filter_search)) {
            if (!empty($filter_type)) {
                $filter_type = explode('-', $filter_type);
            }

            foreach ($data as $index => $extension) {
                if (!empty($filter_search)) {
                    if (
                        !stristr($extension['name'], $filter_search) && !stristr($extension['title'], $filter_search)
                        && !stristr($extension['description'], $filter_search)
                    ) {
                        unset($data[$index]);
                        continue;
                    }
                }

                if (!empty($filter_type)) {
                    if (count($filter_type) == 1) {
                        if ($extension['type'] != $filter_type[0]) {
                            unset($data[$index]);
                            continue;
                        }
                    } else {
                        if ($filter_type[0] == 'module') {
                            if ($extension['type'] != 'module') {
                                unset($data[$index]);
                                continue;
                            } elseif ($extension['type'] != $filter_type[0] || $extension['app'] != $filter_type[1]) {
                                unset($data[$index]);
                                continue;
                            }
                        } elseif ($filter_type[0] == 'plugin') {
                            if ($extension['type'] != 'plugin') {
                                unset($data[$index]);
                                continue;
                            } elseif (
                                $filter_type[1] == 'other' && in_array(
                                    $extension['group'],
                                    ['magebridgeproduct', 'magebridgenewsletter', 'magebridgestore', 'magebridgeprofile']
                                )
                            ) {
                                unset($data[$index]);
                                continue;
                            } elseif ($filter_type[1] != 'other' && $extension['group'] != $filter_type[1]) {
                                unset($data[$index]);
                                continue;
                            }
                        }
                    }
                }
            }
        }

        // Detect whether updates are available
        $update = MAGEBRIDGE_UPDATE_NOTAVAILABLE;
        if (!empty($data)) {
            $update = MAGEBRIDGE_UPDATE_NOTNEEDED;
            foreach ($data as $index => $extension) {
                // Skip this entry for version detection
                if (empty($extension['version']) || empty($extension['current_version'])) {
                    continue;
                }

                if ($extension['current_version'] != $extension['version']) {
                    $update = MAGEBRIDGE_UPDATE_AVAILABLE;
                    break;
                }
            }
        }

        if ($update == MAGEBRIDGE_UPDATE_NOTAVAILABLE) {
            JError::raiseWarning('MB', JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_EMPTY_VERSION'));
        } else {
            $bar = JToolbar::getInstance('toolbar');
            $bar->appendButton('Standard', 'download', 'Update', 'update', false);
        }

        if ($update == MAGEBRIDGE_UPDATE_AVAILABLE) {
            JError::raiseNotice('UPDATE', JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_NEW_VERSIONS'));
        }

        $this->data = $data;
        $this->update = $update;
        $this->search = $filter_search;

        parent::display($tpl);
    }
}
