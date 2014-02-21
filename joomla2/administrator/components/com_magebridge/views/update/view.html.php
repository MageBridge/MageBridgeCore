<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla! 
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT.'/view.php';

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

    /*
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
        $options = array( 
            array('value' => '', 'text' => '- Extension Type -' ),
            array('value' => 'module', 'text' => 'Modules (all)'),
            array('value' => 'module-site', 'text' => 'Modules (site)'),
            array('value' => 'module-admin', 'text' => 'Modules (admin)'),
            array('value' => 'plugin', 'text' => 'Plugins (all)'),
            array('value' => 'plugin-other', 'text' => 'Plugins (other)'),
            array('value' => 'plugin-magebridgeproduct', 'text' => 'Plugins (product)'),
            array('value' => 'plugin-magebridgestore', 'text' => 'Plugins (store)'),
            array('value' => 'plugin-magebridgeprofile', 'text' => 'Plugins (profile)'),
            array('value' => 'plugin-magebridgenewsletter', 'text' => 'Plugins (newsletter)'),
        );
        $filter_type = $this->getFilter('type');
        $javascript = 'onchange="document.adminForm.submit();"';
        $this->lists['type'] = JHTML::_('select.genericlist', $options, 'filter_type', $javascript, 'value', 'text', $filter_type );

        // Toolbar options
        JToolBarHelper::custom('home', 'back', '', 'LIB_YIREO_VIEW_TOOLBAR_HOME', false);
        JToolBarHelper::custom('refresh', 'preview.png', 'preview_f2.png', 'LIB_YIREO_VIEW_TOOLBAR_REFRESH', false);
        JToolBarHelper::custom('updateQueries', 'archive', '', 'LIB_YIREO_VIEW_TOOLBAR_DBUPGRADE', false);

        // Add jQuery for selection effects
        MageBridgeTemplateHelper::load('jquery');
        $this->addJs('backend-update.js');

        // Load the packages
        $data = MageBridgeUpdateHelper::getData();

        // Add filtering
        if(!empty($filter_type)) {
            $type = explode('-', $filter_type);
            foreach ($data as $index => $extension) {
                if(count($type) == 1) {
                    if($extension['type'] != $type[0]) {
                        unset($data[$index]);
                    }
                } else {
                    if($type[0] == 'module') {
                        if($extension['type'] != 'module') {
                            unset($data[$index]);
                        } elseif($extension['type'] != $type[0] || $extension['app'] != $type[1]) {
                            unset($data[$index]);
                        }
                    } elseif($type[0] == 'plugin') {
                        if($extension['type'] != 'plugin') {
                            unset($data[$index]);
                        } elseif($type[1] == 'other' && in_array($extension['group'], 
                            array('magebridgeproduct', 'magebridgenewsletter', 'magebridgestore', 'magebridgeprofile'))) {
                            unset($data[$index]);
                        } elseif($type[1] != 'other' && $extension['group'] != $type[1]) {
                            unset($data[$index]);
                        }
                    }
                }
            }
        }

        // Detect whether updates are available
        $update = MAGEBRIDGE_UPDATE_NOTAVAILABLE;
        foreach ($data as $index => $extension) {

            // Skip this entry for version detection
            if (empty($extension['version']) || empty($extension['current_version'])) continue;

            if ($extension['current_version'] != $extension['version']) {
                $update = MAGEBRIDGE_UPDATE_AVAILABLE;
                break;
            } else {
                $update = MAGEBRIDGE_UPDATE_NOTNEEDED;
            }
        }

        if ($update == MAGEBRIDGE_UPDATE_NOTAVAILABLE) {
            JError::raiseWarning('MB', JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_EMPTY_VERSION'));
        } else {
            JToolBarHelper::custom( 'update', 'download.png', 'download_f2.png', 'Update', false );
        }

        if ($update == MAGEBRIDGE_UPDATE_AVAILABLE) {
            JError::raiseNotice( 'UPDATE', 'There are new updates available' );
        }

        $this->assignRef('data', $data);
        $this->assignRef('update', $update);
		parent::display($tpl);
	}
}
