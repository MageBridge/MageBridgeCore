<?php
/**
 * Joomla! Form Field Template - Select Improved
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();
        
?>
<select <?php echo $arguments; ?>>
    <?php if ($show_empty == true && $show_empty_below == false): ?>
    <option><?php echo (!empty($empty_label)) ? JText::_($empty_label) : null; ?></option>
    <?php endif; ?>
    <?php foreach($options as $option): ?>
    <?php $selected = ($option['value'] == $current_value) ? ' selected="selected" ' : null; ?>
    <?php $label = $option['value']; ?>
    <?php if(isset($option['label'])) $label = $option['label']; ?>
    <?php if(isset($option['title'])) $label = $option['title']; ?>
    <?php $attributes = (!empty($option['attributes'])) ? ' '.implode(' ',$option['attributes']).' ' : null; ?>
    <option value="<?php echo $option['value']; ?>"<?php echo $selected; ?><?php echo $attributes; ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
    <?php if ($show_empty == true && $show_empty_below == true): ?>
    <option><?php echo (!empty($empty_label)) ? JText::_($empty_label) : null; ?></option>
    <?php endif; ?>
</select>
