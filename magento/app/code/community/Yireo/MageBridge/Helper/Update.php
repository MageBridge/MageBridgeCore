<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

class Yireo_MageBridge_Helper_Update extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to remove obsolete files
     *
     * @access public
     * @param null
     * @return bool
     */
    public function removeFiles()
    {
        // Cleanup specific files
        $files = [
            BP.DS.'app'.DS.'etc'.DS.'modules'.DS.'Jira_MageBridge.xml',
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'default'.DS.'layout'.DS.'magebridge.xml',
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'magebridge'.DS.'layout',
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'magebridge'.DS.'template'.DS.'magebridge'.DS.'page.phtml',
            BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'MageBridge'.DS.'controllers'.DS.'IndexController.php',
        ];

        foreach ($files as $file) {
            @unlink($file);
        }

        // Cleanup Magento Downloader left-overs
        $packageFolder = BP.DS.'var'.DS.'package'.DS;
        $downloaderFolder = BP.DS.'downloader'.DS;
        $files = scandir($packageFolder);
        $fileMatch = false;
        foreach ($files as $file) {
            if (preg_match('/^Yireo_MageBridge/', $file)) {
                $fileMatch = true;
                @unlink($packageFolder.$file);
            }
        }

        // If a file has been removed, refresh the Magento Downloader
        if ($fileMatch == true) {
            if (file_exists($downloaderFolder.'cache.cfg')) {
                @unlink($downloaderFolder.'cache.cfg');
            }
            if (file_exists($downloaderFolder.'connect.cfg')) {
                @unlink($downloaderFolder.'connect.cfg');
            }
        }
    }

    /*
     * Helper-method to remove a directory recursively
     *
     * @access public
     * @param string $directory
     * @return bool
     */
    public function recursiveDelete($directory)
    {
        $pointer = opendir($directory);
        if ($pointer) {
            while ($f = readdir($pointer)) {
                $file = $directory.DS.$f;
                if ($f == '.' || $f == '..') {
                    continue;
                } elseif (is_dir($file) && !is_link($file)) {
                    self::recursiveDelete($file);
                } else {
                    @unlink($file);
                }
            }
            closedir($pointer);
            @rmdir($directory);
        }
    }

    /*
     * Helper-method to rename obsolete sections to their new variant
     *
     * @access public
     * @param null
     * @return bool
     */
    public function renameConfigPaths()
    {
        $paths = [
            'settings/caching' => 'cache/caching',
            'settings/caching_gzip' => 'cache/caching_gzip',
            'settings/joomla_remotesso' => 'joomla/remotesso',
            'settings/joomla_auth' => 'joomla/auth',
            'settings/joomla_map' => 'joomla/map',
            'settings/api_detect' => 'joomla/api_detect',
            'settings/api_url' => 'joomla/api_url',
            'settings/api_user' => 'joomla/api_user',
            'settings/api_key' => 'joomla/api_key',
            'settings/debug_print' => 'debug/print',
            'settings/debug_log' => 'debug/log',
            'settings/encryption' => 'joomla/encryption',
            'settings/encryption_key' => 'joomla/encryption_key',
            'settings/license_key' => 'hidden/support_key',
        ];

        foreach ($paths as $originalPath => $newPath) {
            $this->renameConfigPath($originalPath, $newPath);
        }
    }

    /*
     * Helper-method to copy one configuration value to another path
     *
     * @access public
     * @param string $originalPath
     * @param string $newPath
     * @return bool
     */
    public function renameConfigPath($originalPath, $newPath)
    {
        $originalPath = 'magebridge/'.$originalPath;
        $newPath = 'magebridge/'.$newPath;

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('core/config_data');

        $query = 'SELECT * FROM `'.$table.'` WHERE `path` = "'.$originalPath.'"';
        $newPathResults = $connection->fetchAll($query);

        $query = 'SELECT * FROM `'.$table.'` WHERE `path` = "'.$newPath.'"';
        $originalPathResults = $connection->fetchAll($query);

        if (empty($newPathResults) && !empty($originalPathResults)) {
            $query = 'UPDATE `'.$table.'` SET `path`="'.$newPath.'" WHERE `path` = "'.$originalPath.'"';
            $connection->query($query);
        } elseif (!empty($newPathResults) && !empty($originalPathResults)) {
            $query = 'DELETE FROM `'.$table.'` WHERE `path` = "'.$originalPath.'"';
            $connection->query($query);
        }
    }
}
