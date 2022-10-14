<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class
 *
 * @package MageBridge
 */
class MagebridgeTableUsergroup extends YireoTable
{
    /**
     * Constructor
     *
     * @param JDatabase $db
     */
    public function __construct(& $db)
    {
        // List of required fields that can not be left empty
        $this->required = ['joomla_group', 'magento_group'];

        // Call the constructor
        parent::__construct('#__magebridge_usergroups', 'id', $db);
    }
}
