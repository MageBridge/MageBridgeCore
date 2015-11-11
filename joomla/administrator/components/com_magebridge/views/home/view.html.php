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
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewHome extends YireoViewHome
{
	/**
	 * Display method
	 *
	 * @param string $tpl
	 * @return null
	 */
	public function display($tpl = null)
	{
		$icons = array();
		$icons[] = $this->icon( 'config', 'COM_MAGEBRIDGE_VIEW_CONFIG', 'config.png');
		$icons[] = $this->icon( 'stores', 'COM_MAGEBRIDGE_VIEW_STORES', 'store.png');
		$icons[] = $this->icon( 'products', 'COM_MAGEBRIDGE_VIEW_PRODUCTS', 'product.png');
		$icons[] = $this->icon( 'users', 'COM_MAGEBRIDGE_VIEW_USERS', 'user.png');
		$icons[] = $this->icon( 'check', 'COM_MAGEBRIDGE_VIEW_CHECK', 'cpanel.png');
		$icons[] = $this->icon( 'logs', 'COM_MAGEBRIDGE_VIEW_LOGS', 'info.png');
		$icons[] = $this->icon( 'update', 'COM_MAGEBRIDGE_VIEW_UPDATE', 'install.png');
		$icons[] = $this->icon( 'cache', 'COM_MAGEBRIDGE_CLEAN_CACHE', 'trash.png');
		$icons[] = $this->icon( 'magento', 'COM_MAGEBRIDGE_MAGENTO_BACKEND', 'magento.png', null, '_blank');
		$icons[] = $this->icon( 'tutorials', 'LIB_YIREO_TUTORIALS', 'tutorials.png', null, '_blank');
		$icons[] = $this->icon( 'forum', 'LIB_YIREO_FORUM', 'forum.png', null, '_blank');
		$this->icons = $icons;

		$urls = array();
		$urls['twitter'] ='http://twitter.com/yireo';
		$urls['facebook'] ='http://www.facebook.com/yireo';
		$urls['tutorials'] = 'http://www.yireo.com/tutorials/magebridge';
		$urls['jed'] ='http://extensions.joomla.org/extensions/bridges/e-commerce-bridges/9440';
		$urls['changelog'] ='http://www.yireo.com/tutorials/magebridge/updates/975-magebridge-changelog'; // @todo: Use this
		$this->urls = $urls;

		parent::display($tpl);
	}
}
