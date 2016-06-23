<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<?php if(YireoHelper::isJoomla25()) : ?>
<div class="filter-search">
    <label class="filter-search-lbl" for="search"><?php echo JText::_('LIB_YIREO_VIEW_FILTER'); ?></label>
    <input type="text" name="<?php echo $this->lists['search_name']; ?>" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area"
onchange="document.adminForm.submit();" />
    <button class="btn" onclick="this.form.submit();"><?php echo JText::_('LIB_YIREO_VIEW_SEARCH'); ?></button>
    <button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('LIB_YIREO_VIEW_RESET'); ?></button>
</div>
<?php else: ?>
<div class="btn-wrapper input-append">
    <input type="text" name="<?php echo $this->lists['search_name']; ?>" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area"
onchange="document.adminForm.submit();" />
    <button class="btn" onclick="this.form.submit();"><i class="icon-search"></i></button>
</div>
<div class="btn-wrapper">
    <button class="btn" onclick="jQuery('#search').value='';this.form.submit();"><?php echo JText::_('LIB_YIREO_VIEW_RESET'); ?></button>
</div>
<?php endif; ?>
