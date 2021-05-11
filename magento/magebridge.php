<?php
/**
 * Magento Bridge
 *
 * @author Yireo
 * @package Magento Bridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

// Basic PHP settings that can be overwritten
ini_set('zlib.output_compression', 0);
ini_set('display_errors', 1);

// Check for the maintence-file
$maintenanceFile = 'maintenance.flag';
if (file_exists($maintenanceFile)) {
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    exit;
}

// Use this for profiling
define('yireo_starttime', microtime(true));
if(function_exists('yireo_benchmark') == false) {
    function yireo_benchmark($title) {
        $yireo_totaltime = round(microtime(true) - yireo_starttime, 4);
        Mage::getSingleton('magebridge/debug')->profiler($title.': '.$yireo_totaltime.' seconds');
    }
}

// Initialize the bridge
require_once 'magebridge.class.php';
$magebridge = new MageBridge();

// Mask this request
$magebridge->premask();
        

// Support for Magento Compiler
$compilerConfig = 'includes/config.php';
if (file_exists($compilerConfig)) {
    include $compilerConfig;
}

// Initialize the Magento application
require_once 'app/Mage.php';
try {

    // Determine the Mage::app() arguments from the bridge
    $app_value = $magebridge->getMeta('app_value');
    $app_type = $magebridge->getMeta('app_type');
    if (is_array($app_type)){
        $app_type = empty($app_type) ? null : array_shift($app_type);
    }
    if (is_array($app_value)){
        $app_value = empty($app_value) ? null : array_shift($app_value);
    }

    // Doublecheck certain values
    if($app_type == 'website' && $app_value != 'admin') $app_value = (int)$app_value;
    if($app_value == 'admin') $app_type = null;

    // Initialize app_time for benchmarking
    $app_time = time();

    // Switch debugging
    if($magebridge->getMeta('debug')) {
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        Varien_Profiler::enable();
        Mage::setIsDeveloperMode(true);
    }

    // Switch debugging
    if($magebridge->getMeta('debug_display_errors')) {
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        Varien_Profiler::enable();
    } else {
        ini_set('display_errors', 0);
    }

    // Make sure the headers-sent warning does not throw an exception
    Mage::$headersSentThrowsException = false;

    // Determine what to do
    $task = 'app';

    // Start the Magento application
    if(!empty($app_value) && !empty($app_type)) {
        Mage::$task($app_value, $app_type);
    } elseif(!empty($app_value)) {
        Mage::$task($app_value);
    } else {
        Mage::$task();
    }

    // End the task if running the normal Magento procedure
    if($task == 'run') exit;

    // @todo: Set custom-logging
    //if($magebridge->getMeta('debug_custom_log')) {
    //    $customErrorLog = Mage::getBaseDir().DS.'var'.DS.'log'.DS.'php_errors.log';
    //    ini_set('error_log', $customErrorLog);
    //}

    // Debugging
    $debug = Mage::getSingleton('magebridge/debug');
    if(!empty($debug)) {
        $debug->notice("Mage::app($app_value,$app_type)", $app_time);
    }

    // Benchmarking
    yireo_benchmark('Mage::app()');

} catch(Exception $e) {

    // Debugging
    $debug = Mage::getSingleton('magebridge/debug');
    if(!empty($debug)) {
        $debug->notice("Mage::app($app_value,$app_type) failed to start", $app_time);
        $debug->notice("Fallback to Mage::app()", $app_time);
    }

    // Start the Magento application with default values
    Mage::app();
}

// Run the bridge
$magebridge->run();

// End
