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
<?php if(isset($this->urls['facebook'])) : ?>
<div class="facebook">
    <?php echo JText::_('LIB_YIREO_VIEW_HOME_FACEBOOK'); ?>:<br/>
    <a href="<?php echo $this->urls['facebook']; ?>"><?php echo $this->urls['facebook']; ?></a>
</div>
<?php endif; ?>
