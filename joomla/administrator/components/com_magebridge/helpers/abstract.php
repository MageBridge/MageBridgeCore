<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Structure
 */
class HelperAbstract
{
	/**
	 * Structural data of this component
	 */
	static public function getStructure()
	{
		return array(
			'title' => 'MageBridge',
			'menu' => array(
				'home' => 'HOME',
				'config' => 'CONFIG',
				'stores' => 'STORES',
				'products' => 'PRODUCTS',
				'usergroups' => 'USERGROUPS',
				'urls' => 'URLS',
				'users' => 'USERS',
				'check' => 'CHECK',
				'logs' => 'LOGS',
				'update' => 'UPDATE',
			),
			'views' => array(
				'home' => 'HOME',
				'configuration' => 'CONFIGURATION',
				'usergroups' => 'USERGROUPS',
				'usergroup' => 'USERGROUP',
				'products' => 'PRODUCTS',
				'product' => 'PRODUCT',
				'stores' => 'STORES',
				'store' => 'STORE',
				'urls' => 'URLS',
				'url' => 'URL',
				'users' => 'USERS',
				'check' => 'CHECK',
				'log' => 'LOG',
				'logs' => 'LOGS',
				'update' => 'UPDATE',
			),
			'obsolete_files' => array(
				JPATH_ADMINISTRATOR.'/components/com_magebridge/css',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/lib',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/images',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/js',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/home/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/home/tmpl/feeds.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/usergroups/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/logs/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/stores/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/urls/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/connectors',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/connector',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/products/tmpl/default.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/product/tmpl/form.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/config/tmpl/default_license.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/config/tmpl/joomla25/field.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/views/config/tmpl/default_advanced.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/helpers/toolbar.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/models/config.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/models/connectors.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/models/connector.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/models/proxy.php',
				JPATH_ADMINISTRATOR.'/components/com_magebridge/tables/connector.php',
				JPATH_SITE.'/components/com_magebridge/connectors/product',
				JPATH_SITE.'/components/com_magebridge/connectors/store',
				JPATH_SITE.'/components/com_magebridge/connectors/profile',
				JPATH_SITE.'/components/com_magebridge/helpers/acl.php',
				JPATH_SITE.'/components/com_magebridge/helpers/xmlrpc.php',
				JPATH_SITE.'/components/com_magebridge/libraries/xmlrpc.php',
				JPATH_SITE.'/components/com_magebridge/controllers/default.json.php',
				JPATH_SITE.'/components/com_magebridge/controllers/default.xmlrpc.php',
				JPATH_SITE.'/components/com_magebridge/controllers/default.php',
				JPATH_SITE.'/components/com_magebridge/models/encryption.php',
				JPATH_SITE.'/components/com_magebridge/views/content/tmpl/default.php',
				JPATH_SITE.'/components/com_magebridge/views/content/tmpl/default.xml',
				JPATH_SITE.'/components/com_magebridge/views/catalog/tmpl/default.php',
				JPATH_SITE.'/components/com_magebridge/views/catalog/tmpl/default.xml',
				JPATH_SITE.'/components/com_magebridge/rewrite-16',
				JPATH_SITE.'/components/com_magebridge/rewrite-17',
				JPATH_SITE.'/components/com_magebridge/rewrite-25',
				JPATH_SITE.'/components/com_magebridge/rewrite-30',
				JPATH_SITE.'/components/com_magebridge/rewrite-31',
				JPATH_SITE.'/components/com_magebridge/rewrite-32',
				JPATH_SITE.'/components/com_magebridge/rewrite',
				JPATH_SITE.'/media/com_magebridge/css/backend-home.css',
				JPATH_SITE.'/media/com_magebridge/css/backend-j16.css',
				JPATH_SITE.'/media/com_magebridge/js/index.php',
			),
		);
	}
}
