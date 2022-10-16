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

// Include Joomla! libraries
jimport('joomla.filesystem.file');

/**
 * MageBridge Controller
 */
class MageBridgeUpdateHelper
{
    /**
     * Get versioning information of all packages
     *
     * @param null
     * @return array
     */
    public static function getData()
    {
        $data = [];
        foreach (MageBridgeUpdateHelper::getPackageList() as $package) {
            $package['current_version'] = MageBridgeUpdateHelper::getCurrentVersion($package);
            $package['update'] = false;
            $data[] = $package;
        }

        return $data;
    }

    /**
     * Get the version of the MageBridge component
     *
     * @param null
     * @return string
     */
    public static function getComponentVersion()
    {
        static $version = false;
        if ($version == false) {
            $version = MageBridgeUpdateHelper::getCurrentVersion(['type' => 'component', 'name' => 'com_magebridge']);
        }
        return $version;
    }

    /**
     * Get a list of all the packages
     *
     * @param null
     * @return array
     */
    public static function getPackageList()
    {
        $url = 'https://api.yireo.com/';
        $domain = preg_replace('/\:(.*)/', '', $_SERVER['HTTP_HOST']);
        $arguments = [
            'key' => MageBridgeModelConfig::load('supportkey'),
            'domain' => $domain,
            'resource' => 'packages',
            'request' => 'magebridge',
        ];
        foreach ($arguments as $name => $value) {
            $arguments[$name] = "$name,$value";
        }
        $url = $url . implode('/', $arguments);

        $proxy = MageBridgeModelProxy::getInstance();
        $result = $proxy->getRemote($url, null, 'get', false);
        if (empty($result)) {
            return [];
        }

        $packages = json_decode($result, true);
        if (empty($packages) || empty($packages['joomla'])) {
            return [];
        }

        $packages = $packages['joomla'];

        // Load the currently configured connectors
        $db = JFactory::getDbo();
        $query = 'SELECT DISTINCT(`connector`) FROM `#__magebridge_products`';
        $db->setQuery($query);
        $usedConnectorsList = $db->loadObjectList();
        $usedConnectors = [];
        if (!empty($usedConnectorsList)) {
            foreach ($usedConnectorsList as $usedConnector) {
                if (!empty($usedConnector->connector)) {
                    $usedConnectors[] = $usedConnector->connector;
                }
            }
        }

        // Process the postinstall queries
        foreach ($packages as $packageIndex => $package) {
            if ($package['type'] == 'module' && !empty($package['position'])) {
                $package['post_install_query'] = self::getPostInstallQuery('module', $package['name'], $package['position']);
            }

            if ($package['type'] == 'plugin' && !empty($package['enable'])) {
                $package['post_install_query'] = self::getPostInstallQuery('plugin', $package['file'], $package['group']);
            }

            if ($package['type'] == 'plugin' && $package['group'] == 'magebridgeproduct' && in_array($package['file'], $usedConnectors)) {
                $package['post_install_query'] = self::getPostInstallQuery('plugin', $package['file'], $package['group']);
            }

            $packages[$packageIndex] = $package;
        }

        return $packages;
    }

    /**
     * Get the current version of a specific MageBridge extension (component, plugin or module)
     *
     * @param array $package
     * @return string
     */
    public static function getCurrentVersion($package)
    {
        switch($package['type']) {
            case 'component':
                $file = JPATH_ADMINISTRATOR.'/components/'.$package['name'].'/magebridge.xml';
                break;

            case 'module':
                if ($package['app'] == 'admin') {
                    $file = JPATH_ADMINISTRATOR.'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                } else {
                    $file = JPATH_SITE.'/modules/'.$package['name'].'/'.$package['name'].'.xml';
                }
                break;

            case 'plugin':
                $file = JPATH_SITE.'/plugins/'.$package['group'].'/'.$package['file'].'/'.$package['file'].'.xml';
                break;

            case 'template':
                $file = JPATH_SITE.'/templates/'.$package['file'].'/templateDetails.xml';
                break;

            case 'library':
                $libraryName = preg_replace('/^lib_/', '', $package['name']);
                $file = JPATH_SITE.'/libraries/'.$libraryName.'/'.$libraryName.'.xml';
                break;
        }

        if (JFile::exists($file) == false) {
            return false;
        }

        // @todo: Addd a check whether this extension is actually installed (#__extensions)

        $data = JApplicationHelper::parseXMLInstallFile($file);
        return $data['version'];
    }

    /**
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $target
     * @return string
     */
    public static function downloadPackage($url, $target = false)
    {
        $app = JFactory::getApplication();

        // Open the remote server socket for reading
        $proxy = MageBridgeModelProxy::getInstance();
        $data = $proxy->getRemote($url, null, 'get', false);
        if (empty($data)) {
            JError::raiseWarning(42, JText::_('REMOTE_DOWNLOAD_FAILED'));
            return false;
        }

        // Set the target path if not given
        $config = JFactory::getConfig();

        if (!$target) {
            $target = $config->get('tmp_path').'/'.JInstallerHelper::getFilenameFromURL($url);
        } else {
            $target = $config->get('tmp_path').'/'.basename($target);
        }

        // Write received data to file
        file_put_contents($target, $data);

        // Return the name of the downloaded package
        return basename($target);
    }

    /**
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $target
     * @return string
     */
    public static function getPostInstallQuery($type = null, $name = null, $value = null)
    {
        if ($type == 'module') {
            $query = 'UPDATE `#__modules` SET `position`="'.$value.'" WHERE `module`="'.$name.'"';
        } else {
            $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="'.$name.'" AND `folder`="'.$value.'"';
        }

        return $query;
    }
}
