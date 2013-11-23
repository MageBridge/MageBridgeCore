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
<legend><?php echo JText::_('Debug settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_IP'); ?>
        </td>
        <td class="value">
            <input type="text" name="debug_ip" value="<?php echo $this->config['debug_ip']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_IP_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Your_IP'); ?>
        </td>
        <td class="value">
            <a id="remoteaddr"><?php echo $_SERVER['REMOTE_ADDR']; ?></a>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            &nbsp;
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Level'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_level']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_LEVEL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Log'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_log']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_LOG_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Console'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_console']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_CONSOLE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Bar'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_bar']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_BAR_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Bar_Parts'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_bar_parts']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_BAR_PARTS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Bar_Request'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_bar_request']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_BAR_REQUEST_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Bar_Store'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_bar_store']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_BAR_STORE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Debug_Display_Errors'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['debug_display_errors']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DEBUG_DISPLAY_ERRORS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
