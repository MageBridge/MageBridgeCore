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
<?php foreach ($this->icons as $icon) { ?>
<div style="float:left">
    <div class="icon">
        <a href="<?php echo $icon['link']; ?>" target="<?php echo $icon['target']; ?>"><?php echo $icon['icon']; ?><span><?php echo $icon['text']; ?></span></a>
    </div>
</div>
<?php } ?>
<div style="clear:both;" />
