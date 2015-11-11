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
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="50%" valign="top">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MAGEBRIDGE_USERGROUP_FIELDSET_USERGROUPS'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="joomla_group">
					<?php echo JText::_('COM_MAGEBRIDGE_USERGROUP_FIELD_JOOMLA_GROUP'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->fields['joomla_group']; ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="magento_group">
					<?php echo JText::_('COM_MAGEBRIDGE_USERGROUP_FIELD_MAGENTO_GROUP'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->fields['magento_group']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_('JDETAILS'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="label">
					<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_LABEL'); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="label" value="<?php echo $this->item->label; ?>" size="30" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="description">
					<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_DESCRIPTION'); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="description" value="<?php echo $this->item->description; ?>" size="30" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_('JPUBLISHED'); ?>:
			</td>
			<td class="value">
				<?php echo $this->fields['published']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="ordering">
					<?php echo JText::_('JORDERING'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->fields['ordering']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
</td>
<td width="50%" valign="top">
	<fieldset class="adminform">
		<legend><?php echo JText::_('JPARAMS'); ?></legend>
		<?php echo $this->loadTemplate('params'); ?>
	</fieldset>
</td>
</tr>
</tbody>
</table>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
