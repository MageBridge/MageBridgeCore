<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge configuration class
 */
class MageBridgeModelConfigDefaults
{
    /**
     * Array of default values
     *
     * @var array
     */
    protected $_defaults = [];

    /**
     * Constructor
     *
     * @param null
     *
     * @retun array
     */
    public function __construct()
    {
        $this->_defaults = [
            'supportkey'                          => '',
            'host'                                => '',
            'protocol'                            => 'http',
            'method'                              => 'post',
            'encryption'                          => '0',
            'encryption_key'                      => null,
            'http_auth'                           => 0,
            'http_user'                           => '',
            'http_password'                       => '',
            'http_authtype'                       => CURLAUTH_ANY,
            'enforce_ssl'                         => 0,
            'ssl_version'                         => 0,
            'ssl_ciphers'                         => null,
            'basedir'                             => '',
            'offline'                             => 0,
            'offline_message'                     => 'The webshop is currently not available. Please come back again later.',
            'offline_exclude_ip'                  => '',
            'website'                             => '1',
            'storegroup'                          => null,
            'storeview'                           => null,
            'backend'                             => 'admin',
            'api_user'                            => '',
            'api_key'                             => '',
            'api_widgets'                         => '1',
            'api_type'                            => 'jsonrpc',
            'enable_cache'                        => '0',
            'cache_time'                          => '300',
            'debug'                               => '0',
            'debug_ip'                            => '',
            'debug_log'                           => 'db',
            'debug_level'                         => 'all',
            'debug_console'                       => '1',
            'debug_bar'                           => '1',
            'debug_bar_parts'                     => '1',
            'debug_bar_request'                   => '1',
            'debug_bar_store'                     => '1',
            'debug_display_errors'                => '0',
            'disable_css_mage'                    => '',
            // List of CSS files from Magento
            'disable_css_all'                     => 0,
            // Disable Magento CSS or not
            'disable_default_css'                 => 1,
            // Disable MageBridge CSS
            'disable_js_mage'                     => 'varien/menu.js,lib/ds-sleight.js,js/ie6.js',
            // List of JS files from Magento
            'disable_js_mootools'                 => 1,
            // Disable MooTools
            'disable_js_footools'                 => 0,
            // Disable FooTools
            'disable_js_frototype'                => 0,
            // Disable Frototype
            'disable_js_jquery'                   => 0,
            // Disable jQuery
            'disable_js_prototype'                => 0,
            // Disable Magento ProtoType
            'disable_js_custom'                   => '',
            // Custom list of JS files from Joomla!
            'disable_js_all'                      => 1,
            // Disable Joomla! JS
            'replace_jquery'                      => 1,
            // Replace Magento jQuery with Joomla
            'merge_js'                            => 0,
            'merge_css'                           => 0,
            'use_google_api'                      => 0,
            'use_protoaculous'                    => 0,
            'use_protoculous'                     => 0,
            'bridge_cookie_all'                   => 0,
            'bridge_cookie_custom'                => '',
            'flush_positions'                     => 0,
            'flush_positions_home'                => '',
            'flush_positions_customer'            => '',
            'flush_positions_product'             => '',
            'flush_positions_category'            => '',
            'flush_positions_cart'                => '',
            'flush_positions_checkout'            => '',
            'use_rootmenu'                        => 1,
            'preload_all_modules'                 => 0,
            'enforce_rootmenu'                    => 0,
            'customer_group'                      => '',
            'customer_pages'                      => '',
            'usergroup'                           => '',
            'enable_sso'                          => 0,
            'enable_usersync'                     => 1,
            'username_from_email'                 => 0,
            'realname_from_firstlast'             => 1,
            'realname_with_space'                 => 1,
            'enable_auth_backend'                 => 0,
            'enable_auth_frontend'                => 1,
            'enable_content_plugins'              => 0,
            'enable_block_rendering'              => 0,
            'enable_jdoc_tags'                    => 1,
            'enable_messages'                     => 1,
            'enable_breadcrumbs'                  => 1,
            'modify_url'                          => 1,
            'link_to_magento'                     => 0,
            'module_chrome'                       => 'raw',
            'module_show_title'                   => 1,
            'mobile_joomla_theme'                 => 'magebridge_mobile',
            'mobile_magento_theme'                => 'iphone',
            'magento_theme'                       => '',
            'spoof_browser'                       => 1,
            'spoof_headers'                       => 0,
            'curl_post_as_array'                  => 1,
            'curl_timeout'                        => 120,
            'enable_notfound'                     => 0,
            'payment_urls'                        => '',
            'direct_output'                       => '',
            'template'                            => '',
            'update_format'                       => '',
            'update_method'                       => 'curl',
            'backend_feed'                        => 1,
            'users_website_id'                    => '',
            'users_group_id'                      => '',
            'keep_alive'                          => '1',
            'load_urls'                           => '1',
            'load_stores'                         => '1',
            'filter_content'                      => '1',
            'filter_store_from_url'               => '1',
            'show_help'                           => '1',
            'enable_canonical'                    => '1',
            'use_referer_for_homepage_redirects'  => '1',
            'use_homepage_for_homepage_redirects' => '0',
        ];
    }

    public function getDefaults()
    {
        return $this->_defaults;
    }
}
