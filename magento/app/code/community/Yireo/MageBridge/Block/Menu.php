<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge class for the menu-block
 */
class Yireo_MageBridge_Block_Menu extends Mage_Core_Block_Template
{
    /*
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area','adminhtml');
        $this->setTemplate('magebridge/menu.phtml');
    }

    /*
     * Helper method to get data from the Magento configuration
     *
     * @access public
     * @param null
     * @return array
     */
    public function getMenuItems()
    {
        // Build the list of menu-items
        $items = array(
            array(
                'action' => 'settings',
                'title' => 'Settings',
            ),
            array(
                'action' => 'check',
                'title' => 'System Check',
            ),
            array(
                'action' => 'updates',
                'title' => 'Updates',
            ),
            array(
                'action' => 'supportkey',
                'title' => 'Support Key',
            ),
        );

        // Fetch the URL-model
        $url = Mage::getModel('adminhtml/url');

        // Get the current request
        $current_action = $this->getRequest()->getActionName();

        // Parse the array into usable URLs and CSS-classes
        foreach($items as $index => $item) {

            // Set the CSS-class
            if($item['action'] == $current_action) {
                $item['class'] = 'active';
            } else {
                $item['class'] = 'inactive';
            }
        
            // Set the URL
            $item['url'] = $url->getUrl('adminhtml/magebridge/'.$item['action']);

            $items[$index] = $item;
        }

        return $items;
    }
}
