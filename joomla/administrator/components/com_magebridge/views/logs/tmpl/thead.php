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
<th class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_MESSAGE', 'log.message', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_TYPE', 'log.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_ORIGIN', 'log.origin', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_IP', 'log.ip', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="1%" nowrap="nowrap">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_SESSION', 'log.session', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="140" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_LOGS_TIME', 'log.timestamp', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
