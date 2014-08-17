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

// Import the loader
require_once dirname(__FILE__).'/loader.php';

/**
 * Yireo Dispatcher
 *
 * @package Yireo
 */
class YireoDispatcher
{
    static public function dispatch()
    {
        // Fetch URL-variables
        $option = JRequest::getCmd('option');
        $view = JRequest::getCmd('view');

        // Construct the controller-prefix
        $prefix = ucfirst(preg_replace('/^com_/', '', $option));

        // Check for a corresponding view-controller
        if(!empty($view)) {
            $controllerFile = JPATH_COMPONENT.'/controllers/'.$view.'.php';
            if(file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerClass = $prefix.'Controller'.ucfirst($view);
                if(class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                }
            }
        }

        // Return to the default component-controller
        if(empty($controller)) {
            $controllerFile = JPATH_COMPONENT.'/controller.php';
            if(file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerClass = $prefix.'Controller';
                if(class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                }
            }
        }

        // Default to YireoController
        if(empty($controller)) {
            $controller = new YireoController();
        }

        // Perform the Request task
        $controller->execute( JRequest::getCmd('task'));
        $controller->redirect();
    }
}
