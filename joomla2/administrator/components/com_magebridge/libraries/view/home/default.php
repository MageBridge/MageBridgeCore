<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">
<table id="adminform" width="100%">
<tr>
<td width="60%" valign="top">

<div id="cpanel">
<?php echo $this->loadTemplate('cpanel'); ?>
</div>
<div id="yireo-logo" class="shadedbox">
    <a href="http://www.yireo.com/" target="_new"><img src="../media/<?php echo JRequest::getCmd('option'); ?>/images/yireo.png" align="left" /></a>
    <h3><?php echo JText::_('LIB_YIREO_VIEW_HOME_SLOGAN'); ?></h3>
</div>
<div class="details">
    <p> 
        <?php echo $this->loadTemplate('details'); ?>
    </p>
</div>

</td>
<td width="40%" valign="top" style="margin-top:0; padding:0">
<?php echo $this->loadTemplate('ads'); ?>
</td>
</tr>
</table>
<input type="hidden" name="option" value="<?php echo $this->_option; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
