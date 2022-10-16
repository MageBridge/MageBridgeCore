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
 * Helper for usage in Joomla!/MageBridge plugins
 */
class MageBridgePluginHelper
{
    /**
     * @var MageBridgePluginHelper
     */
    private static $_instance;

    /**
     * @var MageBridgeModelBridge
     */
    private $bridge;

    /**
     * @var MageBridgeModelDebug
     */
    private $debug;

    /**
     * @var array
     */
    private $deniedEvents = [];

    /**
     * Singleton method
     *
     * @return MageBridgePluginHelper
     */
    public static function getInstance()
    {
        static $instance;

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Helper-method to determine if it's possible to run this event
     *
     * @param string $event
     * @param array  $options
     *
     * @return bool
     * @deprecated Use MageBridgePluginHelper::getInstance()->isEventAllowed() instead
     */
    public static function allowEvent($event, $options = [])
    {
        $instance = self::getInstance();
        return $instance->isEventAllowed($event, $options);
    }

    /**
     * MageBridgePluginHelper constructor.
     */
    public function __construct()
    {
        $this->bridge = MageBridge::getBridge();
        $this->debug  = MageBridgeModelDebug::getInstance();
    }

    /**
     * Helper-method to determine if it's possible to run this event
     *
     * @param string $event
     * @param array  $options
     *
     * @return bool
     */
    public function isEventAllowed($event, $options = [])
    {
        // Do not run this event if the bridge itself is offline
        if ($this->bridge->isOffline()) {
            $this->debug->notice("Plugin helper detects bridge is offline");

            return false;
        }

        // Do not run this event if the option "disable_bridge" is set to true
        if (isset($options['disable_bridge']) && $options['disable_bridge'] === true) {
            $this->debug->notice("Plugin helper detects event '$event' is currently disabled");

            return false;
        }

        // Do not execute additional plugin-events on the success-page (to prevent refreshing)
        $request = MageBridgeUrlHelper::getRequest();

        if (preg_match('/checkout\/onepage\/success/', $request)) {
            $this->debug->notice("Plugin helper detects checkout/onepage/success page");

            return false;
        }

        // Do not execute this event if we are in XML-RPC or JSON-RPC
        if (MageBridge::isApiPage() === true) {
            return false;
        }

        // Check if this event is the list of events already thrown
        if (in_array($event, $this->deniedEvents)) {
            $this->debug->notice("Plugin helper detects event '$event' is already run");

            return false;
        }

        $this->debug->notice("Plugin helper allows event '$event'");
        $this->deniedEvents[] = $event;

        return true;
    }
}
