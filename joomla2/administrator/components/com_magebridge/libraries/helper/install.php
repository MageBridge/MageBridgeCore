<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include libraries
require_once dirname(dirname(__FILE__)).'/loader.php';

/** 
 * Yireo Install Helper
 */
class YireoHelperInstall
{
    static public function remove($files = array())
    {
        if(empty($files)) $files = YireoHelper::getData('obsolete_files');

        if(!empty($files)) {
            foreach($files as $file) {
                if(file_exists($file)) {
                    jimport('joomla.filesystem.file'); 
                    JFile::delete($file);
                }
            }
        }
    }
}
