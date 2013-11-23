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
<div id="magebridge-switcher" class="magebridge-module">
    <?php if (!empty($select)) { ?>
    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="magebridge-switcher" id="mbswitcher">
    <?php echo $select; ?>
    <input type="hidden" name="option" value="com_magebridge" />
    <input type="hidden" name="task" value="switch" />
    <input type="hidden" name="redirect" value="<?php echo $redirect_url ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <?php } ?>
</div>
