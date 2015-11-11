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
		<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_STORE'); ?></legend>
		<table class="admintable">
		<tbody>
		<tr>
			<td width="100" align="right" class="key">
				<label for="store">
					<?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELD_STORE'); ?>:
				</label>
			</td>
			<td class="value">
				<?php echo $this->lists['store']; ?>
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
</td>
</tr>
</tbody>
</table>
<input type="hidden" name="default" value="1" />
<input type="hidden" name="apply_url" value="<?php echo JRoute::_('index.php?option=com_magebridge&view=store&task=default'); ?>" />
<?php echo $this->loadTemplate('formend'); ?>
</form>
