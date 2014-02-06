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
<table id="adminform" width="100%">
    <?php if (!empty($this->feeds)) { ?>
    <?php foreach ($this->feeds as $feed) { ?>
    <tr>
    <td>
        <a target="_new" href="<?php echo $feed['link']; ?>"><h4><?php echo $feed['title']; ?></h4></a>
        <?php echo $feed['description']; ?>
    </td>
    </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
    <td>
        <?php echo JText::_('LIB_YIREO_VIEW_FEED_ERROR'); ?>
    </td>
    </tr>
    <?php } ?>
</table>
