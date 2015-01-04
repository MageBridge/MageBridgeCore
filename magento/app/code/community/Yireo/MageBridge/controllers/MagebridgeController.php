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

/**
 * MageBridge admin controller
 */
class Yireo_MageBridge_MagebridgeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Common method to initialize each action
     *
     * @access protected
     * @param null
     * @return $this
     */
    protected function _initAction()
    {
        // Give a warning if Mage::getResourceModel('api/user_collection') returns zero
        $collection = Mage::getResourceModel('api/user_collection');
        if(!count($collection) > 0) {
            Mage::getModel('adminhtml/session')->addError('You have not configured any API-user yet [MageBridge Installation Guide]');
        }

        // Fetch the current store
        $store = Mage::app()->getStore(Mage::getModel('magebridge/core')->getStore());

        // Give a warning if the URL suffix is still set to ".html"
        if($store->getConfig('catalog/seo/product_url_suffix') == '.html' || $store->getConfig('catalog/seo/category_url_suffix') == '.html') {
            Mage::getModel('adminhtml/session')->addError('You have configured the URL-suffix ".html" which conflicts with Joomla! [MageBridge Magento Settings Guide]');
        }

        // Give a warning if the setting "Redirect to Base URL" is still enabled
        if($store->getConfig('web/url/redirect_to_base') == '1') {
            Mage::getModel('adminhtml/session')->addError('The setting "Auto-redirect to Base URL" is not configured properly [MageBridge Magento Settings Guide]');
        }

        // Load the layout
        $this->loadLayout()
            ->_setActiveMenu('cms/magebridge')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('CMS'), Mage::helper('adminhtml')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('MageBridge'), Mage::helper('adminhtml')->__('MageBridge'))
        ;

        $this->prependTitle(array('MageBridge', 'CMS'));
        return $this;
    }

    /**
     * Settings page
     *
     * @access public
     * @param null
     * @return null
     */
    public function indexAction()
    {
        if(strlen(Mage::helper('magebridge')->getLicenseKey()) == '') {
            $block = 'license';
        } else {
            $block = 'settings';
        }
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/'.$block))
            ->renderLayout();
    }

    /**
     * Settings page
     *
     * @access public
     * @param null
     * @return null
     */
    public function settingsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/settings'))
            ->renderLayout();
    }

    /**
     * Support Key page
     *
     * @access public
     * @param null
     * @return null
     */
    public function supportkeyAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/license'))
            ->renderLayout();
    }

    /**
     * System Check page 
     *
     * @access public
     * @param null
     * @return null
     */
    public function checkAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/check'))
            ->renderLayout();
    }

    /**
     * Browse page 
     *
     * @access public
     * @param null
     * @return null
     */
    public function browseAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/browse'))
            ->renderLayout();
    }

    /**
     * Log page 
     *
     * @access public
     * @param null
     * @return null
     */
    public function logAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/log'))
            ->renderLayout();
    }

    /**
     * Wipe a log
     *
     * @access public
     * @param null
     * @return null
     */
    public function wipelogAction()
    {
        @mkdir(BP.DS.'var'.DS.'log');
        $type = $this->getRequest()->getParam('type');
        switch($type) {
            case 'system':
                $file = BP.DS.'var'.DS.'log'.DS.'system.log';
                break;
            case 'exception':
                $file = BP.DS.'var'.DS.'log'.DS.'exception.log';
                break;
            default:
                $file = BP.DS.'var'.DS.'log'.DS.'magebridge.log';
                break;
        }
        file_put_contents($file, '');

        $url = Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/log', array('type' => $type));
        $this->getResponse()->setRedirect($url);
    }

    /**
     * Updates page (which calls for AJAX)
     *
     * @access public
     * @param null
     * @return null
     */
    public function updatesAction()
    {
        if(defined('COMPILER_INCLUDE_PATH')) {
            Mage::getModel('adminhtml/session')->addError('Magento Compiler is enabled. Disable it before making any changes to your site');
        }

        Mage::getModel('magebridge/update')->setFilesUmask();
        Mage::helper('magebridge/update')->renameConfigPaths();
        Mage::getConfig()->removeCache();

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/updates'))
            ->renderLayout();
    }

    /**
     * Credits page
     *
     * @access public
     * @param null
     * @return null
     */
    public function creditsAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('magebridge/credits'))
            ->renderLayout();
    }

    /**
     * Perform an update through AJAX
     *
     * @access public
     * @param null
     * @return null
     */
    public function doupdateAction()
    {
        $update = Mage::getSingleton('magebridge/update');
        if($update->upgradeNeeded() == true) {
            $status = $update->doUpgrade();
        } else {
            $status = 'No upgrade needed';
        }

        $response = new Varien_Object();
        $response->setError(0);
        $response->setMessage($status);
        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Save all the MageBridge settings
     *
     * @access public
     * @param null
     * @return null
     */
    public function saveAction()
    {
        $page = 'adminhtml/magebridge/index';
        if ($data = $this->getRequest()->getPost()) {
                
            if(isset($data['license_key'])) {
                Mage::getConfig()->saveConfig('magebridge/hidden/support_key', trim($data['license_key']));
                $page = 'adminhtml/magebridge/supportkey';
            }

            if(!empty($data['event_forwarding'])) {
                foreach($data['event_forwarding'] as $name => $value) {
                    Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$name, $value);
                }
            }

            Mage::getModel('adminhtml/session')->addSuccess('Settings saved');
            Mage::getConfig()->removeCache();
            
        }

        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset API settings to their default value
     *
     * @access public
     * @param null
     * @return null
     */
    public function resetapiAction()
    {
        $page = 'adminhtml/magebridge/index';

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('core/config_data');
        foreach(array('api_url', 'api_user', 'api_key') as $path) {  
            $query = 'DELETE FROM `'.$table.'` WHERE path = "magebridge/settings/'.$path.'";';
            $data = $connection->query($query);
        }

        Mage::getConfig()->deleteConfig('magebridge/settings/bridge_all');
        Mage::getConfig()->removeCache();

        Mage::getModel('adminhtml/session')->addSuccess('API-details are reset to default');
            
        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset usermapping 
     *
     * @access public
     * @param null
     * @return null
     */
    public function resetusermapAction()
    {
        $page = 'adminhtml/magebridge/index';

        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $table = Mage::getSingleton('core/resource')->getTableName('magebridge/customer_joomla');
        $query = 'DELETE FROM `'.$table.'`';
        $data = $connection->query($query);

        Mage::getModel('adminhtml/session')->addSuccess('User-mapping is removed');
            
        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Reset all MageBridge events to their recommended value
     *
     * @access public
     * @param null
     * @return null
     */
    public function reseteventsAction()
    {
        $page = 'adminhtml/magebridge/index';

        $events = Mage::getModel('magebridge/listener')->getEvents();
        foreach($events as $event) {
            Mage::getConfig()->saveConfig('magebridge/settings/event_forwarding/'.$event[0], $event[1]);
        }

        Mage::getConfig()->removeCache();
        Mage::getModel('adminhtml/session')->addSuccess('Events-settings are reset to their recommended value');
            
        $url = Mage::getModel('adminhtml/url')->getUrl($page);
        $this->getResponse()->setRedirect($url);
    }

    /*
     * Foo Bar
     *
     * @access public
     * @param null
     * @return null
     */
    public function fooAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /*
     * Method to prepend a page-title
     *
     * @access public
     * @param $subtitles array
     * @return null
     */
    protected function prependTitle($subtitles)
    {
        $headBlock = $this->getLayout()->getBlock('head');
        $title = $headBlock->getTitle();
        if(!is_array($subtitles)) $subtitles = array($subtitles);
        $headBlock->setTitle(implode(' / ', $subtitles).' / '.$title);
    }
}
