<?php
/**
 * JoomlaApi
 *
 * @author Yireo
 * @package JoomlaApi
 * @copyright Copyright 2015
 * @license Open Source License v3
 * @link http://www.yireo.com
 */

/*
 * JoomlaApi observer 
 */
class Yireo_JoomlaApi_Model_Observer
{
    /*
     * Method fired on the event <controller_action_predispatch>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_JoomlaApi_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        $this->initJoomla();

        return $this;
    }

    /*
     * Method to initialize Joomla!
     *
     * @access public
     * @param null
     * @return Yireo_JoomlaApi_Model_Observer
     */
    public function initJoomla()
    {
        // Get the Joomla! path
        $root = Mage::helper('joomlaapi')->getJoomlaPath();
        if(empty($root) || $root == '.' || $root == '..') {
            return false;
        }

        // Check if this is a Joomla! path
        if(!is_dir($root) || !is_file($root.'/includes/defines.php')) {
            return false;
        }

        // Necessary definitions
        if(!defined('_JEXEC')) define('_JEXEC', 1);
        if(!defined('JPATH_BASE')) define('JPATH_BASE', $root);

        // Go to Joomla!
        chdir(JPATH_BASE);

        // Include the framework
        require_once (JPATH_BASE.'/includes/defines.php');
        require_once (JPATH_BASE.'/includes/framework.php');
        jimport('joomla.environment.request');
        jimport('joomla.database.database');

        // Start the application
        $startApp = false;
        if($startApp) {
            $app = JFactory::getApplication('site');
            $app->initialise();
        }
    }
}
