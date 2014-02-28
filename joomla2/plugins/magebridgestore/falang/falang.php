<?php
/**
 * MageBridge Store plugin - Falang
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Store Plugin to dynamically load a Magento store-scope based on a Joomla! falang
 *
 * @package MageBridge
 */
class plgMageBridgeStoreFalang extends MageBridgePluginStore
{
    /*
     * Deprecated variable to migrate from the original connector-architecture to new Store Plugins
     */
    protected $connector_field = 'falang_language';

    /**
     * Event "onMageBridgeValidate"
     * 
     * @access public
     * @param array $actions
     * @return bool
     */
    public function onMageBridgeValidate($actions = null)
    {
        // Make sure this plugin is enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Make sure to check upon the $actions array to see if it contains what we need
        if(empty($actions['falang_language'])) {
            return false;
        }

        // Fetch the current language
        $language = JFactory::getLanguage();

        // Fetch the languages
        $languages = FalangManager::getInstance()->getActiveLanguages();
        if (!empty($languages)) {
            foreach ($languages as $l) {
                if ($language->getTag() == $l->code || $language->getTag() == $l->lang_code) {
                    if (!empty($l->shortcode)) {
                        $language_code = $l->shortcode;
                        break;
                    } else if (!empty($l->sef)) {
                        $language_code = $l->sef;
                        break;
                    }
                }
            }
        } else {
            $language_code = JRequest::getCmd('lang');
        }

        // Check if the condition applies
        if ($actions['falang_language'] == $language_code) {
            return true;
        }

        // Return false by default
        return false;
    }

    /*
     * Method to check whether this plugin is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if (is_dir(JPATH_SITE.'/components/com_falang')) {
            return true;
        } else {
            return false;
        }
    }
}

