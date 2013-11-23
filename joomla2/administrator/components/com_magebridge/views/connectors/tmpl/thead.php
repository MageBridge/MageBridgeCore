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
<th width="150" class="title">
    <?php echo JHTML::_('grid.sort',  'Title', 'p.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
    <?php echo JHTML::_('grid.sort',  'Name', 'p.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" class="title">
    <?php echo JHTML::_('grid.sort',  'Connector Type', 'p.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="80" nowrap="nowrap">
    <?php echo JHTML::_('grid.sort',  'Filename', 'p.filename', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
