<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo
 * @package YireoLib
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<th width="300" class="title">
    <?php echo JHtml::_('grid.sort',  'LIB_YIREO_TABLE_FIELDNAME_TITLE', 'title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
</th>
