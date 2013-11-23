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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * HTML View class
 */
class MageBridgeViewProducts extends YireoViewList
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null, $prepare = true)
    {
        // Automatically fetch items, total and pagination - and assign them to the template
        $this->fetchItems();

        // Custom filters
        $this->lists['connector'] = $this->selectConnector($this->getFilter('connector'));

        // Prepare the items for display
        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                $item->edit_link = 'index.php?option=com_magebridge&view=product&task=edit&cid[]='.$item->id;
                $this->items[$index] = $item;
            }
        }

        parent::display($tpl);
    }

    /*
     * Helper-method to return the HTML-field for connector
     *
     * @param string $current
     * @return string
     */
    public function selectConnector($current)
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__magebridge_connectors WHERE `published`=1 AND `type`="product"');
        $rows = $db->loadObjectList();

        $options = array();
        $options[] = JHTML::_('select.option', '', '- '.JText::_( 'Select Connector' ).' -', 'id', 'title' );

        if (!empty($rows)) {
            foreach ( $rows as $row ) {
                $options[] = JHTML::_('select.option', $row->name, JText::_($row->title), 'id', 'title' );
            }
        }

        $javascript = 'onchange="document.adminForm.submit();"';
        return JHTML::_('select.genericlist', $options, 'filter_connector', $javascript, 'id', 'title', $current );
    }
}
