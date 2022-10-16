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
 * MageBridge URL model
 */
class MagebridgeModelUrl extends YireoModelItem
{
    /**
     * Constructor method
     */
    public function __construct()
    {
        $this->_orderby_title = 'source';

        parent::__construct('url');
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
        // Store the item
        $rt = parent::store($data);

        // Change the setting "load_urls" in the MageBridge configuration
        if ($data['published'] == 1) {
            MageBridgeModelConfig::getSingleton()->saveValue('load_urls', 1);
        }

        return $rt;
    }
}
