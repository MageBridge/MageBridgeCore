<?php
/**
 * Joomla! MageBridge - YOOtheme System plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge System Plugin
 */
class plgSystemMageBridgeYoo extends JPlugin
{
    /**
     * Event onAfterDispatch
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterDispatch()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        // Load variables
        $application = JFactory::getApplication();

        // Don't do anything in other applications than the frontend
        if ($application->isSite() == false) {
            return false;
        }

        // Load the whitelist settings
        $whitelist = JFactory::getConfig()->get('magebridge.script.whitelist');
        if (empty($whitelist)) {
            $whitelist = [];
        }
        if ($this->getParams()->get('enable_js_widgetkit', 1) == 1) {
            $whitelist[] = '/widgetkit/';
        }
        if ($this->getParams()->get('enable_js_warp', 1) == 1) {
            $whitelist[] = '/warp/';
        }
        if ($this->getParams()->get('enable_js_template', 1) == 1) {
            $whitelist[] = '/js/';
        }
        JFactory::getConfig()->set('magebridge.script.whitelist', $whitelist);

        // Read the template-related files
        $ini = JPATH_THEMES.'/'.$application->getTemplate().'/params.ini';
        $conf = JPATH_THEMES.'/'.$application->getTemplate().'/config';
        if (!file_exists($conf)) {
            $conf = JPATH_THEMES.'/'.$application->getTemplate().'/config.json';
        }
        $xml = JPATH_THEMES.'/'.$application->getTemplate().'/templateDetails.xml';
        $ini_content = @file_get_contents($ini);
        $conf_content = @file_get_contents($conf);

        // WARP-usage of "config" file
        if (!empty($conf_content)) {
            // Unjson the data-array
            $data = json_decode($conf_content, true);
            if (is_array($data)) {
                // Fetch the Itemid
                $Itemid = JFactory::getApplication()->input->getInt('Itemid');

                // Define the current profile-indications
                $profileDefault = (isset($data['profile_default'])) ? $data['profile_default'] : null;

                // Load the profile-specific CSS, set in GET
                $profileGet = JFactory::getApplication()->input->getCmd('profile');
                if (!empty($profileGet)) {
                    $profile = $profileGet;
                    MageBridgeTemplateHelper::load('css', 'profile-'.$profile.'.css');

                // Load the profile-specific CSS, set through the Itemid-mapping
                } elseif (isset($data['profile_map'][$Itemid])) {
                    $profileMapped = $data['profile_map'][$Itemid];
                    if (!empty($profileMapped)) {
                        $profile = $profileMapped;
                        MageBridgeTemplateHelper::load('css', 'profile-'.$profile.'.css');
                    }

                // Load the default profile-CSS
                } elseif (!empty($profileDefault)) {
                    $profile = $profileDefault;
                    MageBridgeTemplateHelper::load('css', 'profile-'.$profile.'.css');
                }

                // Load a profile-specific color-definition
                if (!empty($profile) && isset($data['profile_data'][$profile]['color'])) {
                    $color = $data['profile_data'][$profile]['color'];
                } elseif (isset($data['profile_data']['default']['color'])) {
                    $color = $data['profile_data']['default']['color'];
                }

                // If a color-definition is detected, load the CSS
                if (!empty($color)) {
                    MageBridgeTemplateHelper::load('css', 'color-'.$color.'.css');
                }

                // Load a profile-specific style-definition
                if (!empty($profile) && isset($data['profile_data'][$profile]['style'])) {
                    $style = $data['profile_data'][$profile]['style'];
                } elseif (isset($data['profile_data']['default']['style'])) {
                    $style = $data['profile_data']['default']['style'];
                }

                // If a style-definition is detected, load the CSS
                if (!empty($style)) {
                    if ($style == 'default') {
                        $style = $profileDefault;
                    }
                    MageBridgeTemplateHelper::load('css', 'style-'.$style.'.css');
                }

                // Load a layout-specific style-definition
                if (!empty($profile) && isset($data['layouts'][$profile]['style'])) {
                    $layout = $data['layouts'][$profile]['style'];
                } elseif (isset($data['layouts']['default']['style'])) {
                    $layout = $data['layouts']['default']['style'];
                }

                // If a style-definition is detected, load the CSS
                if (!empty($layout)) {
                    if ($layout == 'default') {
                        $layout = $profileDefault;
                    }
                    MageBridgeTemplateHelper::load('css', 'style-'.$layout.'.css');
                }
            }

        // Pre-WARP reading of Joomla! parameters
        } else {
            // Create the parameters object
            $params = YireoHelper::toRegistry($ini_content, $xml);

            // Load a specific stylesheet per color
            $color = $params->get('color');
            if (!empty($color)) {
                MageBridgeTemplateHelper::load('css', 'color-'.$color.'.css');
            }

            // Load a specific stylesheet per style
            $style = $params->get('style');
            if (!empty($style)) {
                MageBridgeTemplateHelper::load('css', 'style-'.$style.'.css');
            }
        }
    }

    /**
     * Event onAfterRender
     *
     * @access public
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        // Don't do anything if MageBridge is not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        $disable_js_mootools = MageBridgeModelConfig::load('disable_js_mootools');
        if (MageBridgeTemplateHelper::hasPrototypeJs() && $disable_js_mootools == 1) {
            $body = JResponse::getBody();
            $body = preg_replace('/Warp.Settings(.*);/', '', $body);
            JResponse::setBody($body);
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
     * Simple check to see if MageBridge exists
     *
     * @access private
     * @param null
     * @return bool
     */
    private function isEnabled()
    {
        $template = JFactory::getApplication()->getTemplate();
        if (preg_match('/^yoo_/', $template) == false) {
            return false;
        }

        if (is_file(JPATH_SITE.'/components/com_magebridge/models/config.php')) {
            return true;
        }
        return false;
    }
}
