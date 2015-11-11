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
$form = $this->form;
?>
<form method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-horizontal">

<ul class="nav nav-tabs" id="configTabs">
<?php $i = 0; ?>
<?php foreach($form->getFieldsets() as $fieldset): ?>
	<li><a href="#<?php echo $fieldset->name;?>" data-toggle="tab" class="<?php if($i == 0) echo 'active'; ?>"><?php echo JText::_($fieldset->label);?></a></li>
	<?php $i++; ?>
<?php endforeach; ?>
</ul>

<div class="span10">
	<div class="tab-content">
	<?php foreach($form->getFieldsets() as $fieldset): ?>
		<?php echo $this->printFieldset($form, $fieldset); ?>
	<?php endforeach; ?>
	</div>
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="config" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script type="text/javascript">
	jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>
