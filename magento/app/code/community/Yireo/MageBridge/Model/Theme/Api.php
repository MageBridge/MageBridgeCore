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

/*
 * MageBridge API-model for themes
 */
class Yireo_MageBridge_Model_Theme_Api extends Mage_Api_Model_Resource_Abstract
{
    /*
     * Method to get a list of themes
     *
     * @access public
     * @param null
     * @return array
     */
    public function items()
    {
        $root = BP.DS.'app'.DS.'design'.DS.'frontend';
        $folders = scandir($root);
        foreach($folders as $folder) {

            if(is_dir($root.DS.$folder) == false) continue;
            if($folder == '.' || $folder == '..') continue;

            $subfolders = scandir($root.DS.$folder);
            foreach($subfolders as $subfolder) {

                if(is_dir($root.DS.$folder.DS.$subfolder) == false) continue;
                if($subfolder == '.' || $subfolder == '..') continue;
                if($folder == 'base' && $subfolder == 'default') continue;
                if($folder == 'default' && $subfolder == 'default') continue;

                $options[] = array(
                    'value' => $folder.'/'.$subfolder,
                    'label' => $folder.'/'.$subfolder,
                );
            }
        }

        return $options;
    }
}
