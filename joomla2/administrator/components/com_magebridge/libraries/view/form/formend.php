<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.5.1
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<input type="hidden" name="option" value="<?php echo $this->_option; ?>" />
<input type="hidden" name="view" value="<?php echo $this->_view; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_('form.token'); ?>
