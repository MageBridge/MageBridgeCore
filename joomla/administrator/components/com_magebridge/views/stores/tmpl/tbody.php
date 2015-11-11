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
<td>
	<a href="<?php echo $item->custom_edit_link; ?>" title="<?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_ACTION_EDIT'); ?>"><?php echo $item->label; ?></a>
</td>
<td>
	<a href="<?php echo $item->custom_edit_link; ?>" title="<?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_ACTION_EDIT'); ?>"><?php echo $item->title; ?></a>
</td>
<td>
	<?php echo $item->name; ?>
</td>
<td>
	<?php echo JText::_($item->type); ?>
</td>
<td>
	<?php echo $item->connector; ?>
</td>
<td>
	<?php echo $item->connector_value; ?>
</td>
