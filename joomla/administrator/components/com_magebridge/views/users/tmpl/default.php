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

$enabled_img = JHTML::image(JURI::base().'/images/disabled.png', JText::_('Disabled'));
$disabled_img = JHTML::image(JURI::base().'/images/check.png', JText::_('Enabled'));
?>
<form method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td nowrap="nowrap">
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th width="150" class="title">
				<?php echo JHTML::_('grid.sort',  'Name', 'u.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="150" class="title">
				<?php echo JHTML::_('grid.sort',  'Username', 'u.username', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="150" class="title">
				<?php echo JHTML::_('grid.sort',  'Email', 'u.email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="150" class="title">
				<?php echo JText::_('Magento Name'); ?>
			</th>
			<th width="100" class="title">
				<?php echo JText::_('User Type'); ?>
			</th>
			<th width="40" class="title">
				<?php echo JText::_('Password'); ?>
			</th>
			<th width="40" class="title">
				<?php echo JText::_('Magento ID'); ?>
			</th>
			<th width="40" class="title">
				<?php echo JText::_('Joomla! ID'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="11">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	if ( count( $this->items ) > 0 ) {
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$item = $this->items[$i];
			$migration_enabled = true;
			$item->checked_out = 0;

			$checked = ($migration_enabled) ? $this->checkbox($item, $i) : '<input type="checkbox" disabled/>';
			$enabled = ($item->block == 0) ? $enabled_img : $disabled_img;
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $this->pagination->getRowOffset( $i ); ?>
				</td>
				<td>
					<?php echo $checked; ?>
				</td>
				<td>
					<?php echo $item->name; ?>
				</td>
				<td>
					<?php echo $item->username; ?>
				</td>
				<td>
					<?php echo $item->email; ?>
				</td>
				<td>
					<?php echo $item->magento_name; ?>
				</td>
				<td>
					<?php echo ''; ?>
				</td>
				<td>
					<?php echo ($item->password) ? '****': '[empty]' ; ?>
				</td>
				<td>
					<?php echo $item->magento_id; ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
	} else {
		?>
		<tr>
		<td colspan="11">
			<?php echo JText::_( 'No items' ); ?>
		</td>
		</tr>
		<?php
	}
	?>
	</tbody>
	</table>
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
