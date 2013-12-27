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

// Include Joomla! libraries
jimport('joomla.filesystem.file');

/**
 * MageBridge Controller
 */
class MageBridgeUpdateHelper
{
    /*
     * Get versioning information of all packages
     *
     * @param null
     * @return array
     */
    static public function getData()
    {
        $data = array();
        foreach (MageBridgeUpdateHelper::getPackageList() as $package) {
            $package['current_version'] = MageBridgeUpdateHelper::getCurrentVersion($package);
            $package['latest_version'] = MageBridgeUpdateHelper::getLatestVersion($package);
            $package['update'] = false;
            $data[] = $package;
        }

        return $data;
    }

    /*
     * Get the version of the MageBridge component
     *
     * @param null
     * @return string
     */
    static public function getComponentVersion()
    {
        $packages = MageBridgeUpdateHelper::getPackageList();
        foreach ($packages as $package) {
            if ($package['type'] == 'component') {
                return MageBridgeUpdateHelper::getCurrentVersion($package);
            }
        }
    }

    /*
     * Get a list of all the packages
     *
     * @param null
     * @return array
     */
    static public function getPackageList()
    {
        $packages = array(
            array( 
                'type' => 'component', 
                'name' => 'com_magebridge', 
                'title' => 'Main component',
                'description' => 'Core functionality of MageBridge',
                'base' => 1,
                'core' => 1,
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_block', 
                'title' => 'Block module',
                'description' => 'Use any Magento block in Joomla!',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_widget', 
                'title' => 'Widget module',
                'description' => 'Use any Magento Widget Instance in Joomla!',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_cart', 
                'title' => 'Cart module',
                'description' => 'Use the Magento cart-block in Joomla!',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_cms', 
                'title' => 'CMS-block module',
                'description' => 'Use any Magento CMS-block in Joomla!',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_login', 
                'title' => 'Login module',
                'description' => 'Replacement of Joomla! login-module',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_menu', 
                'title' => 'Menu module',
                'description' => 'Displays a listing of Magento catalog categories',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_newsletter', 
                'title' => 'Newsletter module',
                'description' => 'Use the Magento newsletter-block in Joomla!',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_products', 
                'title' => 'Products module',
                'description' => 'Display Magento products in multiple styles',
                'core' => 0,
                'base' => 1,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_advertisement', 
                'title' => 'Advertisement module',
                'description' => 'Display single Magento product in advertisement',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_remote', 
                'title' => 'Remote Block module',
                'description' => 'Fetch any Magento block remotely from the Magento frontend through AJAX',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_progress', 
                'title' => 'Checkout Progress module',
                'description' => 'Display progress-block during Magento checkout',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_switcher', 
                'title' => 'Switcher module',
                'description' => 'Allows switching from one Store View to another',
                'core' => 0,
                'base' => 0,
                'app' => 'site',
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_customers', 
                'title' => 'Latest customers module',
                'description' => 'Show a list of latest customers',
                'core' => 0,
                'base' => 0,
                'app' => 'admin',
                'post_install_query' => self::getPostInstallQuery('module', 'mod_magebridge_customers', 'cpanel'),
            ),
            array( 
                'type' => 'module', 
                'name' => 'mod_magebridge_orders', 
                'title' => 'Latest orders module',
                'description' => 'Show a list of latest orders',
                'core' => 0,
                'base' => 0,
                'app' => 'admin',
                'post_install_query' => self::getPostInstallQuery('module', 'mod_magebridge_orders', 'cpanel'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_authentication', 
                'title' => 'Authentication plugin', 
                'description' => 'Authenticate Joomla! users with the Magento database',
                'core' => 1,
                'base' => 1,
                'group' => 'authentication', 
                'file' => 'magebridge',
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'authentication'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_content', 
                'title' => 'Content plugin', 
                'description' => 'Parse Joomla! content through Magento content-filters',
                'core' => 0,
                'base' => 1,
                'group' => 'content', 
                'file' => 'magebridge',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_tags', 
                'title' => 'Content Tags plugin', 
                'description' => 'Adds a block of Magento products to Joomla! articles by looking at their corresponding tags',
                'core' => 0,
                'base' => 0,
                'group' => 'content', 
                'file' => 'magebridgetags',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_magento', 
                'title' => 'Magento plugin', 
                'description' => 'Handles various Magento events in Joomla!',
                'core' => 0,
                'base' => 1,
                'group' => 'magento', 
                'file' => 'magebridge', 
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'magento'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_magebridge', 
                'title' => 'MageBridge plugin', 
                'description' => 'Handles various MageBridge core-events in Joomla!',
                'core' => 0,
                'base' => 1,
                'group' => 'magebridge', 
                'file' => 'magebridge', 
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'magebridge'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_search', 
                'title' => 'Search plugin', 
                'description' => 'Search for Magento products using Joomla! search',
                'core' => 0,
                'base' => 1,
                'group' => 'search', 
                'file' => 'magebridge',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_system', 
                'title' => 'System plugin', 
                'description' => 'Provides core functionality',
                'core' => 1,
                'base' => 1,
                'group' => 'system', 
                'file' => 'magebridge',
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'system'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_user', 
                'title' => 'User plugin', 
                'description' => 'Provides core functionality',
                'core' => 1,
                'base' => 1,
                'group' => 'user', 
                'file' => 'magebridge',
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'user'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_pre', 
                'title' => 'Pre-loader plugin', 
                'description' => 'MageBridge pre-loader',
                'core' => 1,
                'base' => 1,
                'group' => 'system', 
                'file' => 'magebridgepre',
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridgepre', 'system'),
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_yoo', 
                'title' => 'YOOtheme plugin', 
                'description' => 'Adds tricks for YOOtheme templates',
                'core' => 0,
                'base' => 0,
                'group' => 'system', 
                'file' => 'magebridgeyoo',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_zoo', 
                'title' => 'ZOO plugin', 
                'description' => 'Adds MageBridge parsing in ZOO items',
                'core' => 0,
                'base' => 0,
                'group' => 'system', 
                'file' => 'magebridgezoo',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_rt', 
                'title' => 'RocketTheme plugin', 
                'description' => 'Adds tricks for RocketTheme templates',
                'core' => 0,
                'base' => 0,
                'group' => 'system', 
                'file' => 'magebridgert',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_t3', 
                'title' => 'T3 plugin', 
                'description' => 'Adds tricks for JoomlArt T3 templates',
                'core' => 0,
                'base' => 0,
                'group' => 'system', 
                'file' => 'magebridget3',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_community', 
                'title' => 'JomSocial plugin', 
                'description' => 'Integrates with the JomSocial component',
                'core' => 0,
                'base' => 0,
                'group' => 'community', 
                'file' => 'magebridge',
            ),
            array( 
                'type' => 'plugin', 
                'name' => 'plg_magebridge_finder', 
                'title' => 'SmartSearch plugin', 
                'description' => 'Finder-plugin for Magento products',
                'core' => 0,
                'base' => 1,
                'group' => 'finder', 
                'file' => 'magebridge',
                'post_install_query' => self::getPostInstallQuery('plugin', 'magebridge', 'finder'),
            ),
            array( 
                'type' => 'template', 
                'name' => 'tpl_magebridge_root', 
                'title' => 'Root Block template', 
                'description' => 'Experimental Joomla! template',
                'core' => 0,
                'base' => 0,
                'file' => 'magebridge_root',
            ),
        );

        $productPlugins = array(
            array('name' => 'acajoom', 'title' => 'Acajoom'),
            array('name' => 'acctexp', 'title' => 'AEC Membership'),
            array('name' => 'acymailing', 'title' => 'Acymailing'),
            array('name' => 'agora', 'title' => 'Agora'),
            array('name' => 'akeebasubs', 'title' => 'Akeeba Subscriptions'),
            array('name' => 'alphauserpoints', 'title' => 'Alpha User Points'),
            array('name' => 'article', 'title' => 'Joomla! Articles'),
            array('name' => 'ccnewsletter', 'title' => 'ccNewsletter'),
            array('name' => 'communicator', 'title' => 'Communicator'),
            array('name' => 'docman_group', 'title' => 'DOCman Groups'),
            array('name' => 'eventlist', 'title' => 'EventList'),
            array('name' => 'flexiaccess', 'title' => 'FLEXIaccess'),
            array('name' => 'jdownloads', 'title' => 'jDownloads'),
            array('name' => 'jinc', 'title' => 'JINC'),
            array('name' => 'jnews', 'title' => 'jNews'),
            array('name' => 'jnewsletter', 'title' => 'jNewsletter'),
            array('name' => 'jomsocial_group', 'title' => 'JomSocial Groups'),
            array('name' => 'jomsocial_userpoints', 'title' => 'JomSocial User Points'),
            array('name' => 'kunena_ranks', 'title' => 'Kunena Ranks'),
            array('name' => 'mkpostman', 'title' => 'MkPostman'),
            array('name' => 'ohanah', 'title' => 'Ohanah'),
            array('name' => 'osemsc', 'title' => 'OSE MSC legacy'),
            array('name' => 'osemsc4', 'title' => 'OSE MSC v4'),
            array('name' => 'osemsc5', 'title' => 'OSE MSC v5'),
            array('name' => 'rsevents', 'title' => 'RsEvents'),
            array('name' => 'rseventspro', 'title' => 'RsEvents Pro'),
            array('name' => 'rsfiles', 'title' => 'RsFiles'),
            array('name' => 'usergroup', 'title' => 'Joomla! Usergroups'),
        );

        // Load the currently configured connectors
        $db = JFactory::getDBO();
        $query = 'SELECT DISTINCT(`connector`) FROM `#__magebridge_products`';
        $db->setQuery($query);
        $usedConnectorsList = $db->loadObjectList();
        $usedConnectors = array();
        if(!empty($usedConnectorsList)) {
            foreach($usedConnectorsList as $usedConnector) {
                if(!empty($usedConnector->connector)) {
                    $usedConnectors[] = $usedConnector->connector;
                }
            }
        }

        // Loop through the defined product-plugins, and add them to the packages
        foreach($productPlugins as $productPlugin) {
            $package = array(    
                'type' => 'plugin',
                'name' => 'plg_magebridge_product_'.$productPlugin['name'],
                'title' => $productPlugin['title'].' Product Plugin',
                'description' => 'Product Plugin for '.$productPlugin['title'],
                'core' => 1,
                'base' => 1,
                'group' => 'magebridgeproduct', 
                'file' => $productPlugin['name'],
            );

            if(in_array($productPlugin['name'], $usedConnectors)) {
                $package['post_install_query'] = self::getPostInstallQuery('plugin', $productPlugin['name'], 'magebridgeproduct');
            }

            $packages[] = $package;
        }

        return $packages;
    }

    /*
     * Get the current version of a specific MageBridge extension (component, plugin or module)
     *
     * @param array $package
     * @return string
     */
    static public function getCurrentVersion($package) 
    {
        if ($package)

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
        }

        if (JFile::exists($file) == false) {
            return false;
        }

        // @todo: Addd a check whether this extension is actually installed (#__extensions)

        $data = JApplicationHelper::parseXMLInstallFile($file);
        return $data['version'];
    }

