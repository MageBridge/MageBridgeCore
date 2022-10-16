<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton == 'update') {
		var answer = confirm('<?php echo JText::_('COM_MAGEBRIDGE_UPDATE_WARNING'); ?>');
		if (answer) {
			submitform(pressbutton);
		}
	} else {
		submitform(pressbutton);
	}
}

function selectPackages(type) {
	jQuery('input.' + type).prop('checked', 1);
}
</script>
<form method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
	<td align="left" width="40%">
		<?php echo $this->loadTemplate('search'); ?>
	</td>
	<td align="right" width="60%">
		<?php echo $this->loadTemplate('lists'); ?>
	</td>
</tr>
</table>
<div id="editcell">
<table cellspacing="0" cellpadding="0" border="0" width="100%" class="adminlist table table-striped">
<thead>
	<tr>
		<th width="20">
			<?php echo JText::_('JNUM'); ?>
		</th>
		<th>
			<?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_EXTENSION'); ?>
		</th>
		<th width="200"> 
			<?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_SYSTEM_NAME'); ?>
		</th>
		<th width="110"> 
			<?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_TYPE'); ?>
		</th>
		<th width="110">
			<?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CURRENT_VERSION'); ?>
		</th>
		<th width="110">
			<?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_LATEST_VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
<?php
$i = 0;
foreach ($this->data as $package) {
    $k = (empty($k)) ? 0 : 1;

    $checkbox_class = [];
    $checkbox_class[] = 'package';
    if ($package['core'] == 1) {
        $checkbox_class[] = 'package-core';
    }
    if ($package['base'] == 1) {
        $checkbox_class[] = 'package-base';
    }

    // Current version exists
    if ($package['current_version']) {
        $checked = '<input type="checkbox" disabled checked="checked" />';
        $checked .= '<input type="hidden" name="packages[]" value="'.$package['name'].'" />';

    // Not yet installed
    } else {
        $checked = '<input type="checkbox" class="'.implode(' ', $checkbox_class).'" name="packages[]" value="'.$package['name'].'" />';
    }

    $token = JSession::getFormToken();
    $upgrade_url = 'index.php?option=com_magebridge&task=update&packages[]='.$package['name'].'&'.$token.'=1';

    if (isset($package['app'])) {
        if ($package['app'] == 'site') {
            $app = JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_FRONTEND');
        } else {
            $app = JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_BACKEND');
        }
    } else {
        $app = null;
    }
    ?>
	<tr class="<?php echo 'row'.$k; ?>" id="package_<?php echo $i; ?>">
		<td>
			<?php echo $checked; ?>
		</td>
		<td class="select">
			<strong><?php echo $package['title']; ?></strong><br/>
			<?php if(!empty($app)) {
			    echo '['.$app.']' ;
			} ?>
			<?php echo $package['description']; ?>
		</td>
		<td class="select">
			<?php echo $package['name']; ?>
		</td>
		<td class="select">
			<?php if($package['core'] == 1) {
			    echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CORE');
			} elseif($package['base'] == 1) {
			    echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_BASE');
			} else {
			    echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_OPTIONAL');
			} ?>
		</td>
		<td class="select">
			<?php echo ($package['current_version']) ? $package['current_version'] : JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_NOT_INSTALLED'); ?>
		</td>
		<?php
        $class = ['select'];
    if($package['available'] == 0) {
        $class[] = 'error';
    } elseif(empty($package['current_version'])) {
        $class[] = '';
    } elseif($package['version'] != $package['current_version']) {
        $class[] = 'warning';
    } else {
        $class[] = 'notice';
    }
    ?>
		<td class="<?php echo implode(' ', $class); ?>">
			<?php if($package['available'] == 1) : ?>
			<?php echo ($package['version']) ? '<a href="'.$upgrade_url.'">'.$package['version'].'</a>' : JText::_('n/a'); ?>
			<?php elseif(!empty($package['purchase_url'])): ?>
			<?php echo '<a target="_new" href="'.$package['purchase_url'].'">'.JText::_('Buy now').'</a>'; ?>
			<?php else: ?>
			<?php echo JText::_('n/a'); ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php
    $k = 1 - $k;
    $i++;
}
?>
</tbody>
</table>
</div>

<div style="padding:20px">
<strong><?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CHECK'); ?></strong>:
<a href="#" onClick="return selectPackages('package');"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CHECK_ALL'); ?></a> |
<a href="#" onClick="return selectPackages('package-core');"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CHECK_CORE'); ?></a> |
<a href="#" onClick="return selectPackages('package-base');"><?php echo JText::_('COM_MAGEBRIDGE_VIEW_UPDATE_CHECK_RECOMMENDED'); ?></a>
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_('form.token'); ?>
</form>
