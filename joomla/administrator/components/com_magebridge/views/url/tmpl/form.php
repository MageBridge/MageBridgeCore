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
		<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_SOURCE'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="source">
					<?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_SOURCE'); ?>:
				</label>
			</td>
			<td class="value">
				<input type="text" name="source" value="<?php echo $this->item->source; ?>" size="60" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_SOURCE_TYPE'); ?>:
			</td>
			<td class="value">
				<?php echo $this->lists['source_type']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_DESTINATION'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="destination">
					<?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_DESTINATION'); ?>:
				</label>
			</td>
			<td class="value">
				<input type="text" name="destination" value="<?php echo $this->item->destination; ?>" size="60" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_URLS_FIELDSET_META'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="description">
					<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_DESCRIPTION'); ?>:
				</label>
			</td>
			<td class="value">
				<input type="text" name="description" value="<?php echo $this->item->description; ?>" size="40" />
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_PUBLISHED'); ?>:
			</td>
			<td class="value">
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
		</tbody>
		</table>
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
