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
 */
class MagebridgeTableLog extends YireoTable
{
    /**
     * Constructor
     *
     * @param JDatabase $db
     */
    public function __construct(& $db)
    {
        parent::__construct('#__magebridge_log', 'id', $db);
    }

    /**
     * Helper-method to get the default ORDER BY value (depending on the present fields)
     *
     * @return array
     */
    public function getDefaultOrderBy()
    {
        return false;
    }
}
