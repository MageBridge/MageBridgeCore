<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (https://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com/
 * @version 0.6.0
 */

defined('_JEXEC') or die('Restricted access');
?>
<form method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
    <div class="span7">
        <?php echo $this->loadTemplate('cpanel'); ?>
        <?php echo $this->loadTemplate('logo'); ?>
        <?php echo $this->loadTemplate('details'); ?>
    </div>
    <div class="span5">
        <?php echo $this->loadTemplate('ads'); ?>
    </div>
</div>

<input type="hidden" name="option" value="<?php echo $this->_option; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_( 'form.token' ); ?>
</form>
