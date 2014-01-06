<?php
/*
 * Joomla! Yireo Lib
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php if(!empty($this->paramsForm)) : ?>
<?php $form = $this->paramsForm; ?>
<table class="admintable">
<?php foreach($form->getFieldset('params') as $field): ?>
    <tr>
        <td class="key"><?php echo $field->label; ?></td>
        <td class="value"><?php echo $field->input; ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
