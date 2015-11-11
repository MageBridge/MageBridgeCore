<?php 
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');

$form = $this->actions_form;
$fieldsetCount = count($form->getFieldsets('actions'));
?>
<?php if($fieldsetCount > 0) : ?>
<?php foreach($form->getFieldsets('actions') as $fieldset): ?>
<?php $fieldCount = count($form->getFieldset($fieldset->name)); ?>
<?php if($fieldCount == 0) continue; ?>
<fieldset class="adminform">
<legend>
	<?php echo JText::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELDSET_ACTIONS'); ?>: 
	<?php echo (!empty($fieldset->label)) ? JText::_($fieldset->label) : $fieldset->name; ?>
</legend>
<table class="admintable">
	<?php foreach($form->getFieldset($fieldset->name) as $field): ?>
	<tr>
		<td class="key"><?php echo $field->label; ?></td>
		<td class="value"><?php echo $field->input; ?></td>
	</tr>
	<?php endforeach; ?>
</table>
</fieldset>
<?php endforeach; ?>
<?php else: ?>
<p><?php echo JText::_('COM_MAGEBRIDGE_PRODUCT_NO_PLUGINS'); ?></p>
<?php endif; ?>
