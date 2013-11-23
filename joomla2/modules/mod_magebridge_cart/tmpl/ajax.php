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

<script language="javascript" type="text/javascript">
<?php echo MageBridgeAjaxHelper::getScript('cart_sidebar', 'magebridge-cart'); ?>
</script>

<div id="magebridge-cart" class="magebridge-module">
    <center><img src="<?php echo MageBridgeAjaxHelper::getLoaderImage(); ?>" /></center>
</div>
<div style="clear:both"></div>
