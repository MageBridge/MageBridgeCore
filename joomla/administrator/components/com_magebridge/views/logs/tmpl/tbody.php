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

$message = $item->message;
if (strlen($message) > 100) {
	$message = substr($message, 0, 97).'...';
}
?>
<td>
	<span title="<?php echo htmlentities($item->message); ?>">
		<?php echo htmlspecialchars($message); ?>
	</span>
</td>
<td>
	<?php echo $this->printType($item->type); ?>
</td>
<td>
	<?php echo JText::_($item->origin); ?>
</td>
<td>
	<?php echo $item->remote_addr; ?>
</td>
<td>
	<?php echo $item->session; ?>
</td>
<td>
	<?php echo $item->timestamp; ?>
</td>
