<?php
/**
 * Joomla! module MageBridge: Store switcher
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link	  https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Helper-class for the module
 */
class ModMageBridgeSwitcherHelper
{
    /**
     * Method to be called once the MageBridge is loaded
     *
     * @access public
     *
     * @param JRegistry $params
     *
     * @return array
     */
    public static function register($params = null)
    {
        // Initialize the register
        $register = [];
        $register[] = ['api', 'magebridge_storeviews.hierarchy'];

        return $register;
    }

    /**
     * Fetch the content from the bridge
     *
     * @access public
     *
     * @param JRegistry $params
     *
     * @return array
     */
    public static function build($params = null)
    {
        $bridge = MageBridgeModelBridge::getInstance();
        $stores = $bridge->getAPI('magebridge_storeviews.hierarchy');

        if (empty($stores) || !is_array($stores)) {
            return null;
        }

        $storeId = $params->get('store_id');
        foreach ($stores as $store) {
            if ($store['value'] == $storeId) {
                return [$store];
                break;
            }
        }

        return $stores;
    }

    /**
     * Generate a HTML selectbox
     *
     * @access public
     *
     * @param array	 $stores
     * @param JRegistry $params
     *
     * @return string
     */
    public static function getFullSelect($stores, $params = null)
    {
        $options = [];
        $currentType = self::getCurrentStoreType();
        $currentName = self::getCurrentStoreName();
        $currentValue = ($currentType == 'store') ? 'v:' . $currentName : 'g:' . $currentName;
        $showGroups = (count($stores) > 1) ? true : false;

        if (!empty($stores) && is_array($stores)) {
            foreach ($stores as $group) {
                if ($group['website'] != MageBridgeModelConfig::load('website')) {
                    continue;
                }

                if ($showGroups) {
                    $options[] = [
                        'value' => 'g:' . $group['value'],
                        'label' => $group['label'],];
                }

                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        $labelPrefix = ($showGroups) ? '-- ' : null;
                        $options[] = [
                            'value' => 'v:' . $child['value'],
                            'label' => $labelPrefix . $child['label'],];
                    }
                }
            }
        }

        array_unshift($options, ['value' => '', 'label' => '-- Select --']);
        $attribs = 'onChange="document.forms[\'mbswitcher\'].submit();"';

        return JHtml::_('select.genericlist', $options, 'magebridge_store', $attribs, 'value', 'label', $currentValue);
    }

    /**
     * Return a list of Root Menu Items associated with the current Root Menu Item
     *
     * @access public
     *
     * @param null
     *
     * @return array
     */
    public static function getRootItemAssociations()
    {
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc == false) {
            return false;
        }

        $root_item = MageBridgeUrlHelper::getRootItem();

        if ($root_item == false) {
            return false;
        }

        $associations = MenusHelper::getAssociations($root_item->id);

        return $associations;
    }

    /**
     * Return the Root Menu Item ID per language
     *
     * @access public
     *
     * @param string $language
     *
     * @return int
     */
    public static function getRootItemIdByLanguage($language)
    {
        $app = JFactory::getApplication();
        $currentItemId = $app->input->getInt('Itemid');

        $rootItemAssociations = self::getRootItemAssociations();

        if (empty($rootItemAssociations)) {
            return $currentItemId;
        }

        foreach ($rootItemAssociations as $rootItemLanguage => $rootItemId) {
            if ($language == $rootItemLanguage) {
                return $rootItemId;
            }

            if ($language == str_replace('-', '_', $rootItemLanguage)) {
                return $rootItemId;
            }
        }

        return $currentItemId;
    }

    /**
     * Return a list of store languages
     *
     * @access public
     *
     * @param array	 $stores
     * @param JRegistry $params
     *
     * @return string
     */
    public static function getLanguages($stores, $params = null)
    {
        // Base variables
        $languages = [];
        $currentName = (MageBridgeStoreHelper::getInstance()->getAppType() == 'store') ? MageBridgeStoreHelper::getInstance()->getAppValue() : null;
        $storeUrls = MageBridgeModelBridge::getInstance()->getSessionData('store_urls');

        // Generic Joomla! variables
        $app = JFactory::getApplication();

        // Loop through the stores
        if (!empty($stores) && is_array($stores)) {
            foreach ($stores as $group) {
                // Skip everything that does not belong to the current Website
                if ($group['website'] != MageBridgeModelConfig::load('website')) {
                    continue;
                }

                // Loop through the Store Views
                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        // Determine the Magento request per Store View
                        $storeCode = $child['value'];

                        if (isset($storeUrls[$storeCode])) {
                            $request = $storeUrls[$storeCode];

                        // Use the original request
                        } else {
                            $request = JFactory::getApplication()->input->getString('request');
                        }

                        // Construct the Store View URL
                        $itemId = self::getRootItemIdByLanguage($child['locale']);
                        $url = 'index.php?option=com_magebridge&view=root&lang=' . $child['value'] . '&Itemid=' . $itemId . '&request=' . $request;
                        $url = JRoute::_($url);

                        // Add this entry to the list
                        $languages[] = [
                            'url' => $url,
                            'code' => $child['value'],
                            'label' => $child['label'],];
                    }
                }
            }
        }

        return $languages;
    }

    /**
     * Generate a simple list of store languages
     *
     * @param array	 $stores
     * @param JRegistry $params
     *
     * @return string
     */
    public static function getStoreSelect($stores, $params = null)
    {
        $options = [];
        $currentName = (MageBridgeStoreHelper::getInstance()->getAppType() == 'store') ? MageBridgeStoreHelper::getInstance()->getAppValue() : null;
        $currentValue = null;

        if (!empty($stores) && is_array($stores)) {
            foreach ($stores as $group) {
                if ($group['website'] != MageBridgeModelConfig::load('website')) {
                    continue;
                }

                if (!empty($group['childs'])) {
                    foreach ($group['childs'] as $child) {
                        $url = JUri::current() . '?__store=' . $child['value'];

                        if ($child['value'] == $currentName) {
                            $currentValue = $url;
                        }

                        $options[] = [
                            'value' => $url,
                            'label' => $child['label'],];
                    }
                }
            }
        }

        array_unshift($options, ['value' => '', 'label' => '-- Select --']);

        return JHtml::_('select.genericlist', $options, 'magebridge_store', 'onChange="window.location.href=this.value"', 'value', 'label', $currentValue);
    }

    /**
     * Helper method to get the current store name
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public static function getCurrentStoreName()
    {
        $application = JFactory::getApplication();
        $name = $application->getUserState('magebridge.store.name');

        return $name;
    }

    /**
     * Helper method to get the current store type
     *
     * @access public
     *
     * @param null
     *
     * @return string
     */
    public static function getCurrentStoreType()
    {
        $application = JFactory::getApplication();
        $type = $application->getUserState('magebridge.store.type');

        return $type;
    }
}
