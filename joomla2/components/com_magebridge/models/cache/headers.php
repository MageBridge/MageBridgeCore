<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Bridge caching class
 */
class MageBridgeModelCacheHeaders extends MageBridgeModelCache
{
    /*
     * Constructor
     *
     * @access public
     * @param $request string
     * @param @cache_time int
     * @return null
     */
    public function __construct($request = null, $cache_time = null)
    {
        parent::__construct('headers', $request, $cache_time);
    }

    /*
     * Method to store the data to cache
     * 
     * @param mixed $data
     * @return bool
     */
    public function store($data)
    {
        $data = serialize($data);
        return parent::store($data);
    }

    /*
     * Method to load data from cache
     * 
     * @param null
     * @return mixed
     */
    public function load()
    {
        $data = parent::load();
        return unserialize($data);
    }
}
