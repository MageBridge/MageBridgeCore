<?php
/**
 * Joomla! module MageBridge: Shopping Cart
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
<div id="magebridge-cart" class="magebridge-module">
    <?php if (isset($data['items']) && is_array($data['items']) && !empty($data['items'])) { ?>
        <p><?php echo JText::sprintf('MOD_MAGEBRIDGE_CART_ITEMS_COUNT', $data['items_count']); ?></p>
        <ul>
        <?php foreach ($data['items'] as $item) { ?>
            <li style="height:100px;"> 
                <?php $url = MageBridgeUrlHelper::route($item['url_path']); ?>
                <a href="<?php echo $url; ?>" title="<?php echo $item['name']; ?>"><img src="<?php echo $item['thumbnail']; ?>" alt="<?php echo $item['name']; ?>" title="<?php echo $item['name']; ?>" align="left" /></a>
                <p>
                    <a href="<?php echo $url; ?>" title="<?php echo $item['name']; ?>"><?php echo $item['name']; ?></a>
                    <?php echo $item['price']; ?>
                </p>
            </li> 
        <?php } ?>
        </ul>
        <p><?php echo JText::_('MOD_MAGEBRIDGE_CART_SUBTOTAL'); ?>: <?php echo $data['subtotal_formatted']; ?></p>
        <p><a href="<?php echo $data['cart_url']; ?>"><?php echo JText::_('MOD_MAGEBRIDGE_CART_CHECKOUT'); ?></a></p>
    <?php } else { ?>
        <?php echo JText::_('MOD_MAGEBRIDGE_CART_NO_ITEMS'); ?>
    <?php } ?>
</div>
<div style="clear:both"></div>
