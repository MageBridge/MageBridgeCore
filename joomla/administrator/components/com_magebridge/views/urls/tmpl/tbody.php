<?php 
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>
<td>
	<a href="<?php echo $item->edit_link; ?>" title="<?php echo JText::_( 'Edit URL replacement' ); ?>"><?php echo $item->source; ?></a>
</td>
<td>
	<?php echo $item->destination; ?>
</td>
