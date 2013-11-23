<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
<legend>Magento <?php echo JText::_('Server settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Host'); ?>
        </td>
        <td class="value">
            <input type="text" name="host" value="<?php echo $this->config['host']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'HOST_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <?php if (MageBridgeHelper::isJoomla15()) { ?>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('API_type'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['api_type']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'API_TYPE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Protocol'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['protocol']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'PROTOCOL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Method'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['method']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'METHOD_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Basedir'); ?>
        </td>
        <td class="value">
            <input type="text" name="basedir" value="<?php echo $this->config['basedir']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'BASEDIR_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
    
<?php $display = (MageBridgeModelConfig::load('http_auth') == 1) ? 'block' : 'none'; ?>
<fieldset class="adminform">
<legend><a onclick="return toggleFieldset('http_auth');" href="#"><?php echo JText::_('HTTP_Auth'); ?></a></legend>
<table class="admintable" id="http_auth" style="display:<?php echo $display; ?>">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('HTTP_Auth'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['http_auth']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'HTTP_AUTH_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('HTTP_AuthType'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['http_authtype']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'HTTP_AUTHTYPE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('HTTP_User'); ?>
        </td>
        <td class="value">
            <input type="text" name="http_user" value="<?php echo $this->config['http_user']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'HTTP_USER_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('HTTP_Password'); ?>
        </td>
        <td class="value">
            <input type="password" name="http_password" value="<?php echo $this->config['http_password']['value']; ?>" size="30" autocomplete="off" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'HTTP_PASSWORD_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend>Magento <?php echo JText::_('API settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('API_user'); ?>
        </td>
        <td class="value">
            <input type="text" name="api_user" value="<?php echo $this->config['api_user']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'API_USER_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('API_key'); ?>
        </td>
        <td class="value">
            <input type="password" name="api_key" value="<?php echo $this->config['api_key']['value']; ?>" size="30" autocomplete="off" />
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'API_KEY_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

