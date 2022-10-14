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
 * Bridge-segment class
 */
class MageBridgeModelBridgeSegment
{
    /** @var  MageBridgeModelRegister */
    protected $register;

    /** @var MageBridgeModelBridge */
    protected $bridge;

    /** @var  JApplicationCms */
    protected $app;

    /** @var  JDocumentHtml */
    protected $doc;

    /**
     * Instance variable
     */
    protected static $_instances = null;

    /**
     * Singleton
     *
     * @param string $name
     *
     * @return object
     */
    public static function getInstance($name = null)
    {
        static $_instances = [];

        if ($name == null) {
            $name = 'MageBridgeModelBridgeSegment';
        }

        if (!isset(self::$_instances[$name])) {
            self::$_instances[$name] = new $name();
        }

        return self::$_instances[$name];
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->register = MageBridge::getRegister();
        $this->bridge = MageBridge::getBridge();
        $this->app = JFactory::getApplication();
        $this->doc = JFactory::getDocument();
    }

    /**
     * Load the response-data
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     *
     * @return array
     */
    /**public function getResponseData($type = null, $name = null, $arguments = null, $id = null)
     * {
     * return $this->register->getData($type, $name, $arguments, $id);
     * }*/

    /**
     * Method to get something specific from the build
     *
     * @param string $id
     *
     * @return array|bool
     */
    public function getResponseById($id = null)
    {
        return $this->register->getById($id);
    }

    /**
     * Method to get something specific from the build
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     *
     * @return array|bool
     */
    protected function getResponse($type = '', $name = null, $arguments = null, $id = null)
    {
        return $this->register->get($type, $name, $arguments, $id);
    }
}
