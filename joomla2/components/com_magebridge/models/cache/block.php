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
class MageBridgeModelCacheBlock extends MageBridgeModelCache
{
    /*
     * Name of the block to be cached
     */
    private $block = null;

    /*
     * Name of the block to be cached
     */
    private $allowed_blocks = array(
        'content',
    );

    /*
     * Constructor
     *
     * @access public
     * @param $block string
     * @param $request string
     * @param @cache_time int
     * @return null
     */
    public function __construct($block = '', $request = null, $cache_time = null)
    {
        $this->block = $block;
        parent::__construct('block_'.$block, $request, $cache_time);
    }

    /*
     * Method to validate whether the cache is allowed
     * 
     * @param null
     * @return bool
     */
    public function validate() 
    {
        if (parent::validate() == false) {
            return false;
        }

        if (!in_array($this->block, $this->allowed_blocks)) {
            return false;
        }

        return true;
    }

    /*
     * Method to store the data to cache
     * 
     * @param mixed $data
     * @return bool
     */
    public function store($data)
    {
        $data = MageBridgeModelBridgeBlock::decode($data);
        $data = MageBridgeModelBridgeBlock::filterHtml($data);
        return parent::store($data);
    }
}
