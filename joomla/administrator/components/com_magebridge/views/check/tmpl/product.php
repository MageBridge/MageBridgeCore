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

defined('_JEXEC') or die('Restricted access');
?>

<style>
</style>

<form method="post" name="adminForm" id="adminForm">
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_PRODUCT_RELATION_TEST'); ?></legend>
	<?php echo $this->loadTemplate('fieldset', array('fieldset' => 'basic')); ?>
	<?php echo $this->loadTemplate('formend'); ?>
</fieldset>
</form>
