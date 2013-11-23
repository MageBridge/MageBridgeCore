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

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewHome extends YireoViewHome
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        $icons = array();
        $icons[] = $this->icon( 'config', 'Configuration', 'config.png');
        $icons[] = $this->icon( 'stores', 'Store Conditions', 'store.png');
        $icons[] = $this->icon( 'products', 'Product Relations', 'product.png');
        $icons[] = $this->icon( 'connectors', 'Connectors', 'connect.png');
        $icons[] = $this->icon( 'users', 'Users', 'user.png');
        $icons[] = $this->icon( 'check', 'System Check', 'cpanel.png');
        $icons[] = $this->icon( 'logs', 'Logs', 'info.png');
        $icons[] = $this->icon( 'update', 'Update', 'install.png');
        $icons[] = $this->icon( 'cache', 'Empty Cache', 'trash.png');
        $icons[] = $this->icon( 'magento', 'Magento Admin', 'magento.png', null, '_blank');
        $icons[] = $this->icon( 'tutorials', 'Tutorials', 'tutorials.png', null, '_blank');
        $icons[] = $this->icon( 'forum', 'Forum', 'forum.png', null, '_blank');
        $this->assignRef('icons', $icons);

        $urls = array();
        $urls['twitter'] ='http://twitter.com/yireo';
        $urls['facebook'] ='http://www.facebook.com/yireo';
        $urls['tutorials'] = 'http://www.yireo.com/tutorials/magebridge';
        $urls['jed'] ='http://extensions.joomla.org/extensions/bridges/e-commerce-bridges/9440';
        $urls['changelog'] ='http://www.yireo.com/tutorials/magebridge/updates/975-magebridge-changelog'; // @todo: Use this
        $this->assignRef( 'urls', $urls );

        //$current_version = MageBridgeUpdateHelper::getComponentVersion();
        //$this->assignRef( 'current_version', $current_version ); // @todo: is this working?

        parent::display($tpl);
    }
}
