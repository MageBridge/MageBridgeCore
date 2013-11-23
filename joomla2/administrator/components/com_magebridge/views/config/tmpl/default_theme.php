<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<fieldset class="adminform">
<legend><?php echo JText::_('Flush module positions'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_home'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_home" value="<?php echo $this->config['flush_positions_home']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_HOME_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_customer'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_customer" value="<?php echo $this->config['flush_positions_customer']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_CUSTOMER_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_product'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_product" value="<?php echo $this->config['flush_positions_product']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_PRODUCT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_category'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_category" value="<?php echo $this->config['flush_positions_category']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_CATEGORY_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_cart'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_cart" value="<?php echo $this->config['flush_positions_cart']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_CART_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Flush_positions_checkout'); ?>
        </td>
        <td class="value">
            <input type="text" name="flush_positions_checkout" value="<?php echo $this->config['flush_positions_checkout']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'FLUSH_POSITIONS_CHECKOUT_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('Other settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Joomla! template'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['template']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'TEMPLATE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Mobile Joomla! template'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['mobile_joomla_theme']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'MOBILE_JOOMLA_THEME_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Mobile Magento theme'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['mobile_magento_theme']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'MOBILE_MAGENTO_THEME_DESCRIPTION' ); ?></span>
        </td>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Module_chrome'); ?>
        </td>
        <td class="value">
            <input type="text" name="module_chrome" value="<?php echo $this->config['module_chrome']['value']; ?>" size="30" />
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'MODULE_CHROME_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
