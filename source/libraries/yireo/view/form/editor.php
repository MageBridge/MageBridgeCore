<?php 
/**
 * Joomla! Yireo Lib
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright (C) 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$field = $this->getEditorField();
if(!empty($field)) {
?>
<fieldset class="adminform">
    <legend><?php echo JText::_('LIB_YIREO_TABLE_FIELDNAME_'.strtoupper($field)); ?></legend>
    <table class="admintable" width="100%">
    <tbody>
    <tr>
        <td class="value">
            <?php
            $editor = JFactory::getEditor();
            $value = $this->item->$field;
            echo @$editor->display($field, $value, '100%', '300', '44', '9', array('pagebreak', 'readmore' )) ;
            ?>
        </td>
    </tr>
    </tbody>
    </table>
</fieldset>
<?php } ?>
