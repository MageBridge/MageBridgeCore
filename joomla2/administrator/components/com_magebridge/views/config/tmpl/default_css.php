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
<legend><?php echo JText::_('CSS settings'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Disable_CSS_Mage'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_css']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_CSS_MAGE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Disable_default_CSS'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_default_css']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_DEFAULT_CSS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

