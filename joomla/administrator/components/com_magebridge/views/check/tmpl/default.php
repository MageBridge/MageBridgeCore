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

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td valign="top">
	<fieldset class="adminform">
	<legend><?php echo JText::_('COM_MAGEBRIDGE_CHECK_SUGGESTIONS'); ?></legend>
	<table class="admintable" width="100%">
		<tr>
			<td class="key"><?php echo JText::_('COM_MAGEBRIDGE_CHECK_BROWSE_TEST'); ?></td>
			<td class="result"><?php echo JText::sprintf('COM_MAGEBRIDGE_CHECK_BROWSE_TEST_DESC', 'index.php?option=com_magebridge&view=check&layout=browser'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_MAGEBRIDGE_CHECK_PRODUCT_TEST'); ?></td>
			<td class="result"><?php echo JText::sprintf('COM_MAGEBRIDGE_CHECK_PRODUCT_TEST_DESC', 'index.php?option=com_magebridge&view=check&layout=product'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_MAGEBRIDGE_CHECK_MAGENTO_CHECK'); ?></td>
			<td class="result"><?php echo JText::_('COM_MAGEBRIDGE_CHECK_MAGENTO_CHECK_DESC'); ?></td>
		</tr>
		<tr>
			<td class="key"><?php echo JText::_('COM_MAGEBRIDGE_CHECK_TROUBLESHOOTING_GUIDE'); ?></td>
			<td class="result"><?php echo JText::sprintf('COM_MAGEBRIDGE_CHECK_TROUBLESHOOTING_GUIDE_DESC', MageBridgeHelper::getHelpLink('troubleshooting')); ?></td>
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
