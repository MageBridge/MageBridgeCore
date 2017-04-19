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

use \Mage_Core_Model_Store as Store;

/**
 * Model for configuration value "allowed_ips", determining which IPs are allowed to connect to MageBridge
 */
class Yireo_MageBridge_Model_Config_AllowedIps
{
    /**
     * @var \Yireo\MageBridge\Utilities\Config
     */
    protected $storeConfig;

    /**
     * Yireo_MageBridge_Model_Config_AllowedHosts constructor
     *
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        Mage::helper('magebridge/autoloader')->load();

        $this->storeConfig = new \Yireo\MageBridge\Utilities\Config($store);
    }

    /**
     * @param $url
     *
     * @return bool
     */
    public function appendUrlAsIp($url)
    {
        if (empty($url)) {
            return false;
        }

        $hostPart = parse_url($url, PHP_URL_HOST);
        $ip = gethostbyname($hostPart);

        if (empty($ip)) {
            return false;
        }

        $currentIps = $this->getCurrentIps();
        if (in_array($ip, $currentIps)) {
            return false;
        }

        $currentIps[] = $ip;
        array_unique($currentIps);

        $this->save($currentIps);

        return true;
    }

    /**
     * @param $host
     * @return bool
     */
    public function isHostAllowed($host)
    {
        $ip = gethostbyname($host);
        $currentIps = $this->getCurrentIps();
        if (empty($currentIps)) {
            return true;
        }

        if (in_array($ip, $this->getCurrentIps())) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getCurrentIps()
    {
        $value = $this->storeConfig->get('api_allowed_ips');
        $valueObject = new Yireo\MageBridge\Utilities\StringValue($value);

        return $valueObject->asArray();
    }

    /**
     * @param array $ips
     */
    protected function save($ips = [])
    {
        $this->storeConfig->save('api_allowed_ips', implode(',', $ips));
    }
}