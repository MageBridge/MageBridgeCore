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

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Table class
 */
class MagebridgeTableConfig extends YireoTable
{
    /**
     * Constructor
     *
     * @param JDatabase $db
     */
    public function __construct(& $db)
    {
        parent::__construct('#__magebridge_config', 'id', $db);
    }
}
