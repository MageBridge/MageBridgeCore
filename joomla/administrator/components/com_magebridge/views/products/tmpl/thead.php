<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>
<th class="title">
	<?php echo JHtml::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_LABEL', 's.label', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="200" class="title">
	<?php echo JHtml::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_PRODUCT_FIELD_SKU', 's.sku', $this->lists['order_Dir'], $this->lists['order']); ?>
</th>
<th width="100" class="title">
	<?php echo JText::_('COM_MAGEBRIDGE_PRODUCT_PARAM_ALLOWED_STATUS'); ?>
</th>
