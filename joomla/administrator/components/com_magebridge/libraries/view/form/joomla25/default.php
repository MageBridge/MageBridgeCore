<?php 
/**
 * Joomla! Yireo Lib
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Set the right image directory for JavaScipt
jimport('joomla.utilities.utility');
?>
<?php echo $this->loadTemplate('script'); ?>

<form method="post" name="adminForm" id="adminForm">
<div>
    <div class="width-60 fltlft">
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'basic')); ?>
    </div>
    <div class="width-40 fltlft">
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'other')); ?>
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'params')); ?>
    </div>
</div>
<div>
    <div class="width-100 fltlft">
        <?php echo $this->loadTemplate('fieldset', array('fieldset' => 'editor')); ?>
    </div>
</div>
<?php echo $this->loadTemplate('formend'); ?>
</form>
