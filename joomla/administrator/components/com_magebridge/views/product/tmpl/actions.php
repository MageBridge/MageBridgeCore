<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');

$form = $this->actions_form;
$fieldsetCount = count($form->getFieldsets('actions'));
?>
<?php if($fieldsetCount > 0) : ?>
<?php foreach($form->getFieldsets('actions') as $fieldset): ?>
<?php $fieldCount = count($form->getFieldset($fieldset->name)); ?>
<?php if($fieldCount == 0) {
    continue;
} ?>
<fieldset class="adminform">
<legend>
	<?php echo JText::_('COM_MAGEBRIDGE_VIEW_PRODUCT_FIELDSET_ACTIONS'); ?>: 
	<?php echo (!empty($fieldset->label)) ? JText::_($fieldset->label) : $fieldset->name; ?>
</legend>
	<?php foreach($form->getFieldset($fieldset->name) as $field): ?>
    <div class="row-fluid form-group" style="margin-bottom:5px;">
        <div class="span4 col-md-4">
		    <?php echo $field->label; ?>
        </div>
        <div class="span8 col-md-8">
		    <?php echo $field->input; ?>
        </div>
	</div>
	<?php endforeach; ?>
</fieldset>
<?php endforeach; ?>
<?php else: ?>
<p><?php echo JText::_('COM_MAGEBRIDGE_PRODUCT_NO_PLUGINS'); ?></p>
<?php endif; ?>

<style>
select.form-control {
    min-width: 100%;
    width:auto !important;
}
</style>
