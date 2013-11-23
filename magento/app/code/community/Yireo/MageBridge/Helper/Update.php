<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2013
 * @license Open Source License
 * @link http://www.yireo.com
 */

class Yireo_MageBridge_Helper_Update extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to remove obsolete files
     *
     * @access public
     * @param null
     * @return bool
     */
    public function removeFiles()
    {
        $files = array(
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'default'.DS.'layout'.DS.'magebridge.xml',
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'magebridge'.DS.'layout',
            BP.DS.'app'.DS.'design'.DS.'frontend'.DS.'default'.DS.'magebridge'.DS.'template'.DS.'magebridge'.DS.'page.phtml',
            BP.DS.'app'.DS.'code'.DS.'community'.DS.'Yireo'.DS.'MageBridge'.DS.'controllers'.DS.'IndexController.php',
        );
    }

    /*
     * Helper-method to remove a directory recursively
     *
     * @access public
     * @param string $directory
     * @return bool
     */
    public function recursiveDelete($directory) 
    {
        $pointer = opendir($directory);
        if($pointer) {
            while($f = readdir($pointer)) {
                $file = $directory.DS.$f;
                if($f == '.' || $f == '..') {
                    continue;

                } elseif(is_dir($file) && !is_link($file)) {
                    self::recursiveDelete($file);

                } else {
                    @unlink($file);
                }
            }
            closedir($pointer);
            @rmdir($directory);
        }
    }
}
