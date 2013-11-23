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

<p class="notice"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_ELEMENT_SELECT_CATEGORY'); ?></p>

<form method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
    <td align="left" width="80%">
        <?php echo JText::_( 'Store' ); ?>:
        <?php echo $this->lists['store'];?>
        <?php echo JText::_( 'Filter' ); ?>:
        <input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>"
        class="text_area" onchange="document.adminForm.submit();" />
        <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
        <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>
    </td>
    <td align="right" width="20%">
        <?php $js = "window.parent.jSelectCategory('', '', '".JRequest::getVar('object')."');"; ?>
        <button onclick="<?php echo $js; ?>"><?php echo JText::_('No category'); ?></button>
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
        <td colspan="5">
            <?php echo $this->pagination->getListFooter(); ?>
        </td>
    </tr>
</tfoot>
<tbody>
<?php 
if (!empty($this->categories)) {
        
    if (JRequest::getCmd('allow_root') == 1) {
        $allowRoot = true;
    } else {
        $allowRoot = false;
    }

    $i = 0;
    foreach ($this->categories as $category) {

        if (JRequest::getCmd('return') == 'id') {
            $return = 'category_id';
        } else if (JRequest::getCmd('return') == 'url_key') {
            $return = 'url_key';
        } else if (!empty($category['url'])) {
            $return = 'url';
        } else {
            $return = 'category_id';
        }

        $css = array();
        if (isset($category[$return]) && JRequest::getCmd('current') == $category[$return]) {
            $css[] = 'current';
        }

        if (isset($category['status']) && $category['status'] == 0) {
            $css[] = 'inactive';
        } else {
            $css[] = 'active';
        }

        $category_name = htmlspecialchars(str_replace("'", '', $category['name']));
        $jsDefault = "window.parent.jSelectCategory('".$category[$return]."', '$category_name', '".JRequest::getVar('object')."');";
        $jsUrl = "window.parent.jSelectCategory('".$category['url']."', '$category_name', '".JRequest::getVar('object')."');";
        $jsId = "window.parent.jSelectCategory('".$category['category_id']."', '$category_name', '".JRequest::getVar('object')."');";
        ?>
        <tr class="<?php echo implode(' ', $css); ?>">
            <td>
                <?php echo $this->pagination->getRowOffset( $i ); ?>
            </td>
            <td>
                <?php if (!empty($category['indent'])) { ?><?php echo $category['indent']; ?> &nbsp; &nbsp;<?php } ?>
                <?php if ($allowRoot || $category['level'] > 1) { ?>
                <a style="cursor: pointer;" onclick="<?php echo $jsDefault; ?>"><?php echo $category['name']; ?></a>
                <?php } else { ?>
                <?php echo $category['name']; ?>
                <?php } ?>
            </td>
            <td>
                <?php if (!empty($category['url'])) { ?>
                <a style="cursor: pointer;" onclick="<?php echo $jsUrl; ?>">
                    <?php echo $category['url']; ?>
                </a>
                <?php } ?>
            </td>
            <td>
                <?php echo ($category['is_active'] ? JText::_('Yes') : JText::_('No')); ?>
            </td>
            <td>
                <?php if (!empty($category['category_id'])) { ?>
                <a style="cursor: pointer;" onclick="<?php echo $jsId; ?>">
                    <?php echo $category['category_id']; ?>
                </a>
                <?php } ?>
            </td>
        </tr>
        <?php 
        $i++;
    } 
} else {
    ?>
    <tr>
        <td colspan="5"><?php echo JText::_('No categories found'); ?></td>
    </tr>
    <?php
}
?>
</tbody>
</table>
<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="element" />
<input type="hidden" name="type" value="category" />
<input type="hidden" name="object" value="<?php echo $this->object; ?>" />
<input type="hidden" name="current" value="<?php echo $this->current; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
