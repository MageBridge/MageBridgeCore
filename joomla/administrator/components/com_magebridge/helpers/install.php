<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
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
	/** 
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
			"ALTER TABLE `#__magebridge_log` ADD `session` VARCHAR( 50 ) NOT NULL AFTER  `http_agent`",
			"ALTER TABLE `#__magebridge_urls` DROP INDEX published",
			"ALTER TABLE `#__magebridge_products` ADD `actions` TEXT NOT NULL AFTER `connector_value`",
			"ALTER TABLE `#__magebridge_stores` ADD `actions` TEXT NOT NULL AFTER `connector_value`",
			"DELETE FROM `#__magebridge_connectors`",
			"ALTER TABLE `#__magebridge_usergroups` ADD `label` VARCHAR( 255 ) NOT NULL AFTER  `id`",
		);

		// Perform the update queries
		$db = JFactory::getDBO();
		foreach ($update_queries as $query) {
			$query = trim($query);
			if(empty($query)) continue;
			$db->setQuery($query);
			try {
				$db->execute();
			} catch(Exception $e) {
			}
		}
	}
}
