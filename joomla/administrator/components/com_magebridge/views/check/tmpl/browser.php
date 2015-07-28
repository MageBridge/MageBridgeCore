<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

defined('_JEXEC') or die('Restricted access');
?>

<style>
input.browse {
	font-size: 120%;
	padding: 2px;
}
div.description {
	padding-left: 20px;
	padding-bottom: 20px;
	width: 700px;
}
div.description pre {
	padding-left: 20px;
}
</style>

<fieldset class="adminform">
<legend><?php echo JText::_('COM_MAGEBRIDGE_BROWSE_TEST'); ?></legend>
<h3>Step 1: From Joomla! to Magento</h3>
<div class="description">
<p>
On this page, you can see the result of Joomla! fetching data from the Magento MageBridge-API. The result given below should be 
<em>SUCCESS:...</em>. If it is not, something is wrong. In many cases, the feedback might already give you vital clues.
</p>
</div>
<table class="admintable" width="100%">
<tr>
	<td class="key">
		<?php echo JText::_('COM_MAGEBRIDGE_URL'); ?>
	</td>
	<td>
		<form method="post" name="adminForm" id="adminForm">
			<input class="browse" type="text" name="url" value="<?php echo $this->url; ?>" size="60" disabled />
			<input class="submit" type="submit" name="type" value="Test" />
			<input type="hidden" name="option" value="com_magebridge" />
			<input type="hidden" name="view" value="check" />
			<input type="hidden" name="layout" value="browser" />
			<input type="hidden" name="task" value="" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	</td>
</tr>
<tr>
	<td class="key">
		<?php echo JText::_('COM_MAGEBRIDGE_HOSTNAME'); ?>
	</td>
	<td>
		<?php echo $this->host; ?> (<?php echo JText::_('COM_MAGEBRIDGE_IPADDRESS'); ?>: <?php echo gethostbyname($this->host); ?>)
	</td>
</tr>
<tr>
	<td class="key">
		<?php echo JText::_('COM_MAGEBRIDGE_RESULT'); ?>
	</td>
	<td>
		<iframe src="index.php?option=com_magebridge&view=check&layout=result" width="100%" height="80"></iframe>
	</td>
</tr>
</table>
<h3>Step 2: From browser to Magento</h3>
<div class="description">
<p>
Another thing to check is whether the Magento MageBridge-API is actually there. You can check this by opening up the URL <a target="_new" href="<?php echo $this->url; ?>"><?php echo $this->url; ?></a>. 
The feedback should be a bit cryptic, but should look like the following:
</p>
<pre>
{"meta":{"type":"meta","data":{"state":"empty metadata","extra":null}}}
</pre>
</p>
</div>
<h3>Step 3: Interpreting the results</h3>
<div class="description">
<ul>
<li>If you receive a 404-error in <strong>Step 1</strong> AND <strong>Step 2</strong>, it is most likely that MageBridge is not installed correctly in Magento.</li>
<li>If you receive a 500-error in <strong>Step 1</strong> AND <strong>Step 2</strong>, it is most likely that the permissions on the MageBridge files in Magento are incorrect</li>
<li>If you receive a 404-error in <strong>Step 1</strong> but NOT <strong>Step 2</strong>, something lacks in the webserver configuration: You should contact the system administrator
of this webserver to fix this problem.</li>
</ul>
<p>
Refer to the <?php echo MageBridgeHelper::getHelpText('troubleshooting'); ?> for more information.
</p>
</div>
</fieldset>
