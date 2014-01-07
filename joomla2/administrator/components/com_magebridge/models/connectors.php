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

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/*
 * MageBridge Connectors model
 */
class MagebridgeModelConnectors extends YireoModel
{
    /**
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function __construct()
    {
        $this->_search = array('title', 'name');
        parent::__construct('connector');

        $type = $this->getFilter('type');
        if (!empty($type)) $this->addWhere($this->_tbl_alias.'.`type` = '.$this->_db->Quote($type));
    }
}
