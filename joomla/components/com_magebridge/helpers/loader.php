<?php
/**
 * Joomla! component MageBridge
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include the original Joomla! loader
require_once(JPATH_LIBRARIES . '/loader.php');

// Also include the Yireo loader
if (file_exists(JPATH_LIBRARIES . '/yireo/loader.php'))
{
	require_once(JPATH_LIBRARIES . '/yireo/loader.php');
}
else
{
	require_once JPATH_ADMINISTRATOR . '/components/com_magebridge/libraries/loader.php';
}

// If the Joomla! autoloader exists, add it to SPL
if (function_exists('__autoload'))
{
	spl_autoload_register('__autoload');
}

// Add our own loader-function to SPL
spl_autoload_register('MageBridge_Autoload::load');

/**
 * Loader-class to load all other MageBridge classes
 */

class MageBridge_Autoload
{
	static public function load($name = null)
	{
		// Preliminary check
		if (empty($name))
		{
			return false;
		}

		// Get the class names
		$classes = MageBridge_Autoload::getClassNames();

		// Try to actually include the class
		if (isset($classes[$name]) && is_file($classes[$name]) && is_readable($classes[$name]))
		{
			require_once $classes[$name];

			return true;
		}

		return false;
	}

	static private function getClassNames()
	{
		// Note that not all classes are included, because MVC finds them anyway
		$classes = array(
			'MageBridge' => JPATH_SITE . '/components/com_magebridge/libraries/factory.php',
			'MageBridgeApi' => JPATH_SITE . '/components/com_magebridge/libraries/api.php',
			'MageBridgePlugin' => JPATH_SITE . '/components/com_magebridge/libraries/plugin.php',
			'MageBridgePluginProduct' => JPATH_SITE . '/components/com_magebridge/libraries/plugin/product.php',
			'MageBridgePluginStore' => JPATH_SITE . '/components/com_magebridge/libraries/plugin/store.php',
			'MageBridgePluginMagento' => JPATH_SITE . '/components/com_magebridge/libraries/plugin/magento.php',
			'MageBridgePluginProfile' => JPATH_SITE . '/components/com_magebridge/libraries/plugin/profile.php',
			'MageBridgeHelper' => JPATH_SITE . '/components/com_magebridge/helpers/helper.php',
			'MageBridgeUrlHelper' => JPATH_SITE . '/components/com_magebridge/helpers/url.php',
			'MageBridgeStoreHelper' => JPATH_SITE . '/components/com_magebridge/helpers/store.php',
			'MageBridgeProxyHelper' => JPATH_SITE . '/components/com_magebridge/helpers/proxy.php',
			'MageBridgeEncryptionHelper' => JPATH_SITE . '/components/com_magebridge/helpers/encryption.php',
			'MageBridgeRegisterHelper' => JPATH_SITE . '/components/com_magebridge/helpers/register.php',
			'MageBridgeModuleHelper' => JPATH_SITE . '/components/com_magebridge/helpers/module.php',
			'MageBridgeTemplateHelper' => JPATH_SITE . '/components/com_magebridge/helpers/template.php',
			'MageBridgeBlockHelper' => JPATH_SITE . '/components/com_magebridge/helpers/block.php',
			'MageBridgeBridgeHelper' => JPATH_SITE . '/components/com_magebridge/helpers/bridge.php',
			'MageBridgeAjaxHelper' => JPATH_SITE . '/components/com_magebridge/helpers/ajax.php',
			'MageBridgeDebugHelper' => JPATH_SITE . '/components/com_magebridge/helpers/debug.php',
			'MageBridgePluginHelper' => JPATH_SITE . '/components/com_magebridge/helpers/plugin.php',
			'MageBridgeUserHelper' => JPATH_SITE . '/components/com_magebridge/helpers/user.php',
			'MageBridgeConnector' => JPATH_SITE . '/components/com_magebridge/connector.php',
			'MageBridgeConnectorStore' => JPATH_SITE . '/components/com_magebridge/connectors/store.php',
			'MageBridgeConnectorProfile' => JPATH_SITE . '/components/com_magebridge/connectors/profile.php',
			'MageBridgeConnectorProduct' => JPATH_SITE . '/components/com_magebridge/connectors/product.php',
			'MageBridgeConnectorNewsletter' => JPATH_SITE . '/components/com_magebridge/connectors/newsletter.php',
			'MageBridgeModelCheck' => JPATH_SITE . '/components/com_magebridge/models/check.php',
			'MageBridgeModelBridge' => JPATH_SITE . '/components/com_magebridge/models/bridge.php',
			'MageBridgeModelBridgeSegment' => JPATH_SITE . '/components/com_magebridge/models/bridge/segment.php',
			'MageBridgeModelBridgeHeaders' => JPATH_SITE . '/components/com_magebridge/models/bridge/headers.php',
			'MageBridgeModelBridgeMeta' => JPATH_SITE . '/components/com_magebridge/models/bridge/meta.php',
			'MageBridgeModelBridgeApi' => JPATH_SITE . '/components/com_magebridge/models/bridge/api.php',
			'MageBridgeModelBridgeEvents' => JPATH_SITE . '/components/com_magebridge/models/bridge/events.php',
			'MageBridgeModelBridgeBlock' => JPATH_SITE . '/components/com_magebridge/models/bridge/block.php',
			'MageBridgeModelBridgeWidget' => JPATH_SITE . '/components/com_magebridge/models/bridge/widget.php',
			//'MageBridgeModelBridgeFilter' => JPATH_SITE.'/components/com_magebridge/models/bridge/filter.php',
			'MageBridgeModelBridgeBreadcrumbs' => JPATH_SITE . '/components/com_magebridge/models/bridge/breadcrumbs.php',
			'MageBridgeModelProxy' => JPATH_SITE . '/components/com_magebridge/models/proxy.php',
			'MageBridgeModelProxyAbstract' => JPATH_SITE . '/components/com_magebridge/models/proxy/abstract.php',
			'MagebridgeModelConfig' => JPATH_SITE . '/components/com_magebridge/models/config.php',
			'MageBridgeModelConfig' => JPATH_SITE . '/components/com_magebridge/models/config.php',
			'MagebridgeModelDebug' => JPATH_SITE . '/components/com_magebridge/models/debug.php',
			'MageBridgeModelDebug' => JPATH_SITE . '/components/com_magebridge/models/debug.php',
			'MageBridgeModelRegister' => JPATH_SITE . '/components/com_magebridge/models/register.php',
			'MageBridgeModelCache' => JPATH_SITE . '/components/com_magebridge/models/cache.php',
			'MageBridgeModelCacheBlock' => JPATH_SITE . '/components/com_magebridge/models/cache/block.php',
			'MageBridgeModelCacheHeaders' => JPATH_SITE . '/components/com_magebridge/models/cache/headers.php',
			'MageBridgeModelCacheBreadcrumbs' => JPATH_SITE . '/components/com_magebridge/models/cache/breadcrumbs.php',
			'MageBridgeModelUser' => JPATH_SITE . '/components/com_magebridge/models/user.php',
			'MageBridgeModelUserSSO' => JPATH_SITE . '/components/com_magebridge/models/user/sso.php',
			'MageBridgeAclHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/acl.php',
			'MageBridgeToolBarHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/toolbar.php',
			'MageBridgeViewHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/view.php',
			'MageBridgeElementHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/element.php',
			'MageBridgeFormHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/form.php',
			'MageBridgeUpdateHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/update.php',
			'MageBridgeWidgetHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/widget.php',
			'MageBridgeInstallHelper' => JPATH_ADMINISTRATOR . '/components/com_magebridge/helpers/install.php',
			'MagebridgeFormFieldAbstract' => JPATH_ADMINISTRATOR . '/components/com_magebridge/fields/abstract.php',);

		// Get system variables
		$application = JFactory::getApplication();

		// Load different classes depending on which application we're using
		if ($application->isAdmin())
		{
			$classes['MageBridgeController'] = JPATH_ADMINISTRATOR . '/components/com_magebridge/controller.php';
		}
		else
		{
			if ($application->isSite())
			{
				$classes['MageBridgeController'] = JPATH_SITE . '/components/com_magebridge/controller.php';
			}
		}

		return $classes;
	}
}

