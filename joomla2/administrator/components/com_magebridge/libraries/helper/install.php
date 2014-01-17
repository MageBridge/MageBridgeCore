<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include libraries
require_once dirname(dirname(__FILE__)).'/loader.php';

/** 
 * Yireo Install Helper
 */
class YireoHelperInstall
{
    static public function remove($files = array())
    {
        if(empty($files)) $files = YireoHelper::getData('obsolete_files');

        if(!empty($files)) {
            foreach($files as $file) {
                if(file_exists($file)) {
                    jimport('joomla.filesystem.file'); 
                    JFile::delete($file);
                }
            }
        }
    }

    static public function installExtension($url, $label)
    {
        // Include Joomla! libraries
        jimport( 'joomla.installer.installer' );
        jimport('joomla.installer.helper');

        // System variables
        $app = JFactory::getApplication();

        // Download the package-file
        $package_file = self::downloadPackage($url);

        // Simple check for the result
        if ($package_file == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_EMPTY', $url));
            return false;
        }

        // Check if the downloaded file exists
        $tmp_path = JFactory::getApplication()->getCfg('tmp_path');
        $package_path = $tmp_path.'/'.$package_file;
        if (!is_file($package_path)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_EXIST', $package_path));
            return false;
        }

        // Check if the file is readable
        if (!is_readable($package_path)) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_FILE_NOT_READABLE', $package_path));
            return false;
        }

        // Now we assume this is an archive, so let's unpack it
        $package = JInstallerHelper::unpack($package_path);
        if ($package == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_DOWNLOAD_NO_ARCHIVE', $extension['name']));
            return false;
        }

        // Call the actual installer to install the package
        $installer = JInstaller::getInstance();
        if ($installer->install($package['dir']) == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_FAIL', $extension['name']));
            return false;
        }

        // Get the name of downloaded package
        if (!is_file($package['packagefile'])) {
            $package['packagefile'] = JFactory::getApplication()->getCfg('tmp_path').'/'.$package['packagefile'];
        }

        // Clean up the installation
        @JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_EXTENSION_SUCCESS', $label));
        return true;
    }

    /*
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $file
     * @return string
     */
    static public function downloadPackage($url, $file = null)
    {
        // System variables
        $app = JFactory::getApplication();

        // Use fopen() instead
        if (ini_get('allow_url_fopen') == 1) {
            return JInstallerHelper::downloadPackage($url, $file);
        }

        // Set the target path if not given
        if (empty($file)) {
            $file = $app->getCfg('tmp_path').'/'.JInstallerHelper::getFilenameFromURL($url);
        } else {
            $file = $app->getCfg('tmp_path').'/'.basename($file);
        }

        // Open the remote server socket for reading
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FRESH_CONNECT => false,
            CURLOPT_FORBID_REUSE => false,
            CURLOPT_BUFFERSIZE => 8192,
        ));
        $data = curl_exec($ch);
        curl_close($ch);

        if (empty($data)) {
            JError::raiseWarning(42, JText::_('LIB_YIREO_HELPER_INSTALL_REMOTE_DOWNLOAD_FAILED').', '.$error);
            return false;
        }

        // Write received data to file
        JFile::write($file, $data);

        // Return the name of the downloaded package
        return basename($file);
    }

    static public function hasPluginInstalled($plugin, $group)
    {
        if(file_exists(JPATH_SITE.'/plugins/'.$group.'/'.$plugin.'/'.$plugin.'.xml')) {
            $query = 'SELECT `name` FROM `#__extensions` WHERE `type`="plugin" AND `element`="'.$plugin.'" AND `folder`="'.$group.'"';
            $db = JFactory::getDBO();
            $db->setQuery($query);
            return (bool)$db->loadObject();
        }
        return false;
    }

    static public function hasPluginEnabled($plugin, $group)
    {
        $query = 'SELECT `enabled` FROM `#__extensions` WHERE `type`="plugin" AND `element`="'.$plugin.'" AND `folder`="'.$group.'"';
        $db = JFactory::getDBO();
        $db->setQuery($query);
        return (bool)$db->loadResult();
    }

    static public function enablePlugin($plugin, $group, $label)
    {
        if(self::hasPluginInstalled($plugin, $group) == false) {
            return false;
        } elseif(self::hasPluginEnabled($plugin, $group) == true) {
            return true;
        }

        $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="'.$plugin.'" AND `folder`="'.$group.'"';
        $db = JFactory::getDBO();
        $db->setQuery($query);

        try {
            $db->query();
            JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_SUCCESS', $label));
        } catch(Exception $e) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_ENABLE_PLUGIN_FAIL', $label));
        }

        return true;
    }
    
    static public function autoInstallEnablePlugin($plugin, $group, $url, $label)
    {
        // If the plugin is already installed, enable it
        if(self::hasPluginInstalled($plugin, $group)) {
            self::enablePlugin($plugin, $group, $label);

        // Otherwise first, try to install the plugin
        } else {
            if(self::installExtension($url, $label)) {
                self::enablePlugin($plugin, $group, $label);
            } else {
                JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('LIB_YIREO_HELPER_INSTALL_MISSING', $label));
            }
        }
    }
}
