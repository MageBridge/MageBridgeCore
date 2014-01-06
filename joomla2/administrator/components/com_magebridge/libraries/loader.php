<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Load the Yireo Library loader if possible
if(is_file(JPATH_LIBRARIES.'/yireo/loader.php')) {
    require_once JPATH_LIBRARIES.'/yireo/loader.php';
}

// Include the original Joomla! loader
require_once(JPATH_LIBRARIES.'/loader.php');

// If the Joomla! autoloader exists, add it to SPL
if (function_exists('__autoload')) {
    spl_autoload_register('__autoload');
}

// YireoLibLoader-function
if(!function_exists('YireoLibraryLoader')) {
    function YireoLibraryLoader($name = null) {
        // Preliminary check
        if(substr($name, 0, 5) != 'Yireo') return false;

        // Construct the filename
        $filename = null;
        switch($name) {
            case 'YireoDispatcher':
                $filename = 'dispatcher';
                break;
            case 'YireoModel':
            case 'YireoAbstractModel':
                $filename = 'model';
                break;
            case 'YireoView':
            case 'YireoCommonView':
            case 'YireoAbstractView':
                $filename = 'view';
                break;
            case 'YireoViewForm':
                $filename = 'view/form';
                break;
            case 'YireoViewHome':
                $filename = 'view/home';
                break;
            case 'YireoViewHomeAjax':
                $filename = 'view/home_ajax';
                break;
            case 'YireoViewList':
                $filename = 'view/list';
                break;
            case 'YireoController':
            case 'YireoCommonController':
            case 'YireoAbstractController':
                $filename = 'controller';
                break;
            case 'YireoController':
            case 'YireoAbstractController':
                $filename = 'controller';
                break;
            case 'YireoTable':
                $filename = 'table';
                break;
            case 'YireoHelper':
                $filename = 'helper';
                break;
            case 'YireoHelperView':
                $filename = 'helper/view';
                break;
            case 'YireoHelperInstall':
                $filename = 'helper/install';
                break;
            case 'YireoHelperTable':
                $filename = 'helper/table';
                break;
        }

        // Try to include the needed file
        if(!empty($filename)) {
            include_once dirname(__FILE__).'/'.$filename.'.php';
            return true;
        }

        return false;
    }
}

// Add our own loader-function to SPL
spl_autoload_register('YireoLibraryLoader');

