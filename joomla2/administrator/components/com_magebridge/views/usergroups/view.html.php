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
class MageBridgeViewUsergroups extends YireoViewList
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
	public function display($tpl = null)
	{
        // Prepare the items for display
        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                $item->edit_link = 'index.php?option=com_magebridge&view=usergroup&task=edit&cid[]='.$item->id;
                $this->items[$index] = $item;
            }
        }

		parent::display($tpl);
	}

    public function getUsergroupLabel()
    {
    }
}
