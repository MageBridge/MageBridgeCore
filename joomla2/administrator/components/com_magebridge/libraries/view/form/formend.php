<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<input type="hidden" name="option" value="<?php echo $this->_option; ?>" />
<input type="hidden" name="view" value="<?php echo $this->_view; ?>" />
<?php if(isset($this->item->id)) : ?>
<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
<?php endif; ?>
<input type="hidden" name="task" value="<?php echo $this->_task; ?>" />
<?php echo JHTML::_('form.token'); ?>
