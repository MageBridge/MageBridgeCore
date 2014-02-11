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

<form method="post" name="adminForm" id="adminForm">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="50%" valign="top">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_BASIC'); ?></legend>
        <table class="admintable">
        <tbody>
        <tr>
            <td width="100" align="right" class="key">
                <label for="label">
                    <?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELD_LABEL'); ?>:
                </label>
            </td>
            <td class="value">
                <input type="text" name="label" value="<?php echo $this->item->label; ?>" size="30" />
            </td>
        </tr>
        </tbody>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_STORE'); ?></legend>
        <table class="admintable">
        <tbody>
        <tr>
            <td width="100" align="right" class="key">
                <label for="store">
                    <?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELD_STORE'); ?>:
                </label>
            </td>
            <td class="value">
                <?php echo $this->lists['store']; ?>
            </td>
        </tr>
        <tr>
            <td valign="top" align="right" class="key">
                <?php echo JText::_('JSTATE'); ?>:
            </td>
            <td class="value">
                <?php echo $this->lists['published']; ?>
            </td>
        </tr>
        <tr>
            <td valign="top" align="right" class="key">
                <label for="ordering">
                    <?php echo JText::_('JORDERING'); ?>:
                </label>
            </td>
            <td class="value">
                <?php echo $this->lists['ordering']; ?>
            </td>
        </tr>
        </tbody>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_FIELDSET_RELATION'); ?></legend>
        <table class="admintable">
        <tbody>
        <?php if (!empty($this->connectors)) { ?>
        <?php foreach ($this->connectors as $connector) { ?>
        <?php if ($connector->isVisible() == false) continue; ?>
        <?php $current = ($connector->name == $this->item->connector) ? true : false; ?>
        <?php $value = ($current) ? $this->item->connector_value : null; ?>
        <tr>
            <td width="100" align="right" valign="top" class="key">
                <label for="connector<?php echo $connector->name; ?>">
                    <?php $checked = ($current) ? 'checked' : ''; ?>
                    <input type="radio" name="connector" value="<?php echo $connector->name; ?>" id="connector-radio-<?php echo $connector->name; ?>" <?php echo $checked; ?>/>
                </label>
            </td>
            <td>
                <label for="connector-radio-<?php echo $connector->name; ?>">
                <strong><?php echo $connector->title; ?></strong><p/>
                <?php echo $connector->getFormField($value); ?>
                </label>
            </td>
        </tr>
        <?php } ?>
        <?php } else { ?>
        <tr>    
            <td>
                <?php echo JText::_('COM_MAGEBRIDGE_VIEW_STORE_NO_PLUGINS'); ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </fieldset>
</td>
</tr>
</tbody>
</table>
<?php echo $this->loadTemplate('formend'); ?>
</form>
