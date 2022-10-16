<?php
/**
 * Joomla! module MageBridge: Category Menu
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Start level
$startLevel = 1;

// Only declare this function once
if (!function_exists('MageBridgeMenuPrintTree')) {
    function MageBridgeMenuPrintTree($tree, $level = 0, $params = null)
    {
        // If the tree is invalid, do not continue
        if (!is_array($tree) || count($tree) == 0) {
            return;
        }
        $i = 0;
        ?>
		<ul>
		<?php foreach ($tree as $item) : ?>
			<?php $class = ModMageBridgeMenuHelper::getCssClass($params, $item, $level, $i, $tree); ?>
			<li class="<?php echo $class; ?>">
				<a href="<?php echo $item['url']; ?>" class="<?php echo $class; ?>"><span><?php echo $item['name']; ?></span></a>
				<?php if ($params->get('include_product_count') == 1 && isset($item['product_count'])) { ?>(<?php echo (int)$item['product_count']; ?>)<?php } ?>
				<?php if ($item['is_active']) {
				    echo MageBridgeMenuPrintTree($item['children'], $level + 1, $params);
				} ?>
		</li>
		<?php $i++; ?>
		<?php endforeach; ?>
		</ul>
		<?php
    }
}
?>

<?php if (is_array($catalog_tree) && count($catalog_tree) > 0): ?>
<ul class="menu<?php echo $params->get('class_sfx'); ?>">
<?php foreach ($catalog_tree as $item) : ?>
	<?php $i = 0; ?>
	<?php $class = ModMageBridgeMenuHelper::getCssClass($params, $item, $startLevel, $i, $catalog_tree); ?>
	<li class="<?php echo $class; ?>">
		<a href="<?php echo $item['url']; ?>" class="<?php echo $class; ?>"><span><?php echo $item['name']; ?></span></a>
		<?php if ($params->get('include_product_count') == 1 && isset($item['product_count'])) { ?>(<?php echo (int)$item['product_count']; ?>)<?php } ?>
		<?php if ($item['is_active']) {
		    echo MageBridgeMenuPrintTree($item['children'], $startLevel + 1, $params);
		} ?>
	</li>
	<?php $i++; ?>
<?php endforeach; ?>
</ul>
<?php endif; ?>