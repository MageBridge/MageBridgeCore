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

// Set the right image directory for JavaScipt
jimport('joomla.utilities.utility');
?>
<?php echo $this->loadTemplate('script'); ?>

<form method="post" name="adminForm" id="adminForm">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="70%" valign="top">
    <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'LIB_YIREO_VIEW_FIELDSET_DETAILS', 'fields' => $this->fields)); ?>
    <?php echo $this->loadTemplate('editor'); ?>
</td>
<td width="30%" valign="top">
    <?php echo $this->loadTemplate('params'); ?>
</td>
</tr>
</tbody>
</table>
<?php echo $this->loadTemplate('formend'); ?>
</form>
