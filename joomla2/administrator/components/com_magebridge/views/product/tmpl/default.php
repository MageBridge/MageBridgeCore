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
<form method="post" name="adminForm" id="adminForm">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td width="50%" valign="top">
    <fieldset class="adminform">
        <legend><?php echo JText::_( 'Title' ); ?></legend>
        <table class="admintable">
        <tbody>
        <tr>
            <td width="100" align="right" class="key">
                <label for="label">
                    <?php echo JText::_( 'Label' ); ?>:
                </label>
            </td>
            <td>
                <input type="text" name="label" value="<?php echo $this->item->label; ?>" size="30" />
            </td>
        </tr>
        </tbody>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo JText::_( 'Product' ); ?></legend>
        <table class="admintable">
        <tbody>
        <tr>
            <td width="100" align="right" class="key">
                <label for="sku">
                    <?php echo JText::_( 'Magento product' ); ?>:
                </label>
            </td>
            <td class="value">
                <?php echo $this->lists['product']; ?>
            </td>
        </tr>
        <tr>
            <td valign="top" align="right" class="key">
                <?php echo JText::_( 'Published' ); ?>:
            </td>
            <td class="value">
                <?php echo $this->lists['published']; ?>
            </td>
        </tr>
        <tr>
            <td valign="top" align="right" class="key">
                <label for="ordering">
                    <?php echo JText::_( 'Ordering' ); ?>:
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
        <legend><?php echo JText::_( 'Connector' ); ?></legend>
        <table class="admintable">
        <tbody>
        <?php if (!empty($this->connectors)) { ?>
        <?php foreach ($this->connectors as $connector) { ?>
        <?php $current = ($connector->name == $this->item->connector || count($this->connectors) == 1) ? true : false; ?>
        <?php $value = ($current) ? $this->item->connector_value : null; ?>
        <tr>
            <td width="100" align="right" valign="top" class="key">
                <label for="connector<?php echo $connector->name; ?>">
                    <?php $checked = ($current) ? 'checked' : ''; ?>
                    <input type="radio" name="connector" value="<?php echo $connector->name; ?>" id="connector-radio-<?php echo $connector->name; ?>" <?php echo $checked; ?>/>
                </label>
            </td>
            <td class="value">
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
                <?php echo JText::_('There are no connectors available'); ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </fieldset>
</td>
<td width="50%" valign="top">
    <fieldset class="adminform">
        <legend><?php echo JText::_('Actions'); ?></legend>
        <?php echo $this->loadTemplate('actions'); ?>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('Parameters (optional)'); ?></legend>
        <?php echo $this->loadTemplate('params'); ?>
    </fieldset>
</td>
</tr>
</tbody>
</table>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
