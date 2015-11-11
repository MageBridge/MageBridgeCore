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
		<legend><?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_LABEL'); ?></legend>
		<table class="admintable" width="100%">
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
		</tbody>
		</table>
	</fieldset>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELDSET_RELATION'); ?></legend>
		<table class="admintable" width="100%">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="sku">
					<?php echo JText::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELD_SKU'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->lists['product']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_('JPUBLISHED'); ?>:
			</td>
			<td class="value">
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<label for="ordering">
					<?php echo JText::_('JORDERING'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->lists['ordering']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>

	<?php echo $this->loadTemplate('actions'); ?>
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
