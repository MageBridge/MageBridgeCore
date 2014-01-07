<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<p class="notice"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_ELEMENT_SELECT_PRODUCT'); ?></p>

<form method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
    <td align="left" width="60%">
        <?php echo JText::_( 'Filter' ); ?>:
        <input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>"
        class="text_area" onchange="document.adminForm.submit();" />
        <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
        <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
    </td>
    <td align="right" width="40%">
        <?php $js = "window.parent.jSelectProduct('', '', '".JRequest::getVar('object')."');"; ?>
        <button onclick="<?php echo $js; ?>"><?php echo JText::_('No product'); ?></button>
    </td>
</tr>
</table>
<table class="adminlist" cellspacing="1">
<thead>
    <tr>
        <th width="30">
            <?php echo JText::_( 'Num' ); ?>
        </th>
        <th class="title" width="300">
            <?php echo JText::_( 'Title' ); ?>
        </th>
        <th class="title" width="100">
            <?php echo JText::_( 'SKU' ); ?>
        </th>
        <th class="title">
            <?php echo JText::_( 'URL key' ); ?>
        </th>
        <th class="title">
            <?php echo JText::_( 'Active' ); ?>
        </th>
        <th width="30">
            <?php echo JText::_( 'ID' ); ?>
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

        if (JRequest::getCmd('return') == 'id' || JRequest::getCmd('return') == 'product_id') {
            $return = $product['product_id'];
        } else if (JRequest::getCmd('return') == 'sku' && !empty($product['sku'])) {
            $return = $product['sku'];
        } else if (!empty($product['url_key'])) {
            $return = $product['url_key'];
        } else {
            $return = $product['product_id'];
        }

        if (JRequest::getCmd('current') == $return) {
            $css[] = 'current';
        }

        $css = array();
        if (isset($product['status']) && $product['status'] == 1) {
            $css[] = 'active';
        } else {
            $css[] = 'inactive';
        }

        if (strlen($product['name']) > 50) {
            $product['name'] = substr($product['name'], 0, 47).'...';
        }

        if (strlen($product['url_key']) > 30) {
            $product['url_key'] = substr($product['url_key'], 0, 27).'...';
        }

        $product_name = htmlspecialchars(str_replace("'", '', $product['name']));
        $jsDefault = "window.parent.jSelectProduct('$return', '$product_name', '".JRequest::getVar('object')."');";
        ?>
        <tr class="<?php echo implode(' ', $css); ?>">
            <td>
                <?php echo $this->pagination->getRowOffset( $i ); ?>
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
                <?php echo ((isset($product['status']) && $product['status'] == 1) ? JText::_('Yes') : JText::_('No')); ?>
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
        <td colspan="6"><?php echo JText::_('No products found'); ?></td>
    </tr>
    <?php
}
?>
</tbody>
</table>
<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="element" />
<input type="hidden" name="type" value="product" />
<input type="hidden" name="object" value="<?php echo $this->object; ?>" />
<input type="hidden" name="current" value="<?php echo $this->current; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
