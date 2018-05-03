<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2017
 * @license Open Source License
 * @link https://www.yireo.com
 */

namespace Yireo\MageBridge\Utilities;

use \Mage_Core_Model_Store as Store;

/**
 * Class interacting with Magento System Configuration
 */
class Config
{
    /**
     * Configuration prefix
     */
    const CONFIG_PREFIX = 'magebridge/joomla/';

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var \Mage_Core_Model_Config
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->config = \Mage::app()->getConfig();
    }

    /**
     * @param $path
     *
     * @return null|string
     */
    public function get($path)
    {
        return $this->store->getConfig(self::CONFIG_PREFIX . $path);
    }

    /**
     * @param string $path
     * @param string $value
     *
     * @return mixed
     */
    public function save($path, $value)
    {
        return $this->config->saveConfig(self::CONFIG_PREFIX . $path, $value);
    }
}