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
<table class="admintable">
<?php if(!empty($this->params_form)) : ?>
<?php foreach($this->params_form->getFieldset('params') as $field): ?>
    <tr>
        <td class="key"><?php echo $field->label; ?></td>
        <td class="value"><?php echo $field->input; ?></td>
    </tr>
<?php endforeach; ?>
<?php else: ?>
    <tr>
        <td>
            <?php echo JText::_('No parameters'); ?>
        </td>
    </tr>
<?php endif; ?>
</table>
