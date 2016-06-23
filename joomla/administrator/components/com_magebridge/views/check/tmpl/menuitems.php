<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');

$items = MageBridgeUrlHelper::getRootItems(false);
?>
<h3>MageBridge Root Menu-Items</h3>
<table class="table table-striped">
	<thead>
	<tr>
		<th>
			ID
		</th>
		<th>
			Title
		</th>
		<th>
			Menu
		</th>
		<th>
			Access
		</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($items as $item): ?>
	<tr>
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo $item->title; ?>
		</td>
		<td>
			<?php echo $item->menutype; ?>
		</td>
		<td>
			<?php echo $item->access; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>