    /*
     * Get the latest available version from the Yireo API-site
     *
     * @param array $package
     * @return string
     */
    static public function getLatestVersion($package)
    {
        static $init = null;
        static $data = null;

        if (empty($init)) {

            $init = true;
            $url = 'http://api.yireo.com/';
            $domain = preg_replace( '/\:(.*)/', '', $_SERVER['HTTP_HOST'] );
            $arguments = array(
                'key' => MagebridgeModelConfig::load('supportkey'),
                'domain' => $domain,
                'resource' => 'versions',
                'request' => 'downloads/magebridge',
            );
            foreach ($arguments as $name => $value) {
                $arguments[$name] = "$name,$value";
            }
            $url = $url . implode('/', $arguments);

            $proxy = MageBridgeModelProxy::getInstance();
            $result = $proxy->getRemote( $url, null, 'get', false );

            if (empty($result)) {
                JError::raiseWarning( 500, 'Empty version-check. Is your licensing correctly configured?');
                return false;
            }

            $data = @simplexml_load_string($result);
            if (!is_object($data)) {
                JError::raiseWarning( 500, 'Version information could not be loaded. Is your licensing correctly configured?');
                return false;
            }
        }

        if (!empty($data)) {
            return (string)$data->joomla;
        }
        return false;   
    }

