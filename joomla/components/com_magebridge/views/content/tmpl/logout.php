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

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php if ($this->params->get('intermediate_page') != 1 && !empty($this->block)) { ?>

<div id="magebridge-content">
	<?php echo $this->block; ?>
</div>
<div style="clear:both"></div>

<?php } else { ?>

<div id="magebridge-content">
<div class="page-head">
	<h3><?php echo $this->escape($this->params->get('page_title')); ?></h3>
</div>
<p><?php echo $this->escape($this->params->get('page_text')); ?></p>
<form action="<?php echo $this->logout_url; ?>" method="post" name="logout" id="logout">
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('COM_MAGEBRIDGE_LOGOUT'); ?>" />
</form>
</div>
<div style="clear:both"></div>

<?php } ?>
