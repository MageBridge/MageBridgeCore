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
	<?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 'label', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th class="title">
	<?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_DESCRIPTION', 'description', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_USERGROUP_FIELD_JOOMLA_GROUP', 'joomla_group', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_USERGROUP_FIELD_MAGENTO_GROUP', 'magento_group', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
