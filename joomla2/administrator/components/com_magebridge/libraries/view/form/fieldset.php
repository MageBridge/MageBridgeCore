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

$form = $this->form;
if(!empty($form)):
?>
<fieldset class="adminform">
<legend><?php echo JText::_('LIB_YIREO_VIEW_FORM_FIELDSET_'.$fieldset); ?></legend>
<?php foreach($form->getFieldset($fieldset) as $field): ?>
<div class="row-fluid">
    <div class="span4"><?php echo $field->label; ?></div>
    <div class="span8"><?php echo $field->input; ?></div>
    </tr>
</div>
<?php endforeach; ?>
</fieldset>
<?php else: ?>
<p>No form loaded</p>
<?php endif; ?>
