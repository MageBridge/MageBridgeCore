<?php
/**
 * Joomla! component MageBridge
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Abstract proxy class
 */
abstract class MageBridgeModelProxyAbstract
{
    public const CONNECTION_FALSE = 0;

    public const CONNECTION_SUCCESS = 1;

    public const CONNECTION_ERROR = 1;

    /**
     * Counter for how many times we connect to Magento
     */
    protected $count = 2;

    /**
     * State of connection
     */
    protected $state = '';

    /**
     * Initialization flag
     */
    protected $init = self::CONNECTION_FALSE;

    /**
     * @var MageBridgeModelBridge
     */
    protected $bridge;

    /**
     * @var MageBridgeModelDebug
     */
    protected $debug;

    /**
     * @var JApplicationWeb
     */
    protected $app;

    /**
     * @var MageBridgeProxyHelper
     */
    protected $helper;

    /**
     * @var JInput
     */
    protected $input;

    /**
     * @var \Joomla\Registry\Registry
     */
    protected $config;

    /**
     * @var MageBridgeModelUser
     */
    protected $user;

    /**
     * Method to fetch the data
     *
     * @return MageBridgeModelProxy
     */
    public static function getInstance()
    {
        static $instance;

        if ($instance === null) {
            $instance = new MageBridgeModelProxy();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bridge = MageBridgeModelBridge::getInstance();

        $this->debug = MageBridgeModelDebug::getInstance();

        $this->app = JFactory::getApplication();

        $this->helper = new MageBridgeProxyHelper($this->app);

        $this->input = $this->app->input;

        $this->user = MageBridgeModelUser::getInstance();

        $this->config = JFactory::getConfig();
    }


    /**
     * Encode data for transmission
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encode($data)
    {
        $rt = json_encode($data);

        if ($rt == false) {
            if (is_string($data)) {
                $data = utf8_encode($data);
            }

            $rt = json_encode($data);

            if ($rt == false) {
                if (function_exists('json_last_error')) {
                    $json_error = json_last_error();

                    if ($json_error == JSON_ERROR_UTF8) {
                        $json_error = "Malformed UTF-8";
                    }

                    if ($json_error == JSON_ERROR_SYNTAX) {
                        $json_error = "Syntax error";
                    }
                } else {
                    $json_error = 'unknown';
                }

                $this->debug->error('PHP Error: json_encode failed with error "' . $json_error . '"');
                $this->debug->trace('Data before json_encode', $data);
            }
        }

        return $rt;
    }

    /**
     * Decode data after transmission
     *
     * @param string $data
     *
     * @return mixed
     */
    public function decode($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);

            if ($decoded == false || $decoded == 1 || $decoded == $data) {
                return false;
            } else {
                return $decoded;
            }
        } else {
            return $data;
        }
    }

    /**
     * Method to set the current proxy state
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Method to get the current proxy state
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }
}
