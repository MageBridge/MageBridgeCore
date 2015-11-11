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
<form enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td nowrap="nowrap">
	</td>
</tr>
</table>
<div id="editcell">
	<input type="hidden" name="max_file_size" value="100000" />
	Choose a file to upload: <input name="csv" type="file" /><br />
	<input type="submit" value="Upload File" />
</div>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="users" />
<input type="hidden" name="task" value="upload" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
