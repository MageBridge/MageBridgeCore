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
class MageBridgeViewOffline extends MageBridgeView
{
    /*
     * Method to display the requested view
     */
    public function display($tpl = null)
    {
        $this->assignRef('offline_message', $this->getOfflineMessage());

        parent::display($tpl);
    }
}
