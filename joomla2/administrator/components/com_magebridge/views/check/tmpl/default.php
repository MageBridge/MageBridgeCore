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
<form method="post" name="adminForm" id="adminForm">

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td valign="top">
    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_SUGGESTIONS'); ?></legend>
    <table class="admintable" width="100%">
        <tr>
            <td class="key">Browse-test</td>
            <td class="result">Use the <a href="index.php?option=com_magebridge&view=check&layout=browser">internal browse-test</a> to check if Magento is accessible from Joomla!</a></td>
        </tr>
        <tr>
            <td class="key">Check Magento</td>
            <td class="result">Make sure you review <strong>CMS &gt; MageBridge Settings &gt; System Check</strong> within Magento</td>
        </tr>
        <tr>
            <td class="key">Troubleshooting Guide</td>
            <td class="result">See the <?php echo MageBridgeHelper::getHelpText('troubleshooting'); ?> for more help</td>
        </tr>
    </table>
    </fieldset>

    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_COMPATIBILITY'); ?></legend>
    <table class="admintable" width="100%">
    <?php foreach ($this->checks['compatibility'] as $result) { ?>
    <tr class="check-row-<?php echo $result['status']; ?>">
        <td class="key" valign="top">
            <?php echo $result['text']; ?>
        </td>
        <td class="check-image check-image-<?php echo $result['status']; ?>"></td> 
        <td class="check-description">
            <?php echo $result['description']; ?>
        </td>
    </tr>
    <?php } ?>
    </table>
    </fieldset>

    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_EXTENSION_CONFLICT'); ?></legend>
    <table class="admintable" width="100%">
    <?php if (isset($this->checks['extension'])) { ?>
    <?php foreach ($this->checks['extension'] as $result) { ?>
    <tr class="check-row-<?php echo $result['status']; ?>">
        <td class="key" valign="top">
            <?php echo $result['text']; ?>
        </td>
        <td class="check-image check-image-<?php echo $result['status']; ?>"></td>
        <td class="check-description">
            <?php echo $result['description']; ?>
        </td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr class="check-row-ok">
        <td class="key" valign="top">
            &nbsp;
        </td>
        <td class="check-image check-image-ok"></td>
        <td class="check-description">
            <?php echo JText::_('COM_MAGEBRIDGE_CHECK_NO_CONFLICTING_EXTENSIONS'); ?>
        </td>
    </tr>
    <?php } ?>
    </table>
    </fieldset>

    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_BRIDGE'); ?></legend>
    <table class="admintable" width="100%">
    <?php foreach ($this->checks['bridge'] as $result) { ?>
    <tr class="check-row-<?php echo $result['status']; ?>">
        <td class="key" valign="top">
            <?php echo $result['text']; ?>
        </td>
        <td class="check-image check-image-<?php echo $result['status']; ?>"></td>
        <td class="check-description">
            <?php echo $result['description']; ?>
        </td>
    </tr>
    <?php } ?>
    </table>
    </fieldset>

    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_SYSTEM'); ?></legend>
    <table class="admintable" width="100%">
    <?php foreach ($this->checks['system'] as $result) { ?>
    <tr class="check-row-<?php echo $result['status']; ?>">
        <td class="key" valign="top">
            <?php echo $result['text']; ?>
        </td>
        <td class="check-image check-image-<?php echo $result['status']; ?>"></td> 
        <td class="check-description">
            <?php echo $result['description']; ?>
        </td>
    </tr>
    <?php } ?>
    </table>
    </fieldset>

    <fieldset class="adminform">
    <legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_EXTENSIONS'); ?></legend>
    <table class="admintable" width="100%">
    <?php foreach ($this->checks['extensions'] as $result) { ?>
    <tr class="check-row-<?php echo $result['status']; ?>">
        <td class="key" valign="top">
            <?php echo $result['text']; ?>
        </td>
        <td class="check-image check-image-<?php echo $result['status']; ?>"></td> 
        <td class="check-description">
            <?php echo $result['description']; ?>
        </td>
    </tr>
    <?php } ?>
    </table>
    </fieldset>
</td>
</tr>
</table>
<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
