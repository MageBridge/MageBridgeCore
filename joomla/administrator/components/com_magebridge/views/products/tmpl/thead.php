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
<th width="200" class="title">
	<?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 's.label', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="160" class="title">
	<?php echo JHTML::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_PRODUCT_FIELD_SKU', 's.sku', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
