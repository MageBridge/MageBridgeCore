<?php 
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>
<td>
    <a href="<?php echo $item->edit_link; ?>" title="<?php echo JText::_( 'Edit connector' ); ?>"><?php echo $item->title; ?></a>
</td>
<td>
    <?php echo $item->name; ?>
</td>
<td>
    <?php echo $item->type; ?>
</td>
<td>
    <?php echo $item->filename; ?>
</td>
