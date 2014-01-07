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
<form method="post" name="adminForm" id="adminForm" autocomplete="off">

<?php echo $this->pane->startPane('config'); ?>
<?php foreach($this->getTabs() as $tab) : ?>
<?php echo $this->printTab($tab[0], $tab[1], $tab[2]); ?>
<?php endforeach; ?>
<?php echo $this->pane->endPane(); ?>

<input type="hidden" name="option" value="com_magebridge" />
<input type="hidden" name="view" value="config" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
