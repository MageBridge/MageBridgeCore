<?php
/**
 * Joomla! MageBridge Preloader - System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

/**
 * MageBridge Preloader System Plugin
 */
class plgSystemMageBridgePre extends JPlugin
{
    /**
     * Event onAfterInitialise
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterInitialise()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        // Perform actions on the frontend
        $application = JFactory::getApplication();
        if ($application->isSite()) {

            // Detect whether we can load the module-helper
            $classes = get_declared_classes();
            if (!in_array('JModuleHelper', $classes) && !in_array('jmodulehelper', $classes)) {
                $loadModuleHelper = true;
            } else {
                $loadModuleHelper = false;
            }

            // Import the custom module helper - this is needed to make it possible to flush certain positions 
            if ($this->getParam('override_modulehelper', 1) == 1 && $loadModuleHelper == true) {
                $component_path = JPATH_SITE.'/components/com_magebridge/';
                if (MageBridgeHelper::isJoomlaVersion('2.5')) {
                    @include_once($component_path.'rewrite/25/joomla/application/module/helper.php');
                } else if (MageBridgeHelper::isJoomlaVersion('3.0')) {
                    @include_once($component_path.'rewrite/30/joomla/application/module/helper.php');
                } else if (MageBridgeHelper::isJoomlaVersion('3.1')) {
                    @include_once($component_path.'rewrite/31/cms/application/module/helper.php');
                } else {
                    @include_once($component_path.'rewrite/32/cms/application/module/helper.php');
                }
            }
        }

        // Check for postlogin-cookie
        if(isset($_COOKIE['mb_postlogin']) && !empty($_COOKIE['mb_postlogin'])) {

            // If the user is already logged in, remove the cookie
            if(JFactory::getUser()->id > 0) {
                setcookie('mb_postlogin', '', time() - 3600, '/', '.'.JURI::getInstance()->toString(array('host')));
            }

            // Otherwise decrypt the cookie and use it here
            $data = MageBridgeEncryptionHelper::decrypt($_COOKIE['mb_postlogin']);
            if(!empty($data)) $customer_email = $data;
        }

        // Perform a postlogin if needed
        $post = JRequest::get('post');
        if (empty($post)) {
            $postlogin_userevents = ($this->getParams()->get('postlogin_userevents', 0) == 1) ? true : false;
            if(empty($customer_email)) $customer_email = MageBridgeModelBridge::getInstance()->getSessionData('customer/email');
            if (!empty($customer_email)) MageBridge::getUser()->postlogin($customer_email, null, $postlogin_userevents);
        }
    }

    /**
     * Event onAfterRoute
     *
     * @access public
     * @param null
     * @return null
     */
    /*public function onAfterRoute()
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;
    }*/

    /*
     * Event onPrepareModuleList (used by Advanced Module Manager)
     */
    public function onPrepareModuleList(&$modules)
    {
        // Don't do anything if MageBridge is not enabled 
        if ($this->isEnabled() == false) return false;

        foreach ($modules as $id => $module) {
            if (MageBridgeTemplateHelper::allowPosition($module->position) == false) {
                unset($modules[$id]);
                continue;
            } 
        }
    }

    /**
     * Load the parameters
     *
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        return $this->params;
    }

    /**
     * Load a specific parameter
     *
     * @access private
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getParam($name, $default = null)
    {
        return $this->getParams()->get($name, $default);
    }

    /**
     * Simple check to see if MageBridge exists
     * 
     * @access private
     * @param null
     * @return bool
     */
    private function isEnabled()
    {
        // Import the MageBridge autoloader
        include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

        // Check for the file only
        if (is_file(JPATH_SITE.'/components/com_magebridge/models/config.php')) {
            return true;
        }
        return false;
    }
}
