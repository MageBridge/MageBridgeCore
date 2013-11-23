<?php
/*
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

/*
 * Method run when installing MageBridge
 */
function com_install() 
{
    require_once(dirname(__FILE__).'/helpers/install.php');
    $helper = new MageBridgeInstallHelper();

    // Initialize important variables
    $application = JFactory::getApplication();
    $db = JFactory::getDBO();

    // Remove obsolete files
    //$helper->cleanFiles();

    // Upgrade the database tables
    $helper->updateQueries();

    // Install new connectors
    $helper->installConnectors();

    // Done
    return true;
}
