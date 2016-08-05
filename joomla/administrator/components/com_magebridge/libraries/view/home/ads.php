<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

defined('_JEXEC') or die('Restricted access');
?>
<div id="promotion" class="well">
	<?php if ($this->backend_feed == 1) : ?>
		<div class="loader"/>
	<?php else : ?>
		<?php echo JText::_('LIB_YIREO_VIEW_HOME_ADS_DISABLED'); ?>
	<?php endif; ?>
</div>

</div>
<div id="latest_news" class="well">
	<?php if ($this->backend_feed == 1) : ?>
		<div class="loader"/>
	<?php else : ?>
		<?php echo JText::_('LIB_YIREO_VIEW_HOME_BLOG_DISABLED'); ?>
	<?php endif; ?>
</div>
