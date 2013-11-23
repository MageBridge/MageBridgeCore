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
<th class="title">
    <?php echo JHTML::_('grid.sort',  'Message', 'log.message', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
    <?php echo JHTML::_('grid.sort',  'Type', 'log.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
    <?php echo JHTML::_('grid.sort',  'Origin', 'log.origin', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
    <?php echo JHTML::_('grid.sort',  'IP', 'log.ip', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="1%" nowrap="nowrap">
    <?php echo JHTML::_('grid.sort',  'Debug Session', 'log.session', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="140" class="title">
    <?php echo JHTML::_('grid.sort',  'Time', 'log.timestamp', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
