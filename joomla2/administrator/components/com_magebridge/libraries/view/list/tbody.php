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
?>
<td>
    <?php if ($this->isCheckedOut($item)) { ?>
        <?php echo $this->checkedout($item, $i); ?>
        <span class="checked_out"><?php echo $item->title; ?></span>
    <?php } else { ?>
        <a href="<?php echo $item->edit_link; ?>" title="<?php echo JText::_( 'Edit Item' ); ?>"><?php echo $item->title; ?></a>
    <?php } ?>
</td>
