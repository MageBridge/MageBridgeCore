<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Automatically install the library if not available
if (!is_dir(JPATH_SITE . '/libraries/yireo')) {
    $url = 'https://www.yireo.com/documents/lib_yireo_j3x.zip';

    $app = JFactory::getApplication();
    $app->input->set('installtype', 'url');
    $app->input->set('install_url', $url);

    require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php';

    $installer = new InstallerModelInstall();
    $installer->install();
}
