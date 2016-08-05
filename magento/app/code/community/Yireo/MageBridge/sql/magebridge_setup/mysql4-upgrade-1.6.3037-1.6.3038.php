<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

$installer = $this;
Mage::log('Running MageBridge cleanup');

// Remove obsolete files
$base = BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'MageBridge'.DS;
$files = array(
    $base.'Block'.DS.'Credits.php',
    $base.'Model'.DS.'Email.php',
);
foreach($files as $file) {
    if(file_exists($file)) {
        @unlink($file);
    }
}
