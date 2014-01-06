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

// Set the right image directory for JavaScipt
jimport('joomla.utilities.utility');
?>
<?php echo $this->loadTemplate('script'); ?>

<form method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
    <div class="span6">
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'basic')); ?>
    </div>
    <div class="span6">
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'other')); ?>
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'params')); ?>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">
        <?php echo $this->loadTemplate('editor'); ?>
    </div>
</div>
<?php echo $this->loadTemplate('formend'); ?>
</form>
