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
class MageBridgeViewConnectors extends YireoViewList
{
    /*
     * Flag to determine whether to load the toolbar
     */
    protected $loadToolbarEdit = false;
    protected $loadToolbarDelete = false;

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // filters
        $options = array( 
            array( 'value' => '', 'text' => '- Select Type -' ),
            array( 'value' => 'store', 'text' => 'Store Connectors' ),
            array( 'value' => 'product', 'text' => 'Product Connectors' ),
            array( 'value' => 'profile', 'text' => 'Profile Connectors' ),
        );
        $filter_type = $this->getFilter('type');
        $javascript = 'onchange="document.adminForm.submit();"';
        $this->lists['type'] = JHTML::_('select.genericlist', $options, 'filter_type', $javascript, 'value', 'text', $filter_type );

        // Fetch the items
        $this->fetchItems();

        // Prepare the items for display
        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {

                if ($item->type == 'product') {
                    $object = MageBridgeConnectorProduct::getInstance()->getConnectorObject($item);
                } else if ($item->type == 'profile') {
                    $object = MageBridgeConnectorProfile::getInstance()->getConnectorObject($item);
                } else {
                    $object = MageBridgeConnectorStore::getInstance()->getConnectorObject($item);
                }

                if (is_object($object)) {
                    $item->enabled = $object->isEnabled();
                } else {
                    $item->enabled = false;
                }

                $item->edit_link = 'index.php?option=com_magebridge&view=connector&task=edit&cid[]='.$item->id;

                if($item->enabled == false) {
                    $item->hasState = false;
                    $item->hasOrdering = false;
                    $item->hasCheckbox = false;
                }

                $this->items[$index] = $item;
            }
        }

        parent::display($tpl);
    }
}
