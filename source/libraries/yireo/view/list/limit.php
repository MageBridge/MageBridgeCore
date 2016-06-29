<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.5.3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<?php
// Construct the options
$limits = array(0, 10, 20, 30, 40, 50, 100, 200, 300, 400, 500);
$options = array();
foreach($limits as $limit) {
    $options[] = array('value' => $limit, 'title' => $limit);
}
$javascript = 'onchange="document.adminForm.submit();"';
?>
<div class="list-limit">
    <?php echo JHTML::_('select.genericlist', $options, 'filter_list_limit', $javascript, 'value', 'title', $this->getModel()->getState('limit')); ?>
</div>
