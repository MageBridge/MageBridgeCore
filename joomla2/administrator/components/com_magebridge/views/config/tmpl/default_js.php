<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<fieldset class="adminform">
<legend><?php echo JText::_('JavaScript settings for Joomla!'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Disable_JS_All'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_all']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_ALL_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key vital" valign="top">
            <?php echo JText::_('Disable_JS_Mootools'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_mootools']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_MOOTOOLS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Disable_JS_JQuery'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_jquery']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_JQUERY_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('JavaScript settings for Magento'); ?></legend>
<table class="admintable">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Disable_JS_ProtoType'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_prototype']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_PROTOTYPE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Disable_JS_Mage'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_mage']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_MAGE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Merge_JS'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['merge_js']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'MERGE_JS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_Google_API'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_google_api']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_GOOGLE_API_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_Protoaculous'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_protoaculous']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_PROTOACULOUS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Use_Protoculous'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['use_protoculous']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'USE_PROTOCULOUS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>

<fieldset class="adminform">
<legend><a onclick="return toggleFieldset('advanced_js');" href="#"><?php echo JText::_('Advanced JavaScript settings'); ?></a></legend>
<table class="admintable" id="advanced_js" style="display:block">
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Disable_JS_Footools'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_footools']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_FOOTOOLS_DESCRIPTION' ); ?></span>
        </td>
    </tr>
    <tr>
        <td class="key" valign="top">
            <?php echo JText::_('Disable_JS_Frototype'); ?>
        </td>
        <td class="value">
            <?php echo $this->fields['disable_js_frototype']; ?>
        </td>
        <td class="status">
        </td>
        <td class="description" valign="top">
            <span><?php echo JText::_( 'DISABLE_JS_FROTOTYPE_DESCRIPTION' ); ?></span>
        </td>
    </tr>
</table>
</fieldset>
