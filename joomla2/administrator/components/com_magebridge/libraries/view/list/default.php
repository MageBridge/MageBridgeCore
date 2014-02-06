<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Check the table for certain capabilities
$table = $this->getModel()->getTable();
$hasState = ($table->getStateField()) ? true : false;
$hasOrdering = ($table->getDefaultOrderBy()) ? true : false;
?>
<form method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
    <td align="left" width="40%">
        <?php echo $this->loadTemplate('search'); ?>
    </td>
    <td align="right" width="60%">
        <?php echo $this->loadTemplate('lists'); ?>
    </td>
</tr>
</table>
<div id="editcell">
    <table class="adminlist table table-striped" width="100%">
    <thead>
        <tr>
            <th width="5">
                <?php echo JText::_('LIB_YIREO_VIEW_NUM'); ?>
            </th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
            </th>
            <?php echo $this->loadTemplate('thead'); ?>
            <?php if($hasState) : ?>
            <th width="5%" class="title">
                <?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_PUBLISHED', $this->fields['state_field'], $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
            <?php endif; ?>
            <?php if($hasOrdering && (YireoHelper::isJoomla15() || YireoHelper::isJoomla25())) : ?>
            <th width="8%" nowrap="nowrap">
                <?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_ORDERING', $this->fields['ordering_field'], $this->lists['order_Dir'], $this->lists['order'] ); ?>
                <?php echo JHTML::_('grid.order', $this->items ); ?>
            </th>
            <?php endif; ?>
            <th width="5">
                <?php echo JHTML::_('grid.sort', 'LIB_YIREO_TABLE_FIELDNAME_ID', $this->fields['primary_field'], $this->lists['order_Dir'], $this->lists['order'] ); ?>
            </th>
        </tr>
    </thead>
    <?php if($this->pagination) : ?>
    <tfoot>
        <tr>
            <td colspan="100">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        <?php echo $this->loadTemplate('legend'); ?>
    </tfoot>
    <?php endif; ?>
    <tbody>
    <?php
    $i = 0;
    if (!empty($this->items)) {
        foreach($this->items as $item) {

            // Construct the checkbox
            if(isset($item->hasCheckbox) && $item->hasCheckbox == false) {
                $checkbox = $this->getImageTag('disabled.png');
            } else {
                $checkbox = $this->checkbox($item, $i);
            }

            // Construct the published-field
            if(isset($item->hasState) && $item->hasState == false) {
                $published = $this->getImageTag('disabled.png');
            } else {
                $published = ($hasState == true) ? $this->published($item, $i, $this->getModel()) : true;
            }

            // Construct the published-field
            if(isset($item->hasOrdering) && $item->hasOrdering == false) {
                $ordering = false;
            } else {
                $ordering = ($this->lists['order'] == $this->fields['ordering_field']);
                $orderingField = $this->fields['ordering_field'];
            }

            // Determine whether to automatically insert common columns or not
            $auto_columns = true;
            ?>
            <tr class="<?php echo "row".($i%2); ?>">
                <td>
                    <?php echo $i+1; ?>
                </td>
                <td>
                    <?php echo $checkbox; ?>
                </td>

                <?php echo $this->loadTemplate('tbody', array('item' => $item, 'auto_columns' => $auto_columns, 'published' => $published)); ?>

                <?php if($auto_columns): ?>
                <?php if($hasState) : ?>
                <td>
                    <?php echo $published; ?>
                </td>
                <?php endif; ?>
                <?php if($hasOrdering && (YireoHelper::isJoomla15() || YireoHelper::isJoomla25())) : ?>
                <td class="order">
                    <?php if(isset($item->hasOrdering) && $item->hasOrdering == false) : ?>
                        <?php echo $this->getImageTag('disabled.png'); ?>
                    <?php else: ?>
                        <?php if($this->pagination) : ?>
                            <span><?php echo $this->pagination->orderUpIcon( $i, true,'orderup', 'Move Up', $ordering ); ?></span>
                            <span><?php echo $this->pagination->orderDownIcon( $i, 0, true, 'orderdown', 'Move Down', $ordering ); ?></span>
                        <?php endif; ?>
                        <?php $disabled = ($ordering) ?  '' : 'disabled="disabled"'; ?>
                        <input type="text" name="order[]" size="5" value="<?php echo $item->$orderingField;?>" <?php echo $disabled ?> class="ordering" style="text-align: center" />
                    <?php endif; ?>
                </td>
                <?php endif; ?>
                <td>
                    <?php echo $item->id; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php
            $i++;
        }
    } else {
        ?>
        <tr>
            <td colspan="100">
                <?php echo JText::_('LIB_YIREO_VIEW_LIST_NO_ITEMS') ; ?>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
    </table>
</div>

<?php echo $this->loadTemplate('formend'); ?>
</form>
