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

// Definitions
define('MAGEBRIDGE_DEBUG_TRACE', 1);
define('MAGEBRIDGE_DEBUG_NOTICE', 2);
define('MAGEBRIDGE_DEBUG_WARNING', 3);
define('MAGEBRIDGE_DEBUG_ERROR', 4);
define('MAGEBRIDGE_DEBUG_FEEDBACK', 5);
define('MAGEBRIDGE_DEBUG_PROFILER', 6);

define('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA', 'joomla');
define('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO', 'magento');
define('MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC', 'joomla_jsonrpc');
define('MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC', 'magento_jsonrpc');

/**
 * Bridge debugging class
 */
class MageBridgeModelDebug
{
    public const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA = 'joomla';
    public const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO = 'magento';
    public const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC = 'joomla_jsonrpc';
    public const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC = 'magento_jsonrpc';

    /**
     * Singleton
     */
    protected static $_instance = null;

    /**
     * Data
     */
    private $_data = [];

    /**
     * Method to fetch the data
     *
     * @return MageBridgeModelDebug
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Method to initialize debugging
     */
    public static function init()
    {
        static $flag = 0;

        if ($flag == 0) {
            $flag = 1;

            if (MageBridgeModelDebug::isDebug() == true && MageBridgeModelConfig::load('debug_display_errors') == 1) {
                ini_set('display_errors', 1);
            }
        }
    }

    /**
     * Method to add general information to the bridge
     */
    public static function addGeneral()
    {
        static $flag = 0;
        if ($flag == 0) {
            $flag = 1;
            if (MageBridgeModelDebug::isDebug() == true) {
                MageBridgeModelDebug::getInstance()
                    ->notice("Support Key: " . MageBridgeModelConfig::load('supportkey'));
            }
        }
    }

    /**
     * Method to run before the build
     */
    public static function beforeBuild()
    {
        static $flag = 0;

        if ($flag == 0) {
            $flag = 1;
            $bridge = MageBridgeModelBridge::getInstance();
            $debug = MageBridgeModelDebug::getInstance();

            $debug->notice("API session: " . $bridge->getApiSession());
            $debug->notice("Magento session: " . $bridge->getMageSession());
            $debug->addGeneral();
        }
    }

    /**
     * Method to get the data
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Method to fetch debug-data from the other side of the bridge
     */
    public function getBridgeData()
    {
        static $flag;

        if (empty($flag)) {
            $flag = true;

            $bridge = MageBridgeModelBridge::getInstance();
            $data = $bridge->getDebug();

            if (!empty($data) && is_array($data)) {
                foreach ($data as $d) {
                    $d['origin'] = MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO;
                    $d['time'] = time();
                    $this->_add($d);
                }
            }
        }
    }

    /**
     * Method to clean all data
     */
    public function clean()
    {
        $this->_data = [];
    }

    /**
     * Method to get the debug origin
     *
     * @param string $value
     *
     * @return int
     */
    public static function getDebugOrigin($value = null)
    {
        static $debug_type = MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA;

        if (!empty($value)) {
            $debug_type = $value;
        }

        return $debug_type;
    }

    /**
     * Generic method to add a message
     *
     * @param int    $type
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return bool
     */
    public function add($type = MAGEBRIDGE_DEBUG_NOTICE, $message = null, $section = null, $origin = null, $time = null)
    {
        $application = JFactory::getApplication();

        if ($application->isAdmin() && $type == MAGEBRIDGE_DEBUG_FEEDBACK) {
            JError::raiseWarning('API', $message);

            return true;
        }

        if (MageBridgeModelDebug::isDebug() == false) {
            return false;
        }

        if (!empty($message)) {
            if (!$time > 0) {
                $time = time();
            }

            if (empty($origin)) {
                $origin = self::getDebugOrigin();
            }

            if (empty($section)) {
                $section = '';
            }

            $data = [
                'type' => $type,
                'message' => $message,
                'section' => $section,
                'origin' => $origin,
                'time' => $time,];
            $this->_add($data);

            return true;
        }

        return false;
    }

    /**
     * Method to add a notice
     *
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function notice($message = null, $section = null, $origin = null, $time = null)
    {
        $this->add(MAGEBRIDGE_DEBUG_NOTICE, $message, $section, $origin, $time);
    }

    /**
     * Method to add a warning
     *
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function warning($message = null, $section = null, $origin = null, $time = null)
    {
        $this->add(MAGEBRIDGE_DEBUG_WARNING, $message, $section, $origin, $time);
    }

    /**
     * Method to add an error
     *
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function error($message = null, $section = null, $origin = null, $time = null)
    {
        $this->add(MAGEBRIDGE_DEBUG_ERROR, $message, $section, $origin, $time);
    }

    /**
     * Method to add a trace
     *
     * @param string $message
     * @param mixed  $variable
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function trace($message = null, $variable = null, $section = null, $origin = null, $time = null)
    {
        if (!empty($variable)) {
            $message .= ': ' . var_export($variable, true);
        }

        $this->add(MAGEBRIDGE_DEBUG_TRACE, $message, $section, $origin, $time);
    }

    /**
     * Method to add feedback for the user-GUI
     *
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function feedback($message = null, $section = null, $origin = null, $time = null)
    {
        $this->add(MAGEBRIDGE_DEBUG_FEEDBACK, $message, $section, $origin, $time);
    }

    /**
     * Method to add profiling-data
     *
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int    $time
     *
     * @return null
     */
    public function profiler($message = null, $section = null, $origin = null, $time = null)
    {
        $this->add(MAGEBRIDGE_DEBUG_PROFILER, $message, $section, $origin, $time);
    }

