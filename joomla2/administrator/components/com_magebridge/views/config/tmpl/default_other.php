<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<fieldset class="adminform">
<legend><?php echo JText::_('Plugin-events'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Block_Rendering'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_block_rendering']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_BLOCK_RENDERING_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Enable_Content_Plugins'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_content_plugins']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_CONTENT_PLUGINS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Enable_Jdoc_Tags'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_jdoc_tags']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_JDOC_TAGS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_CONFIG_FIELDSET_COOKIE'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Bridge_Cookie_All'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['bridge_cookie_all']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'BRIDGE_COOKIE_ALL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Bridge_Cookie_Custom'); ?>
        </td>
        <td class="value">
            <textarea name="bridge_cookie_custom" cols="36" rows="3"><?php echo $this->config['bridge_cookie_custom']['value']; ?></textarea>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'BRIDGE_COOKIE_CUSTOM_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Advanced settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_RootMenu'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_rootmenu']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_ROOTMENU_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enforce_RootMenu'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enforce_rootmenu']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENFORCE_ROOTMENU_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Messages'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_messages']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_MESSAGES_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Breadcrumbs'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_breadcrumbs']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_BREADCRUMBS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Enable_NotFound'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_notfound']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_NOTFOUND_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Canonical'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_canonical']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_CANONICAL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_Homepage_For_Homepage_Redirects'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_homepage_for_homepage_redirects']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_HOMEPAGE_FOR_HOMEPAGE_REDIRECTS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_Referer_For_Homepage_Redirects'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_referer_for_homepage_redirects']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_REFERER_FOR_HOMEPAGE_REDIRECTS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
<fieldset class="adminform">
<legend><?php echo JText::_('Backend options'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('API_widgets'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['api_widgets']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'API_WIDGETS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Backend_Feed'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['backend_feed']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'BACKEND_FEED_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Expert settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Modify_URL'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['modify_url']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'MODIFY_URL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Link_To_Magento'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['link_to_magento']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'LINK_TO_MAGENTO_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Spoof_Browser'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['spoof_browser']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'SPOOF_BROWSER_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Spoof_Headers'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['spoof_headers']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'SPOOF_HEADERS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Filter_Content'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['filter_content']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FILTER_CONTENT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Filter_Store_From_URL'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['filter_store_from_url']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FILTER_STORE_FROM_URL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Preload_All_Modules'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['preload_all_modules']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'PRELOAD_ALL_MODULES_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Curl_Post_As_Array'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['curl_post_as_array']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'CURL_POST_AS_ARRAY_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('CURL_Timeout'); ?>
        </td>
        <td class="value">
            <input type="text" name="curl_timeout" value="<?php echo $this->config['curl_timeout']['value']; ?>" size="5" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'CURL_TIMEOUT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Direct_Output'); ?>
        </td>
        <td class="value">
            <textarea name="direct_output" cols="36" rows="3"><?php echo $this->config['direct_output']['value']; ?></textarea>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DIRECT_OUTPUT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Update_Format'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['update_format']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'UPDATE_FORMAT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Update_Method'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['update_method']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'UPDATE_METHOD_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
<fieldset class="adminform">
<legend><?php echo JText::_('Performance settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Cache'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_cache']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENABLE_CACHE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Cache_Time'); ?>
        </td>
        <td class="value">
            <input type="text" name="cache_time" value="<?php echo $this->config['cache_time']['value']; ?>" size="5" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'CACHE_TIME_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Keep_Alive'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['keep_alive']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'KEEP_ALIVE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Encryption'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['encryption']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'ENCRYPTION_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
