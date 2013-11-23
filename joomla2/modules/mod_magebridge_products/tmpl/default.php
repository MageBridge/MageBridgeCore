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
<div class="magebridge-products magebridge-module">
<?php if (!empty($products) && is_array($products)) : ?>
    <?php foreach ($products as $product) : ?>
    <div>
        <?php if ($params->get('show_title',1)) : ?>
            <a href="<?php echo $product['url']; ?>"><h3><?php echo $product['name']; ?></h3></a>
        <?php endif; ?>

        <?php if ($params->get('show_short_description',1)) : ?>
            <p><?php echo $product['short_description']; ?></p>
        <?php endif; ?>

        <?php if ($params->get('show_description',1)) : ?>
            <p><?php echo $product['description']; ?></p>
        <?php endif; ?>

        <?php if ($params->get('show_price',1)) : ?>
            <?php if ($product['has_special_price'] && $params->get('special_price', 1) != 0): ?>
                <?php if ($params->get('special_price') == 2): ?>
                <p><span class="normal_price_with_special"><?php echo $product['price']; ?></span></p>
                <p><span class="special_price"><?php echo $product['special_price']; ?></span></p>
                <?php else: ?>
                <p><span><?php echo $product['special_price']; ?></span></p>
                <?php endif; ?>
                <?php if ($product['special_percentage'] > 0): ?>
                <p><?php echo round($product['special_percentage']); ?>% <?php echo JText::_('MOD_MAGEBRIDGE_PRODUCTS_DISCOUNT'); ?></p>
                <?php endif;?>
            <?php elseif ($product['has_final_price'] && $params->get('final_price', 1) != 0): ?>
                <?php if ($params->get('final_price') == 2): ?>
                <p><span class="normal_price_with_final"><?php echo $product['price']; ?></span></p>
                <p><span class="final_price"><?php echo $product['final_price']; ?></span></p>
                <?php else: ?>
                <p><span><?php echo $product['final_price']; ?></span></p>
                <?php endif; ?>
            <?php else: ?>
                <p><span><?php echo $product['price']; ?></span></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($params->get('show_thumb',1)) : ?>
            <?php $thumb = $params->get('thumb', 'thumbnail'); ?>
            <p><a href="<?php echo $product['url']; ?>" title="<?php echo $product['label']; ?>"><img src="<?php 
                echo $product[$thumb]; ?>" title="<?php echo $product['label']; ?>" alt="<?php 
                echo $product['label']; ?>" /></a></p>
        <?php endif; ?>

        <?php if ($params->get('show_readmore',1) || $params->get('show_addtocart')) : ?>
            <ul>
            <?php if ($params->get('show_readmore',1)) : ?>
                <li><a href="<?php echo $product['url']; ?>" title="<?php echo $product['readmore_label']; ?>"><?php echo $product['readmore_text']; ?></a></li>
            <?php endif; ?>
            <?php if ($params->get('show_addtocart',1)) : ?>
                <li><a href="<?php echo $product['addtocart_url']; ?>" title="<?php echo $product['addtocart_label']; ?>"><?php echo $product['addtocart_text']; ?></a></li>
            <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php endforeach ?>
    <?php if($params->get('show_category_readmore', 0) == 1 && !empty($category)) : ?>
    <p><a title="<?php echo $category['name']; ?>" href="<?php echo $category['url']; ?>"><?php echo $category['readmore']; ?></a></p>
    <?php endif; ?>
<?php else: ?>
    <?php if ($params->get('show_noitems',1)) : ?>
        <?php echo JText::_( 'MOD_MAGEBRIDGE_PRODUCTS_NO_PRODUCTS' ); ?>
    <?php endif; ?>
<?php endif; ?>
</div>
