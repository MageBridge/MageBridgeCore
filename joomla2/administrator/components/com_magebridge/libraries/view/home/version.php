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
<div class="version">
    <?php if(isset($this->current_version)) : ?>
    <?php echo JText::sprintf('LIB_YIREO_VIEW_HOME_CURRENTVERSION', $this->current_version); ?><span style="float:left;" id="latest_version"></span><br/>
    <?php endif; ?>

    <?php if(isset($this->urls['tutorial'])) : ?>
    <?php echo JText::_('LIB_YIREO_VIEW_HOME_TUTORIALS'); ?>:<br/>
    <a href="<?php echo $this->urls['tutorials']; ?>"><?php echo $this->urls['tutorials']; ?></a>
    <?php endif; ?>
</div>
