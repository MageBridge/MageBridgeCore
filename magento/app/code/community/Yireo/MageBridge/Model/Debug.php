<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

if(!defined('MAGEBRIDGE_DEBUG_TRACE')) define( 'MAGEBRIDGE_DEBUG_TRACE', 1 );
if(!defined('MAGEBRIDGE_DEBUG_NOTICE')) define( 'MAGEBRIDGE_DEBUG_NOTICE', 2 );
if(!defined('MAGEBRIDGE_DEBUG_WARNING')) define( 'MAGEBRIDGE_DEBUG_WARNING', 3 );
if(!defined('MAGEBRIDGE_DEBUG_ERROR')) define( 'MAGEBRIDGE_DEBUG_ERROR', 4 );
if(!defined('MAGEBRIDGE_DEBUG_FEEDBACK')) define( 'MAGEBRIDGE_DEBUG_FEEDBACK', 5 );
if(!defined('MAGEBRIDGE_DEBUG_PROFILER')) define( 'MAGEBRIDGE_DEBUG_PROFILER', 6 );

define( 'MAGEBRIDGE_DEBUG_TYPE_JOOMLA', 'joomla' );
define( 'MAGEBRIDGE_DEBUG_TYPE_MAGENTO', 'magento' );
define( 'MAGEBRIDGE_DEBUG_TYPE_JSONRPC', 'jsonrpc' );


/*
 * Stand-alone function to override the default error-handler.
 * This function is called from magebridge/core.
 */
function Yireo_MageBridge_ErrorHandler($errno, $errstr, $errfile, $errline)
{
    // Flag which decides to close the bridge or not
    $close_bridge = false;

    // Handle each error-type differently
    switch($errno) {

        // With errors, we need to close the bridge and exit
        case E_ERROR:
        case E_USER_ERROR:
            Mage::getSingleton('magebridge/debug')->error("PHP Error in $errfile - Line $errline: $errstr");
            $close_bridge = true;
            break;

        // Log warnings
        case E_USER_WARNING:
            Mage::getSingleton('magebridge/debug')->error("PHP Warning in $errfile - Line $errline: $errstr");
            break;

        // E_WARNING also includes Autoload.php messages which are NOT interesting
        case E_WARNING:
            break;
            
        // Ignore notices
        case E_USER_NOTICE:
            break;
            
        // Log unknown errors also as warnings, because we are in a E_STRICT environment
        default:
            Mage::getSingleton('magebridge/debug')->error("PHP Unknown in $errfile - Line $errline: [$errno] $errstr");
            break; 
    }

    // Close the bridge if needed
    if($close_bridge == true) {
        $bridge = Mage::getSingleton('magebridge/core');
        print $bridge->output();
        exit(1);
    }

    return true;
}

/*
 * Stand-alone function to override the default exception-handler.
 * This function is called from magebridge/core.
 */
function Yireo_MageBridge_ExceptionHandler($exception)
{
    // Make sure this exception is logged in MAGENTO/var/log/exception.log
    Mage::logException($exception);

    // Print the error
    if((bool)Mage::getStoreConfig('magebridge/debug/print') == true) {
        die('<h1>PHP Exception:</h1><pre>'.$exception->getMessage().'</pre>');
    }

    // Make sure this exception is added to the bridge-data
    Mage::getSingleton('magebridge/debug')->error("PHP Fatal Error: ".$exception->getMessage());

    // Output the bridge
    $bridge = Mage::getSingleton('magebridge/core');
    print $bridge->output(false);
    return;
}

/*
 * MageBridge Debug-class
 */
class Yireo_MageBridge_Model_Debug
{
    /*
     * Object instance
     */ 
    protected static $_instance = null;

    /*
     * Internal data array
     */ 
    private $_data = array();

    /*
     * Singleton method
     *  
     * @static
     * @access public
     * @param null
     * @return Yireo_MageBridge_Model_Debug
     */
    static public function getInstance()
    {
        if(null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
     * Method to determine whether debugging is enabled or not
     *  
     * @access public
     * @param null
     * @return array
     */
    public function isDebug()
    {
        if(Mage::helper('magebridge')->isBridge()) {
            return (bool)Mage::getSingleton('magebridge/core')->getMetaData('debug');
        } else {
            return (bool)Mage::getStoreConfig('magebridge/debug/log');
        }
    }

    /*
     * Method to get all the debugging data
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
     * Method to clean all the debugging data
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
     * Method to add a new debugging message
     *  
     * @access public
     * @param string $message
     * @param int $type
     * @param int $time
     * @param string $origin
     * @return null
     */
    public function add($message = null, $type = MAGEBRIDGE_DEBUG_NOTICE, $time = null, $origin = null)
    {
        // Do not add this message, when debugging is disabled (but always debug real errors)
        if($type != MAGEBRIDGE_DEBUG_ERROR && $this->isDebug() == false) {
            return false;
        }

        if(!empty($message)) {
            if(empty($time) || !$time > 0) $time = time();
            if(empty($origin)) $origin = 'Magento';

            $data = array( 
                'type' => $type, 
                'message' => $message, 
                'time' => $time,
                'origin' => $origin,
            );
        
            $message = '['.$data['origin'].'] ';
            $message .= '('.date('Y-m-d H:i:s', $data['time']).') ';
            $message .= $data['type'] . ': ';
            $message .= $data['message'] . "\n";

            $this->_data[] = $data;
            Mage::helper('magebridge')->debug($message);
        }
    }

    /*
     * Method to add a new notice
     *  
     * @access public
     * @param string $message
     * @param int $time
     * @return null
     */
    public function notice($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_NOTICE, $time);
    }

    /*
     * Method to add a new warning
     *  
     * @access public
     * @param string $message
     * @param int $time
     * @return null
     */
    public function warning($message = null, $time = null)
    {
        if (!headers_sent()) {
            header('Warning: 199 '.$message);
        }

        $this->add($message, MAGEBRIDGE_DEBUG_WARNING, $time);
    }

    /*
     * Method to add a new error
     *  
     * @access public
     * @param string $message
     * @param int $time
     * @return null
     */
    public function error($message = null, $time = null)
    {
        if (!headers_sent()) {
            header('Warning: 199 '.$message);  
        }

        $this->add($message, MAGEBRIDGE_DEBUG_ERROR, $time);
    }

    /*
     * Method to add a new trace
     *  
     * @access public
     * @param string $message
     * @param mixed $variable
     * @param int $time
     * @return null
     */
    public function trace($message = null, $variable = null, $time = null)
    {
        if(!empty($variable)) {
            $message = $message.': '.var_export($variable, true);
        } else {
            $message = $message.': NULL';
        }
        $this->add($message, MAGEBRIDGE_DEBUG_TRACE, $time);
    }

    /*
     * Method to add a new feedback message
     *  
     * @access public
     * @param string $message
     * @param int $time
     * @return null
     */
    public function feedback($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_FEEDBACK, $time);
    }

    /*
     * Method to add a new profiler message
     *  
     * @access public
     * @param string $message
     * @param int $time
     * @return null
     */
    public function profiler($message = null, $time = null)
    {
        $this->add($message, MAGEBRIDGE_DEBUG_PROFILER, $time);
    }
}