    /*
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $target
     * @return string
     */
    static public function downloadPackage($url, $target = false)
    {
        $app = JFactory::getApplication();

        // Open the remote server socket for reading
        $proxy = MageBridgeModelProxy::getInstance();
        $data = $proxy->getRemote( $url, null, 'get', false );
        if (empty($data)) {
            JError::raiseWarning(42, JText::_('REMOTE_DOWNLOAD_FAILED').', '.$error);
            return false;
        }

        // Set the target path if not given
        if (!$target) {
            $target = $app->getCfg('tmp_path').'/'.JInstallerHelper::getFilenameFromURL($url);
        } else {
            $target = $app->getCfg('tmp_path').'/'.basename($target);
        }

        // Write received data to file
        JFile::write($target, $data);

        // Return the name of the downloaded package
        return basename($target);
    }

    /*
     * Download a specific package using the MageBridge Proxy (CURL-based)
     *
     * @param string $url
     * @param string $target
     * @return string
     */
    static public function getPostInstallQuery($type = null, $name = null, $value = null)
    {
        if ($type == 'module') {
            $query = 'UPDATE `#__modules` SET `position`="'.$value.'" WHERE `module`="'.$name.'"';

        } else {
            $query = 'UPDATE `#__extensions` SET `enabled`="1" WHERE `type`="plugin" AND `element`="'.$name.'" AND `folder`="'.$value.'"';
        }

        return $query;
    }
}
