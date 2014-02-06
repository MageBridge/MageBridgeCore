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
<i class="fa fa-twitter-square fa-lg"></i> <?php echo JText::_('LIB_YIREO_VIEW_HOME_TWITTER'); ?>: <a href="http://twitter.com/yireo">@yireo</a><br/>
<i class="fa fa-facebook-square fa-lg"></i> <?php echo JText::_('LIB_YIREO_VIEW_HOME_FACEBOOK'); ?>: <a href="http://www.facebook.com/yireo">facebook.com/yireo</a><br/>

<?php if(isset($this->urls['jed'])) : ?>
<i class="fa fa-comments fa-lg"></i> <?php echo JText::sprintf('LIB_YIREO_VIEW_HOME_VOTE', YireoHelper::getData('title')); ?>: <a href="<?php echo $this->urls['jed']; ?>"><?php echo JText::_('LIB_YIREO_VIEW_HOME_JED'); ?></a><br/>
<?php endif; ?>

<?php if(isset($this->current_version)) : ?>
<i class="fa fa-certificate fa-lg"></i> <?php echo JText::sprintf('LIB_YIREO_VIEW_HOME_CURRENTVERSION', $this->current_version); ?><br/>
<?php endif; ?>

<?php if(isset($this->urls['tutorial'])) : ?>
<i class="fa fa-book fa-lg"></i> <?php echo JText::_('LIB_YIREO_VIEW_HOME_TUTORIALS'); ?>: <a href="<?php echo $this->urls['tutorials']; ?>"><?php echo $this->urls['tutorials']; ?></a>
<?php endif; ?>
