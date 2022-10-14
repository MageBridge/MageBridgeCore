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

$form = $this->params_form;
?>
<?php foreach($form->getFieldset('params') as $field): ?>
<div class="row-fluid form-group" style="margin-bottom:5px;">
    <div class="span4 col-md-4">
		<?php echo $field->label; ?>
    </div>
    <div class="span4 col-md-4">
		<?php echo $field->input; ?>
    </div>
</div>
<?php endforeach; ?>
