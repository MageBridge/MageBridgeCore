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

/**
 * MageBridge Install Helper
 */
class MageBridgeInstallHelper
{
    /* 
     * Method to remove obsolete files
     *
     * @param null
     * @return null
     */
    public function cleanFiles()
    {
        // List of obsolete folders
        $obsolete_folders = array(
            JPATH_ADMINISTRATOR.'/components/com_magebridge/css',
            JPATH_ADMINISTRATOR.'/components/com_magebridge/lib',
            JPATH_ADMINISTRATOR.'/components/com_magebridge/images',
            JPATH_ADMINISTRATOR.'/components/com_magebridge/js',
            JPATH_SITE.'/components/com_magebridge/lib',
            JPATH_SITE.'/components/com_magebridge/css',
            JPATH_SITE.'/components/com_magebridge/images',
            JPATH_SITE.'/components/com_magebridge/js',
        );

        // Remove obsolete folders
        foreach ($obsolete_folders as $folder) {
            if (@is_dir($folder)) JFolder::delete($folder);
        }

        // List of obsolete files
        $obsolete_files = array(
            JPATH_ADMINISTRATOR.'/components/com_magebridge/views/config/tmpl/joomla25/field.php',
        );

        // Remove obsolete files
        foreach ($obsolete_files as $file) {
            if (@is_file($file)) JFile::delete($file);
        }
    }

    /* 
     * Method to remove obsolete files
     *
     * @param null
     * @return null
     */
    public function updateQueries()
    {
        $update_queries = array(
            "ALTER TABLE `#__magebridge_config` CHANGE `value` `value` TEXT NOT NULL DEFAULT ''",
            "ALTER TABLE `#__magebridge_stores` ADD `label` VARCHAR( 255 ) NOT NULL AFTER `id`",
            "ALTER TABLE `#__magebridge_stores` CHANGE `condition` `connector_value` VARCHAR( 255 ) NOT NULL",
            "ALTER TABLE `#__magebridge_urls` ADD `source_type` TINYINT( 2 ) NOT NULL AFTER `source`",
            "UPDATE `#__magebridge_config` SET `name`='api_key' WHERE `name`='api_password'",
            "UPDATE `#__magebridge_config` SET `name` = 'disable_css_mage' WHERE `name` = 'disable_css'",
            "UPDATE `#__magebridge_config` SET `name` = 'disable_default_css', `value` = '1' WHERE `name` = 'enable_default_css'",
            "UPDATE `#__magebridge_config` SET `name` = 'supportkey' WHERE `name` = 'license'",
            "UPDATE `#__plugins` SET `ordering`='99' WHERE `element`='magebridge' AND `folder`='user'",
            "DELETE FROM `#__magebridge_config` WHERE `name`=''",
            "DELETE FROM `#__magebridge_connectors` WHERE `published`='0'",
            "ALTER TABLE `#__magebridge_log` ADD `session` VARCHAR( 50 ) NOT NULL AFTER  `http_agent`",
            "ALTER TABLE `#__magebridge_urls` ADD UNIQUE `published` ( `published`)",
            "ALTER TABLE `#__magebridge_products` ADD `actions` TEXT NOT NULL AFTER `connector_value`",
        );

        // Perform the update queries
        $db = JFactory::getDBO();
        foreach ($update_queries as $query) {
            $query = trim($query);
            if(empty($query)) continue;
            $db->setQuery($query);
            try {
                $db->query();
            } catch(Exception $e) {
            }
        }
    }

    /* 
     * Method to install new connectors
     *
     * @param null
     * @return null
     */
    public function installConnectors()
    {
        $db = JFactory::getDBO();

        // Get the list of currently installed connectors
        $installed = array();
        $db->setQuery("SELECT * FROM `#__magebridge_connectors` WHERE 1=1");
        $rows = $db->loadObjectList();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $tag = $row->name.'-'.$row->type;
                $installed[] = $tag;
            }
        }

        // Loop through the new connectors
        $connectors = $this->getAvailableConnectors();
        foreach ($connectors as $c) {

            // Skip it if the connector is already in the database
            $tag = $c[1].'-'.$c[2];
            if (in_array($tag, $installed)) continue;

            // Insert the query
            $query = "INSERT INTO `#__magebridge_connectors` (`title`, `name`, `type`, `filename`, `published`, `iscore`, `params`) "
                . " VALUES ('".$c[0]."', '".$c[1]."', '".$c[2]."', '".$c[3]."', 0, 1, '".$c[4]."')";
            $db->setQuery($query);
            $db->query();
        }

        // Done
        return true;
    }

    /* 
     * Method to install new connectors
     *
     * @param null
     * @return null
     */
    public function getAvailableConnectors()
    {
        // JomSocial params
        $jomsocial_fields = array(
            'FIELD_MOBILE' => 'mobile',
            'FIELD_LANDPHONE' => 'phone',
            'FIELD_ADDRESS' => 'street',
            'FIELD_STATE' => 'state',
            'FIELD_CITY' => 'city',
            'FIELD_COUNTRY' => 'country',
        );
        $jomsocial_params = 'fields=';
        foreach ($jomsocial_fields as $name => $value) {
            $jomsocial_params .= "$name=$value\\n";
        }

        // Available connectors
        $connectors = array(
            array('Special Days', 'days', 'store', 'days.php', null),
            array('URL Input', 'get', 'store', 'get.php', null),
            array('Joomla! User Group', 'usergroup', 'store', 'usergroup.php', null),
            array('Nooku MultiLingual', 'nooku', 'store', 'nooku.php', null),
            array('JoomFish MultiLingual', 'joomfish', 'store', 'joomfish.php', null),
            array('Falang MultiLingual', 'falang', 'store', 'falang.php', null),
            array('M17n MultiLingual', 'm17n', 'store', 'm17n.php', null),
            array('Domain Name', 'domain', 'store', 'domain.php', null),
            array('Joomla! MultiLingual', 'joomla', 'store', 'joomla.php', null),

            array('Joomla! Profile', 'joomla', 'profile', 'joomla.php', null),
            array('JomSocial Profile', 'jomsocial', 'profile', 'jomsocial.php', $jomsocial_params),
            array('Community Builder Profile', 'cb', 'profile', 'cb.php', null),
        );

        return $connectors;
    }
}
