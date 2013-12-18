<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2013
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
<div id="logo" class="shadedbox">
    <p><a href="http://www.yireo.com/" target="_new"><img src="../media/<?php echo JRequest::getCmd('option'); ?>/images/yireo.png" /></a></p>
    <p> 
        Follow us on twitter: <a href="http://twitter.com/yireo">@yireo</a><br/>
        Connect with us on <a href="http://www.facebook.com/yireo">our Facebook page</a>
    </p>
    <p>
        <?php echo $this->loadTemplate('review'); ?>
    </p>
</div>
<?php echo $this->loadTemplate('version'); ?>

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
