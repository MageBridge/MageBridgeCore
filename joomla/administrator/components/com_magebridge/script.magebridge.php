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
 * Class run when installing/upgrading/removing MageBridge
 */
if(class_exists('com_magebridgeInstallerScript') == false) {
	class com_magebridgeInstallerScript
	{
		/**
		 * Postflight method 
		 */ 
		public function postflight($action, $installer)
		{
			switch($action) {
				case 'install':
					return self::doInstall();

				case 'uninstall':
					return self::doUninstall();

				case 'update':
					return self::doUpdate();
			}
		}

		/**
		 * Method run when updating MageBridge
		 */ 
		public function doUpdate()
		{
			// Initialize important variables
			$application = JFactory::getApplication();
			$db = JFactory::getDBO();

			// Perform
			$sql = dirname(__FILE__).'/administrator/components/com_magebridge/sql/install.mysql.utf8.sql';
			if (is_file($sql)) {
				$sqlcontent = file_get_contents($sql);
				$queries = $db->splitSql($sqlcontent);

				if (!empty($queries)) {
					foreach ($queries as $query) {
						$query = trim($query);  
						if (!empty($query)) {
							$db->setQuery($query);
							try {
								$db->execute();
							} catch(Exception $e) {}
						}
					}
				}
			}

			// Continue with the normal installation process
			return self::doInstall();
		}

		/**
		 * Method run when installing MageBridge
		 */ 
		public function doInstall()
		{
			// Try to include the file
			$file = 'administrator/components/com_magebridge/helpers/install.php';
			if (is_file(JPATH_ROOT.'/'.$file)) {
				require_once JPATH_ROOT.'/'.$file;
			} else if (is_file(dirname(__FILE__).'/'.$file)) {
				require_once dirname(__FILE__).'/'.$file;
			} else {
				return true;
			}

			// Check for PHP version
			if (version_compare(PHP_VERSION, '5.4.0', '<')) {
				return false;
			}

			// Check for Joomla version
			JLoader::import( 'joomla.version' );
			$jversion = new JVersion();
			if (version_compare($jversion->RELEASE, '3.0.0', '<')) {
				return false;
			}

			// Done
			return true;
		}

		/**
		 * Method run when uninstalling MageBridge
		 */ 
		public function doUninstall() 
		{
			// Initialize the Joomla! installer
			jimport('joomla.installer.installer');
			$installer = JInstaller::getInstance();

			// Select all MageBridge modules and remove them
			$db = JFactory::getDBO();
			$query = "SELECT `id`,`client_id` FROM #__modules WHERE `module` LIKE 'mod_magebridge%'";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$installer->uninstall('module', $row->id, $row->client_id);
				}
			} 

			// Select all MageBridge plugins and remove them
			$db = JFactory::getDBO();
			$query = "SELECT `id`,`client_id` FROM #__plugins WHERE `element` LIKE 'magebridge%' OR `folder` = 'magento'";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$installer->uninstall('plugin', $row->id, $row->client_id);
				}
			} 

			// Done
			return true;
		}
	}
}
