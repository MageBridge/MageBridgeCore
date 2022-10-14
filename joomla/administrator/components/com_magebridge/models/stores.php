<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Stores model
 */
class MagebridgeModelStores extends YireoModel
{
    /**
     * Constructor method
     *
     */
    public function __construct()
    {
        $this->_search = ['description'];

        parent::__construct('store');
    }
}
