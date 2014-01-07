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
    <a href="<?php echo $item->edit_link; ?>" title="<?php echo JText::_( 'Edit usergroup relation' ); ?>"><?php echo $item->description; ?></a>
</td>
<td>
    <?php if(!empty($item->joomla_group_label)) : ?>
    <?php echo $item->joomla_group_label; ?> (ID <?php echo $item->joomla_group; ?>)
    <?php else: ?>
    (ID <?php echo $item->joomla_group; ?>)
    <?php endif; ?>
</td>
<td>
    <?php if(!empty($item->magento_group_label)) : ?>
    <?php echo $item->magento_group_label; ?> (ID <?php echo $item->magento_group; ?>)
    <?php else: ?>
    (ID <?php echo $item->magento_group; ?>)
    <?php endif; ?>
</td>
