<?php 
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');

$form = $this->actions_form;
?>
<table class="admintable">
<?php foreach($form->getFieldset('actions') as $field): ?>
    <tr>
        <td class="key"><?php echo $field->label; ?></td>
        <td class="value"><?php echo $field->input; ?></td>
    </tr>
<?php endforeach; ?>
</table>
