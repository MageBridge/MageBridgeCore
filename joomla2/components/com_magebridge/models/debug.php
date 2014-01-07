<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
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

/*
 * Bridge debugging class
 */
class MagebridgeModelDebug
{
    const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA = 'joomla';
    const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO = 'magento';
    const MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC = 'joomla_jsonrpc';
    const MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO_JSONRPC = 'magento_jsonrpc';

    /*
     * Singleton
     */
    protected static $_instance = null;

    /*
     * Data
     */
    private $_data = array();

    /*
     * Method to fetch the data
     *
     * @access public
     * @param null
     * @return MageBridgeModelDebug
     */
    public static function getInstance()
    {
        static $instance;

        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
     * Method to initialize debugging
     * 
     * @access public
     * @param null
     * @return null
     */
    public static function init()
    {
        static $flag = 0;
        if ($flag == 0) {
            $flag = 1;
            if (MagebridgeModelDebug::isDebug() == true && MagebridgeModelConfig::load('debug_display_errors') == 1) {
                ini_set('display_errors', 1);
            }
        }
    }

    /*
     * Method to add general information to the bridge
     * 
     * @access public
     * @param null
     * @return null
     */
    public static function addGeneral()
    {
        static $flag = 0;
        if ($flag == 0) {
            $flag = 1;
            if (MagebridgeModelDebug::isDebug() == true) {
                MageBridgeModelDebug::getInstance()->notice( "Support Key: ".MagebridgeModelConfig::load('supportkey'));
            }
        }
    }

    /*

    /*
     * Method to run before the build
     * 
     * @access public
     * @param null
     * @return null
     */
    public static function beforeBuild()
    {
        static $flag = 0;
        if ($flag == 0) {
            $flag = 1;
            $bridge = MageBridgeModelBridge::getInstance();
            $debug = MageBridgeModelDebug::getInstance();

            $debug->notice( "API session: ".$bridge->getApiSession());
            $debug->notice( "Magento session: ".$bridge->getMageSession());
            $debug->addGeneral();
        }
    }

    /*
     * Method to get the data
     * 
     * @access public
     * @param null
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /*
     * Method to fetch debug-data from the other side of the bridge
     * 
     * @access public
     * @param null
     * @return null
     */
    public function getBridgeData()
    {
        static $flag;
        if (empty($flag)) {

            $flag = true;

            $data = MageBridgeModelBridge::getInstance()->getDebug();
            if (!empty($data) && is_array($data)) {
                foreach ($data as $d) {
                    $d['origin'] = MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO;
                    $d['time'] = time();
                    $this->_add($d);
                }
            }
        }
    }

    /*
     * Method to clean all data
     * 
     * @access public
     * @param null
     * @return null
     */
    public function clean()
    {
        $this->_data = array();
    }

    /*
     * Method to get the debug origin
     * 
     * @access public
     * @param string $value
     * @return int
     */
    static public function getDebugOrigin($value = null)
    {
        static $debug_type = MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA;
        if (!empty($value)) {
            $debug_type = $value;
        }
        return $debug_type;
    }

    /*
     * Generic method to add a message
     * 
     * @access public
     * @param int $type
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return bool
     */
    public function add($type = MAGEBRIDGE_DEBUG_NOTICE, $message = null, $section = null, $origin = null, $time = null)
    {
        $application = JFactory::getApplication();
        if ($application->isAdmin() && $type == MAGEBRIDGE_DEBUG_FEEDBACK) {
            JError::raiseWarning('API', $message);
            return true;
        }

        if (MagebridgeModelDebug::isDebug() == false) {
            return false;
        }

        if (!empty($message)) {
            if (!$time > 0) $time = time();
            if (empty($origin)) $origin = self::getDebugOrigin();
            if (empty($section)) $section = '';
            $data = array( 
                'type' => $type, 
                'message' => $message, 
                'section' => $section,
                'origin' => $origin,
                'time' => $time,
            );
            $this->_add( $data );
            return true;
        }

        return false;
    }

    /*
     * Method to add a notice
     * 
     * @access public
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function notice($message = null, $section = null, $origin = null, $time = null) 
    {
        $this->add(MAGEBRIDGE_DEBUG_NOTICE, $message, $section, $origin, $time);
    }
    
    /*
     * Method to add a warning
     * 
     * @access public
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function warning($message = null, $section = null, $origin = null, $time = null) 
    {
        $this->add(MAGEBRIDGE_DEBUG_WARNING, $message, $section, $origin, $time);
    }

    /*
     * Method to add an error 
     * 
     * @access public
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function error($message = null, $section = null, $origin = null, $time = null) 
    {
        $this->add(MAGEBRIDGE_DEBUG_ERROR, $message, $section, $origin, $time);
    }

    /*
     * Method to add a trace
     * 
     * @access public
     * @param string $message
     * @param mixed $variable
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function trace($message = null, $variable = null, $section = null, $origin = null, $time = null) 
    {
        if (!empty($variable)) $message .= ': '.var_export($variable, true);
        $this->add(MAGEBRIDGE_DEBUG_TRACE, $message, $section, $origin, $time);
    }

    /*
     * Method to add feedback for the user-GUI
     * 
     * @access public
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function feedback($message = null, $section = null, $origin = null, $time = null) 
    {
        $this->add(MAGEBRIDGE_DEBUG_FEEDBACK, $message, $section, $origin, $time);
    }

    /*
     * Method to add profiling-data
     * 
     * @access public
     * @param string $message
     * @param string $section
     * @param string $origin
     * @param int $time
     * @return null
     */
    public function profiler($message = null, $section = null, $origin = null, $time = null) 
    {
        $this->add(MAGEBRIDGE_DEBUG_PROFILER, $message, $section, $origin, $time);
    }

    /*
     * Method to determine if debugging is needed
     * 
     * @access public
     * @param null
     * @return bool
     */
    static public function isDebug()
    {
        static $debug = null;
        if ($debug == null) {
            $debug = false;
            if (MagebridgeModelConfig::load('debug') == 1 && !empty($_SERVER['REMOTE_ADDR'])) {
                $ips = MagebridgeModelConfig::load('debug_ip');
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

    /*
     * Method to write all messages to a logfile
     * 
     * @access private
     * @param array $data
     * @return null
     */
    private function _add($data)
    {
        // Make sure all fields are defined
        foreach (array('section', 'type') as $index) {
            if (!isset($data[$index])) $data[$index] = null;
        }

        if (MagebridgeModelDebug::isDebug() == false) return false;
        if (MagebridgeModelConfig::load('debug_level') == 'error' && $data['type'] != MAGEBRIDGE_DEBUG_ERROR) return false;
        if (MagebridgeModelConfig::load('debug_level') == 'profiler' && $data['type'] != MAGEBRIDGE_DEBUG_PROFILER) return false;

        $this->_data[] = $data;
        switch(MagebridgeModelConfig::load('debug_log')) {
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
    }

    /*
     * Method to write all messages to a logfile
     * 
     * @access private
     * @param array $data
     * @return null
     */
    private function _writeLog($data = null)
    {
        if ($data) {

            $app = JFactory::getApplication();
            $log_path = $app->getCfg('log_path');

            if (empty($log_path)) {
                $log_path = JPATH_SITE.'/logs';
            }
            $file = $log_path.'/magebridge.txt';

            $message = '['.$data['origin'].'] ';
            $message .= $data['section'].' ';
            $message .= '('.date('Y-m-d H:i:s', $data['time']).') ';
            $message .= $data['type'] . ': ';
            $message .= $data['message'] . "\n";

            @file_put_contents( $file, $message, FILE_APPEND );
        }
    }

    /*
    /*
     * Method to write all messages to the database table 
     * 
     * @access private
     * @param array $data
     * @return null
     */
    private function _writeDb($data = null)
    {
        if ($data) {

            $db = JFactory::getDBO();
            $remote_addr = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : null;
            $http_agent = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null;
            $unique_id = $this->getUniqueId();

            $values = array(
                'message' => $data['message'],
                'type' => $data['type'],
                'origin' => $data['origin'],
                'section' => $data['section'],
                'timestamp' => date('Y-m-d H:i:s', $data['time']),
                'remote_addr' => $remote_addr,
                'session' => $unique_id,
                'http_agent' => $http_agent,
            );

            $query_parts = array();
            foreach ($values as $name => $value) {
                $query_parts[] = "`$name`=".$db->Quote($value);
            }
            $query = "INSERT INTO `#__magebridge_log` SET ".implode(', ', $query_parts);

            $db->setQuery($query);
            $db->query();
        }
    }

    /*
     * Helper method to generate a unique ID for this session
     */
    public function getUniqueId($length = 30) 
    {
        static $unique = null;
        if ($unique === null) {
            $alphNums = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";        
            $newString = str_shuffle(str_repeat($alphNums, rand(1, $length))); 
            $unique = substr($newString, rand(0,strlen($newString)-$length), $length); 
        }
        return $unique;
    }
}