    /**
     * Method to determine if debugging is needed
     *
     * @return bool
     */
    public static function isDebug()
    {
        static $debug = null;

        if ($debug == null) {
            $debug = false;

            if (MageBridgeModelConfig::load('debug') == 1 && !empty($_SERVER['REMOTE_ADDR'])) {
                $ips = MageBridgeModelConfig::load('debug_ip');

                if (strlen($ips) > 0 && $ip_array = explode(',', $ips)) {
                    foreach ($ip_array as $ip) {
                        if (trim($ip) == $_SERVER['REMOTE_ADDR']) {
                            $debug = true;
                            break;
                        }
                    }
                } else {
                    $debug = true;
                }
            }
        }

        return $debug;
    }

    /**
     * Method to write all messages to a logfile
     *
     * @param array $data
     *
     * @return null
     */
    private function _add($data)
    {
        // Make sure all fields are defined
        foreach (['section', 'type'] as $index) {
            if (!isset($data[$index])) {
                $data[$index] = null;
            }
        }

        if (MageBridgeModelDebug::isDebug() == false) {
            return false;
        }

        if (MageBridgeModelConfig::load('debug_level') == 'error' && $data['type'] != MAGEBRIDGE_DEBUG_ERROR) {
            return false;
        }

        if (MageBridgeModelConfig::load('debug_level') == 'profiler' && $data['type'] != MAGEBRIDGE_DEBUG_PROFILER) {
            return false;
        }

        $this->_data[] = $data;

        switch (MageBridgeModelConfig::load('debug_log')) {
            case 'db':
                $this->_writeDb($data);
                break;

            case 'file':
                $this->_writeLog($data);
                break;

            case 'both':
                $this->_writeDb($data);
                $this->_writeLog($data);
                break;
        }

        return true;
    }

    /**
     * Method to write all messages to a logfile
     *
     * @access private
     *
     * @param array $data
     *
     * @return boolean
     */
    private function _writeLog($data = null)
    {
        if (empty($data)) {
            return false;
        }

        $config = JFactory::getConfig();
        $log_path = $config->get('log_path');

        if (empty($log_path)) {
            $log_path = JPATH_SITE . '/logs';
        }

        $file = $log_path . '/magebridge.txt';

        if (is_writable($file) == false) {
            return false;
        }

        $message = '[' . $data['origin'] . '] ';
        $message .= $data['section'] . ' ';
        $message .= '(' . date('Y-m-d H:i:s', $data['time']) . ') ';
        $message .= $data['type'] . ': ';
        $message .= $data['message'] . "\n";

        file_put_contents($file, $message, FILE_APPEND);

        return true;
    }

    /**
     *
     * Method to write all messages to the database table
     *
     * @param array $data
     *
     * @return null
     */
    private function _writeDb($data = null)
    {
        if (empty($data)) {
            return false;
        }

        $db = JFactory::getDbo();
        $remote_addr = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
        $http_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $unique_id = $this->getUniqueId();

        $values = [
            'message' => $data['message'],
            'type' => $data['type'],
            'origin' => $data['origin'],
            'section' => $data['section'],
            'timestamp' => date('Y-m-d H:i:s', $data['time']),
            'remote_addr' => $remote_addr,
            'session' => $unique_id,
            'http_agent' => $http_agent,];

        $query_parts = [];

        foreach ($values as $name => $value) {
            $query_parts[] = "`$name`=" . $db->Quote($value);
        }
        $query = "INSERT INTO `#__magebridge_log` SET " . implode(', ', $query_parts);

        $db->setQuery($query);
        $rt = $db->execute();

        return (bool) $rt;
    }

    /**
     * Helper method to generate a unique ID for this session
     *
     * @param int $length
     *
     * @return string
     */
    public function getUniqueId($length = 30)
    {
        static $unique = null;

        if ($unique === null) {
            $alphNums = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            $newString = str_shuffle(str_repeat($alphNums, rand(1, $length)));
            $unique = substr($newString, rand(0, strlen($newString) - $length), $length);
        }

        return $unique;
    }
}
