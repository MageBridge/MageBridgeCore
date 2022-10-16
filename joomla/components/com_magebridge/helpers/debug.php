<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper for dealing with debugging
 */
class MageBridgeDebugHelper
{
    /**
     * MageBridgeDebugHelper constructor.
     */
    public function __construct()
    {
        $this->bridge = MageBridgeModelBridge::getInstance();
        $this->register = MageBridgeModelRegister::getInstance();
        $this->request = MageBridgeUrlHelper::getRequest();
        $this->app = JFactory::getApplication();
    }

    /**
     * @return bool
     */
    public function isDebugBarAllowed()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            return false;
        }

        if (MageBridgeModelDebug::isDebug() == false) {
            return false;
        }

        if (MageBridgeModelConfig::load('debug_bar') == false) {
            return false;
        }

        return true;
    }

    /**
     * Helper-method to set the debugging information
     *
     * @deprecated
     */
    public function addDebug()
    {
        $this->addDebugBar();
    }

    /**
     * Helper-method to set the debugging information
     */
    public function addDebugBar()
    {
        // Do not add debugging information when posting or redirecting
        if ($this->isDebugBarAllowed() == false) {
            return;
        }

        // Debug the MageBridge request
        if (MageBridgeModelConfig::load('debug_bar_request')) {
            $this->addGenericInformation();
            $this->addPageInformation();
        }

        // Add store information
        $this->addStore();

        // Add category information
        $this->addCurrentCategoryId();

        // Add product information
        $this->addCurrentProductId();

        // Add information on bridge-segments
        $this->addDebugBarParts();
    }

    /**
     * Add generic information
     */
    public function addGenericInformation()
    {
        $request = $this->request;
        $url = $this->bridge->getMagentoUrl() . $request;

        if (empty($request)) {
            $request = '[empty]';
        }

        $Itemid = $this->app->input->getInt('Itemid');
        $rootItemId = $this->getRootItemId();
        $menu_message = 'Menu-Item: ' . $Itemid;

        if ($rootItemId == $Itemid) {
            $menu_message .= ' (Root Menu-Item)';
        }

        JError::raiseNotice('notice', $menu_message);
        JError::raiseNotice('notice', JText::sprintf('Page request: %s', (!empty($request)) ? $request : '[empty]'));
        JError::raiseNotice('notice', JText::sprintf('Original request: %s', MageBridgeUrlHelper::getOriginalRequest()));
        JError::raiseNotice('notice', JText::sprintf('Received request: %s', $this->bridge->getSessionData('request')));
        JError::raiseNotice('notice', JText::sprintf('Received referer: %s', $this->bridge->getSessionData('referer')));
        JError::raiseNotice('notice', JText::sprintf('Current referer: %s', $this->bridge->getHttpReferer()));
        JError::raiseNotice('notice', JText::sprintf('Magento request: <a href="%s" target="_new">%s</a>', $url, $url));
        JError::raiseNotice('notice', JText::sprintf('Magento session: %s', $this->bridge->getMageSession()));
    }

    /**
     * @return bool
     */
    protected function getRootItemId()
    {
        $rootItem = MageBridgeUrlHelper::getRootItem();
        return ($rootItem) ? $rootItem->id : false;
    }

    /**
     * Add information per pages
     */
    public function addPageInformation()
    {
        if (MageBridgeTemplateHelper::isCategoryPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isCategoryPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isProductPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isProductPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isCatalogPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isCatalogPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isCustomerPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isCustomerPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isCartPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isCartPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isCheckoutPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isCheckoutPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isSalesPage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isSalesPage() == TRUE'));
        }

        if (MageBridgeTemplateHelper::isHomePage()) {
            JError::raiseNotice('notice', JText::_('MageBridgeTemplateHelper::isHomePage() == TRUE'));
        }
    }

    /**
     * Add store information
     */
    public function addStore()
    {
        if (MageBridgeModelConfig::load('debug_bar_store')) {
            JError::raiseNotice('notice', JText::sprintf('Magento store loaded: %s (%s)', $this->bridge->getSessionData('store_name'), $this->bridge->getSessionData('store_code')));
        }
    }

    /**
     * Add category information
     */
    public function addCurrentCategoryId()
    {
        $category_id = $this->bridge->getSessionData('current_category_id');
        if ($category_id > 0) {
            JError::raiseNotice('notice', JText::sprintf('Magento category: %d', $category_id));
        }
    }

    /**
     * Add product information
     */
    public function addCurrentProductId()
    {
        $product_id = $this->bridge->getSessionData('current_product_id');
        if ($product_id > 0) {
            JError::raiseNotice('notice', JText::sprintf('Magento product: %d', $product_id));
        }
    }

    /**
     * @return bool
     */
    public function addDebugBarParts()
    {
        if (MageBridgeModelConfig::load('debug_bar_parts') == false) {
            return false;
        }

        $i = 0;
        $segments = $this->register->getRegister();

        foreach ($segments as $segment) {
            if (!isset($segment['status']) || $segment['status'] != 1) {
                continue;
            }

            switch ($segment['type']) {
                case 'breadcrumbs':
                case 'meta':
                case 'debug':
                case 'headers':
                case 'events':
                    JError::raiseNotice('notice', JText::sprintf('Magento [%d]: %s', $i, ucfirst($segment['type'])));
                    break;
                case 'api':
                    JError::raiseNotice('notice', JText::sprintf('Magento [%d]: API resource "%s"', $i, $segment['name']));
                    break;
                case 'block':
                    JError::raiseNotice('notice', JText::sprintf('Magento [%d]: Block "%s"', $i, $segment['name']));
                    break;
                default:
                    $name = (isset($segment['name'])) ? $segment['name'] : null;
                    $type = (isset($segment['type'])) ? $segment['type'] : null;
                    JError::raiseNotice('notice', JText::sprintf('Magento [%d]: type %s, name %s', $i, $type, $name));
                    break;
            }
            $i++;
        }

        return true;
    }
}
