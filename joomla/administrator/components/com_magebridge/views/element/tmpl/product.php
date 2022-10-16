<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<p class="notice"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_ELEMENT_SELECT_PRODUCT'); ?></p>

<form method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td align="left" width="60%">
				<?php echo JText::_('LIB_YIREO_VIEW_FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>"
					   class="text_area" onchange="document.adminForm.submit();"/>
				<button onclick="this.form.submit();"><?php echo JText::_('LIB_YIREO_VIEW_SEARCH'); ?></button>
				<button
					onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('LIB_YIREO_VIEW_RESET'); ?></button>
			</td>
			<td align="right" width="40%">
				<?php $js = "window.parent.jSelectProduct('', '', '" . JFactory::getApplication()->input->get('object') . "');"; ?>
				<button onclick="<?php echo $js; ?>"><?php echo JText::_('COM_MAGEBRIDGE_NO_PRODUCT'); ?></button>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped" cellspacing="1">
		<thead>
		<tr>
			<th width="30">
				<?php echo JText::_('JNUM'); ?>
			</th>
			<th class="title" width="300">
				<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_TITLE'); ?>
			</th>
			<th class="title" width="100">
				<?php echo JText::_('COM_MAGEBRIDGE_VIEW_ELEMENT_SKU'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('COM_MAGEBRIDGE_VIEW_ELEMENT_URL_KEY'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('JACTIVE'); ?>
			</th>
			<th width="30">
				<?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_ID'); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="6">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
        if (!empty($this->products)) {
            $i = 0;
            foreach ($this->products as $product) {
                if (JFactory::getApplication()->input->getCmd('return') == 'id' || JFactory::getApplication()->input->getCmd('return') == 'product_id') {
                    $return = $product['product_id'];
                } else {
                    if (JFactory::getApplication()->input->getCmd('return') == 'sku' && !empty($product['sku'])) {
                        $return = $product['sku'];
                    } else {
                        if (!empty($product['url_key'])) {
                            $return = $product['url_key'];
                        } else {
                            $return = $product['product_id'];
                        }
                    }
                }

                if (JFactory::getApplication()->input->getCmd('current') == $return) {
                    $css[] = 'current';
                }

                $css = [];
                if (isset($product['status']) && $product['status'] == 1) {
                    $css[] = 'active';
                } else {
                    $css[] = 'inactive';
                }

                if (strlen($product['name']) > 50) {
                    $product['name'] = substr($product['name'], 0, 47) . '...';
                }

                if (strlen($product['url_key']) > 30) {
                    $product['url_key'] = substr($product['url_key'], 0, 27) . '...';
                }

                $product_name = htmlspecialchars(str_replace("'", '', $product['name']));
                $jsDefault = "window.parent.jSelectProduct('$return', '$product_name', '" . JFactory::getApplication()->input->get('object') . "');";
                ?>
				<tr class="<?php echo implode(' ', $css); ?>">
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>">
							<?php echo $product['name']; ?>
						</a>
					</td>
					<td>
						<a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>">
							<?php echo $product['sku']; ?>
						</a>
					</td>
					<td>
						<a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>">
							<?php echo $product['url_key']; ?>
						</a>
					</td>
					<td>
						<?php echo((isset($product['status']) && $product['status'] == 1) ? JText::_('JYES') : JText::_('JNO')); ?>
					</td>
					<td>
						<a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>">
							<?php echo $product['product_id']; ?>
						</a>
					</td>
				</tr>
				<?php
                $i++;
            }
        } else {
            ?>
			<tr>
				<td colspan="6"><?php echo JText::_('LIB_YIREO_VIEW_LIST_NO_ITEMS'); ?></td>
			</tr>
			<?php
        }
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_magebridge"/>
	<input type="hidden" name="view" value="element"/>
	<input type="hidden" name="type" value="product"/>
	<input type="hidden" name="object" value="<?php echo $this->object; ?>"/>
	<input type="hidden" name="current" value="<?php echo $this->current; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
