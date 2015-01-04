<?php
/*
 * Joomla! Yireo Lib
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

defined('_JEXEC') or die('Restricted access');

$form = $this->form;
$fieldsetObject = (object)null;
foreach($form->getFieldsets() as $fieldsetCode => $fieldsetObject) {
    if($fieldset == $fieldsetCode) {
        break;
    }
}

?>
<?php if(!empty($form)): ?>
    <?php if(!empty($fieldset)): ?>
        <?php if(empty($legend)) $legend = JText::_('LIB_YIREO_VIEW_FORM_FIELDSET_'.$fieldset); ?>
        <fieldset class="adminform">
            <legend><?php echo $legend; ?></legend>

            <?php if(!empty($fieldsetObject->description)) : ?>
                <div class="fieldset-description"><?php echo $fieldsetObject->description; ?></div>
            <?php endif; ?>

            <?php foreach($form->getFieldset($fieldset) as $field): ?>
                <?php $fieldType = strtolower((string)$field->type); ?>
                <?php if($fieldset == 'editor' || in_array($fieldType, array('textarea', 'editor'))): ?>
                    <div class="row-fluid">
                        <div class="span12">
                            <?php echo $field->label; ?>
                            <?php echo $field->input; ?>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="row-fluid">
                        <div class="span4"><?php echo $field->label; ?></div>
                        <div class="span8"><?php echo $field->input; ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </fieldset>
    <?php endif; ?>
<?php else: ?>
    <p>No form loaded</p>
<?php endif; ?>
