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

// Imports
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
        /** @var Yireo_MageBridge_Helper_Autoloader $autoLoader */
        $autoLoader = Mage::helper('magebridge/autoloader');
        $autoLoader->load();

        $this->storeConfig = new \Yireo\MageBridge\Utilities\Config($store);
    }

    /**
     * @param $url
     *
     * @return array
     */
    public function appendUrlAsIp($url)
    {
        $currentIps = $this->getCurrentIps();

        if (empty($url)) {
            return $currentIps;
        }

        $ip = $this->getIpFromUrl($url);

        if (empty($ip)) {
            return $currentIps;
        }

        if (in_array($ip, $currentIps)) {
            return $currentIps;
        }

        $currentIps[] = $ip;
        array_unique($currentIps);

        return $currentIps;
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
     * @param array $ips
     */
    public function save($ips = [])
    {
        $this->storeConfig->save('api_allowed_ips', implode(',', $ips));
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
     * @param string $url
     *
     * @return string
     */
    protected function getIpFromUrl($url)
    {
        $hostPart = parse_url($url, PHP_URL_HOST);
        $ip = gethostbyname($hostPart);

        return $ip;
    }
}