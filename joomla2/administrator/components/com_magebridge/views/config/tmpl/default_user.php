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
<legend><?php echo JText::_('User synchronization'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Customer_Group'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['customer_group']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'CUSTOMER_GROUP_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('UserGroup'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['usergroup']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'USERGROUP_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_SSO'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_sso']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'ENABLE_SSO_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_UserSync'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_usersync']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'ENABLE_USERSYNC_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Username_from_Email'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['username_from_email']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'USERNAME_FROM_EMAIL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Realname_from_FirstLast'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['realname_from_firstlast']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'REALNAME_FROM_FIRSTLAST_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Realname_with_Space'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['realname_with_space']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'REALNAME_WITH_SPACE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Auth_Backend'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_auth_backend']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'ENABLE_AUTH_BACKEND_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Enable_Auth_Frontend'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['enable_auth_frontend']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'ENABLE_AUTH_FRONTEND_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
<fieldset class="adminform">
<legend><?php echo JText::_('Importing and exporting'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Users_Group_Id'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['users_group_id']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'USERS_GROUP_ID_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Users_Website_Id'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['users_website_id']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description">
            <span><?php echo JText::_( 'USERS_WEBSITE_ID_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
