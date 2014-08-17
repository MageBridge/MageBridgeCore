<?php 
/**
 * Joomla! Yireo Lib
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright (C) 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pane');
$pane =& JPane::getInstance('sliders');
?>
<?php echo $pane->startPane('content-pane'); ?>
<?php echo $pane->startPanel('Parameters', 'params'); ?>
<table class="paramlist admintable" cellspacing="1">
<tbody>
    <tr>
        <td colspan="2" class="params">
            <?php echo $this->params->render();?>
        </td>
    </tr>
</tbody>
</table>
<?php echo $pane->endPanel(); ?>
<?php echo $pane->endPane(); ?>
