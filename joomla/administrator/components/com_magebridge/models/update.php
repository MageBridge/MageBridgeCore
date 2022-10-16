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
defined('_JEXEC') or die('Restricted access');

// Include Joomla! libraries
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

/**
 * MageBridge Update model
 */
class MagebridgeModelUpdate extends YireoCommonModel
{
    /**
     * Method to upgrade all registered packages at once
     *
     * @param array $allow_update
     *
     * @return bool
     */
    public function updateAll($allow_update = [])
    {
        // Fetch all the available packages
        $packages = MageBridgeUpdateHelper::getPackageList();
        $count    = 0;

        foreach ($packages as $package) {
            // Skip optional packages which are not yet installed and not selected in the list
            if (!in_array($package['name'], $allow_update)) {
                continue;
            }

            // Skip packages that are not available
            if ($package['available'] == 0) {
                continue;
            }

            // Update the package and add an error if something goes wrong
            if ($this->update($package['name']) == false) {
                JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FAILED', $package['name']));

                // Only crash when installing the component, continue for all other extensions
                if ($package['name'] == 'com_magebridge') {
                    return false;
                }

                continue;
            } else {
                $count++;
            }
        }

        // Run the helper to remove obsolete files
        YireoHelperInstall::remove();

        // Simple notices as feedback
        JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_SUCCESS', $count));
        JError::raiseNotice('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_CHANGELOG', MageBridgeHelper::getHelpLink('changelog')));

        return true;
    }

    /**
     * Method to upgrade a specific extension
     *
     * @param string $exension_name
     *
     * @return bool
     */
    private function update($extension_name = null)
    {
        // Do not continue if the extension name is empty
        if ($extension_name == null) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_NO_EXTENSION'));

            return false;
        }

        // Fetch a list of available packages
        $packages  = MageBridgeUpdateHelper::getPackageList();
        $extension = false;

        foreach ($packages as $package) {
            if ($package['name'] == $extension_name) {
                $extension = $package;
                break;
            }
        }

        // Do not continue if the extension does not appear from the list
        if ($extension === false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_UNKNOWN_EXTENSION'));

            return false;
        }

        // Premature check for the component-directory to be writable
        $config = JFactory::getConfig();

        if ($extension['type'] == 'component' && $config->get('ftp_enable') == 0) {
            if (is_dir(JPATH_ADMINISTRATOR . '/components/' . $extension['name']) && !is_writable(JPATH_ADMINISTRATOR . '/components/' . $extension['name'])) {
                JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_DIR_NOT_WRITABLE', JPATH_ADMINISTRATOR . '/components/' . $extension['name']));

                return false;
            } else {
                if (!is_dir(JPATH_ADMINISTRATOR . '/components/' . $extension['name']) && !is_writable(JPATH_ADMINISTRATOR . '/components')) {
                    JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_DIR_NOT_WRITABLE', JPATH_ADMINISTRATOR . '/components'));

                    return false;
                }
            }
        }

        // Construct the update URL
        $extension_uri = $extension['name'];
        $extension_uri .= '_j25';
        $extension_uri .= '.' . MageBridgeModelConfig::load('update_format');

        if (!empty($extension['download_url'])) {
            $extension_url = $extension['download_url'];
            $extension_uri = basename($extension['download_url']);
        } else {
            $extension_url = $this->getUrl($extension_uri);
        }

        // Either use fopen() or CURL
        if (ini_get('allow_url_fopen') == 1 && MageBridgeModelConfig::load('update_method') == 'joomla') {
            $package_file = JInstallerHelper::downloadPackage($extension_url, $extension_uri);
        } else {
            $package_file = MageBridgeUpdateHelper::downloadPackage($extension_url, $extension_uri);
        }

        // Simple check for the result
        if ($package_file == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_DOWNLOAD_FAILED', $extension_uri));

            return false;
        }

        // Check if the downloaded file exists
        $tmp_path     = $config->get('tmp_path');
        $package_path = $tmp_path . '/' . $package_file;
        if (!is_file($package_path)) {
            JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FILE_NOT_EXISTS', $package_path));

            return false;
        }

        // Check if the file is readable
        if (!is_readable($package_path)) {
            JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FILE_NOT_READABLE', $package_path));

            return false;
        }

        // Check if the downloaded file is abnormally small (so it might just contain a simple warning-text)
        if (filesize($package_path) < 128) {
            $contents = @file_get_contents($package_path);
            if (empty($contents)) {
                JError::raiseWarning('MB', JText::_('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FILE_EMPTY'));

                return false;
            } else {
                if (preg_match('/^Restricted/', $contents)) {
                    JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_NO_ACCESS'));

                    return false;
                }
            }

            JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FILE_NO_ARCHIVE', $package_path));

            return false;
        }

        // Now we assume this is an archive, so let's unpack it
        $package = JInstallerHelper::unpack($package_path);
        if ($package == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FILE_GONE', $extension['name']));

            return false;
        }

        // Quick workaround to prevent Koowa proxying the database
        if (class_exists('KInput')) {
            KInput::set('option', 'com_installer', 'get');
        }

        // Call the actual installer to install the package
        $installer = JInstaller::getInstance();
        if ($installer->install($package['dir']) == false) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_FAILED', $extension['name']));

            return false;
        }

        // Get the name of downloaded package
        if (!is_file($package['packagefile'])) {
            $package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
        }

        // Clean up the installation
        @JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

        // Post install procedure
        if (isset($extension['post_install_query'])) {
            $query = trim($extension['post_install_query']);
            if (!empty($query)) {
                $db = JFactory::getDbo();
                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (Exception $e) {
                    JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_POSTQUERY_FAILED', $db->getErrorMsg()));

                    return false;
                }

                if ($db->getErrorMsg()) {
                    JError::raiseWarning('MB', JText::sprintf('COM_MAGEBRIDGE_MODEL_UPDATE_INSTALL_POSTQUERY_FAILED', $db->getErrorMsg()));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Method to get the download-URL for a specific extension
     *
     * @param string $extension_name
     *
     * @return string
     */
    private function getUrl($extension_name)
    {
        // Base URL
        $url = 'http://api.yireo.com/';

        // Build the arguments
        $arguments = [
            'key'      => MageBridgeModelConfig::load('supportkey'),
            'domain'   => $_SERVER['HTTP_HOST'],
            'resource' => 'download',
            'request'  => $extension_name,
        ];

        // Append the arguments to the URL
        foreach ($arguments as $name => $value) {
            $arguments[$name] = "$name,$value";
        }

        return $url . implode('/', $arguments);
    }
}
