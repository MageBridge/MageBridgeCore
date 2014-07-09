<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Include libraries
require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/libraries/controller.php';

/**
 * MageBridge Controller
 */
class MageBridgeController extends YireoController
{

    /**
     * Constructor
     * @package MageBridge
     */
    public function __construct()
    {
        $this->_default_view = 'home';

        parent::__construct();

        // Register extra tasks
        $this->registerTask('login', 'ssoCheck');
        $this->registerTask('logout', 'ssoCheck');

        $request = JRequest::getVar('request');
        if (JRequest::getCmd('view') == 'root' && !empty($request)) {
            JRequest::setVar('format', 'raw');
        }
    }

    /*
     * Method to redirect back to home
     *
     * @param null
     * @return null
     */
    public function home()
    {
        $link = JRoute::_('index.php?option=com_magebridge');
        return $this->setRedirect($link);
    }

    /*
     * Method to display the views layout
     *
     * @param null
     * @return null
     */
    public function display($cachable = false, $urlparams = false)
    {
        // If the caching view is called, perform the cache-task instead
        if (JRequest::getCmd('view') == 'cache') {
            return $this->cache();    
        }

        // Redirect to the Magento Admin Panel
        if (JRequest::getCmd('view') == 'magento') {
            $link = MagebridgeModelConfig::load('url').'index.php/'.MagebridgeModelConfig::load('backend');
            return $this->setRedirect($link);
        }

        // Redirect to the Yireo Forum
        if (JRequest::getCmd('view') == 'forum') return $this->setRedirect('http://www.yireo.com/forum/');

        // Redirect to the Yireo Tutorials
        if (JRequest::getCmd('view') == 'tutorials') return $this->setRedirect('http://www.yireo.com/tutorials/magebridge/');

        parent::display();
    }

    /*
     * Method to flush caching
     *
     * @param null
     * @return null
     */
    public function cache()
    {
        // Validate whether this task is allowed
        if ($this->_validate(false) == false) return false;

        // Clean the backend cache 
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->clean();
        
        // Clean the frontend cache 
        $cache = JFactory::getCache('com_magebridge');
        $cache->clean();

        // Build the next URL
        $view = JRequest::getCmd('view');
        if ($view == 'cache') $view = 'home';
        $link = 'index.php?option=com_magebridge&view='.$view;

        // Redirect
        $msg = 'Cache cleaned';
        $this->setRedirect($link, $msg);
        return true;
    }

    /*
     * Method to toggle the configuration mode (advanced/basic)
     *
     * @param null
     * @return null
     */
    public function toggleMode()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) return false;

        // Determine the toggle value
        $name = 'advanced';
        $value = MagebridgeModelConfig::load($name);
        if ($value == 1) {
            $value = 0;
        } else {
            $value = 1;
        }
        MagebridgeModelConfig::saveValue($name, $value);
        
        $link = 'index.php?option=com_magebridge&view=config';
        $this->setRedirect($link);
    }

    /*
     * Method to upgrade specific extensions
     *
     * @param null
     * @return null
     */
    public function update()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) return false;

        // Get the selected packages
        $packages = JRequest::getVar('packages');

        // Get the model and update the packages
        $model = $this->getModel('update');
        $model->updateAll($packages);

        // Clean the MageBridge cache
        $cache = JFactory::getCache('com_magebridge.admin');
        $cache->clean();

        // Clean the Joomla! plugins cache
        $options = array('defaultgroup' => 'com_plugins', 'cachebase' => JPATH_ADMINISTRATOR.'/cache');
        $cache = JCache::getInstance('callback', $options);
        $cache->clean();

        // Initialize the helper
        $helper = new MageBridgeInstallHelper();

        // Upgrade the database tables
        $helper->updateQueries();

        // Redirect
        $link = 'index.php?option=com_magebridge&view=update';
        $this->setRedirect($link);
    }

    /*
     * Method to perform update queries
     *
     * @param null
     * @return null
     */
    public function updateQueries()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) return false;

        // Initialize the helper
        $helper = new MageBridgeInstallHelper();

        // Upgrade the database tables
        $helper->updateQueries();

        // Run the helper to remove obsolete files
        YireoHelperInstall::remove();

        // Clean the Joomla! plugins cache
        $options = array('defaultgroup' => 'com_plugins', 'cachebase' => JPATH_ADMINISTRATOR.'/cache');
        $cache = JCache::getInstance('callback', $options);
        $cache->clean();

        // Redirect
        $link = 'index.php?option=com_magebridge&view=update';
        $msg = JText::_('LIB_YIREO_CONTROLLER_DB_UPGRADED');
        $this->setRedirect($link, $msg);
    }

    /*
     * Method  to truncate the logs
     *
     * @param null
     * @return null
     */
    public function delete()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) return false;

        // Only clean items for the right view
        if (JRequest::getCmd('view') == 'logs') {

            // Clean up the database
            $db = JFactory::getDBO();
            $db->setQuery('DELETE FROM #__magebridge_log WHERE 1 = 1');
            $db->query();

            // Clean up the database
            $app = JFactory::getApplication();
            $file = $app->getCfg('log_path').'/magebridge.txt';
            file_put_contents($file, null);

            // Redirect
            $msg = JText::_('LIB_YIREO_CONTROLLER_LOGS_TRUNCATED');
            $link = 'index.php?option=com_magebridge&view=logs';
            $this->setRedirect($link, $msg);
            return;
        }
            
        // Otherwise display by default
        $this->display();
    }

    /*
     * Method 
     *
     * @param null
     * @return null
     */
    public function export()
    {
        // Validate whether this task is allowed
        if ($this->_validate() == false) return false;

        // Only clean items for the right view
        if (JRequest::getCmd('view') == 'logs') {
            $link = 'index.php?option=com_magebridge&view=logs&format=csv';
            $this->setRedirect($link);
            return;
        }

        // Otherwise display by default
        $this->display();
    }

    /*
     * Method to check SSO coming from Magento
     *
     * @param null
     * @return null
     */
    public function ssoCheck()
    {
        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        if (!$user->guest) {
            MageBridgeModelUserSSO::checkSSOLogin();
            $application->close();
        } else {
            $this->setRedirect(JURI::base());
        }
    }

    /*
     * Method to validate a change-request
     *
     * @param boolean $check_token
     * @param boolean $check_demo
     * @return boolean
     */
    protected function _validate($check_token = true, $check_demo = true)
    {
        // Check the token
        if ($check_token == true && (JRequest::checkToken('post') == false && JRequest::checkToken('get') == false)) {
            $msg = JText::_('JINVALID_TOKEN');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect( $link, $msg );
            return false;
        }

        // Check demo-access
        if ($check_demo == true && MageBridgeAclHelper::isDemo() == true) {
            $msg = JText::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect( $link, $msg );
            return false;
        }

        return true;
    }
}
