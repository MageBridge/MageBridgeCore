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
<th width="200" class="title">
    <?php echo JHTML::_('grid.sort',  'Label', 's.label', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
    <?php echo JHTML::_('grid.sort',  'Store Title', 's.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
    <?php echo JHTML::_('grid.sort',  'Store Code', 's.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
    <?php echo JHTML::_('grid.sort',  'Store Type', 's.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" nowrap="nowrap">
    <?php echo JHTML::_('grid.sort',  'Connector Name', 's.connector', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="200" nowrap="nowrap">
    <?php echo JHTML::_('grid.sort',  'Connector Value', 's.connector_value', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
