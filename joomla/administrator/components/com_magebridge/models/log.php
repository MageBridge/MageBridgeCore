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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import Joomla! libraries
jimport('joomla.utilities.date');

/**
 * MageBridge Logs model
 */
class MagebridgeModelLog extends YireoModel
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('log');
    }

    /**
     * Method to insert a new log
     *
     * @param string $message
     * @param int    $level
     *
     * @return bool
     */
    public function add($message, $level = 0)
    {
        $data = [
            'message' => $message,
            'level'   => $level,
        ];

        return $this->store($data);
    }

    /**
     * Method to store the item
     *
     * @param array $data
     *
     * @return bool
     */
    public function store($data)
    {
        // Prepare the data
        $now = new JDate('now');

        // Build the data
        $data['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        $data['http_agent']  = $_SERVER['HTTP_USER_AGENT'];
        $data['timestamp']   = $now->toSql();

        return parent::store($data);
    }
}
