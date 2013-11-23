<?php
/**
 * Joomla! module MageBridge: Products
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="magebridge-products">
<?php if (!empty($products) && is_array($products)) { ?>
    <ul>
    <?php foreach ($products as $product) { ?>
        <li><a href="<?php echo $product['url']; ?>"><?php echo $product['name']; ?></a></li>
    <?php } ?>
    </ul>
<?php } else { ?>
    <?php if ($params->get('show_noitems',1)) : ?>
        <?php echo JText::_( 'MOD_MAGEBRIDGE_PRODUCTS_NO_PRODUCTS' ); ?>
    <?php endif; ?>
<?php } ?>
</div>
