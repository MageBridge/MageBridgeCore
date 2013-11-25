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
        MageBridgeViewHelper::initialize('Updates');

        // Toolbar options
        JToolBarHelper::custom('home', 'back', '', 'Home', false);
        JToolBarHelper::custom('refresh', 'preview.png', 'preview_f2.png', 'Refresh', false);
        JToolBarHelper::custom('updateQueries', 'archive', '', 'DB Upgrade', false);

        // Add jQuery for selection effects
        MageBridgeTemplateHelper::load('jquery');
        $this->addJs('backend-update.js');

        $update = MAGEBRIDGE_UPDATE_NOTAVAILABLE;
        $component_version = null;

        $data = MageBridgeUpdateHelper::getData();
        foreach ( $data as $index => $extension ) {

            if (empty($extension['latest_version']) || empty($extension['current_version'])) {
                continue;
            }

            if ($extension['current_version'] != $extension['latest_version']) {
                $update = MAGEBRIDGE_UPDATE_AVAILABLE;
                break;
            } else {
                $update = MAGEBRIDGE_UPDATE_NOTNEEDED;
            }
                    
            $data[$index] = $extension;
        }

        if ($update != MAGEBRIDGE_UPDATE_NOTAVAILABLE) {
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
