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
<th width="200" class="title">
	<?php echo JHtml::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_URLS_SOURCE', 'source', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
<th width="200" class="title">
	<?php echo JHtml::_('grid.sort', 'COM_MAGEBRIDGE_VIEW_URLS_DESTINATION', 'destination', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
