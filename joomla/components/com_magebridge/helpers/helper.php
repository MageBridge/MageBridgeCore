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
 * General helper for usage in Joomla!
 */
class MageBridgeHelper
{
    /**
     * Helper-method to get help-URLs for usage in the content
     *
     * @param string $name
     *
     * @return array
     */
    public static function getHelpItem($name = null)
    {
        $links = [
            'faq' => [
                'title' => 'General FAQ',
                'link' => 'https://www.yireo.com/software/magebridge/experience/faq',
                'internal' => 0,
            ],
            'faq-troubleshooting' => [
                'title' => 'Troubleshooting FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/729-magebridge-faq-troubleshooting',
                'internal' => 0,
            ],
            'faq-troubleshooting:api-widgets' => [
                'title' => 'API Widgets FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/729-magebridge-faq-troubleshooting#api-widgets-do-not-work',
                'internal' => 0,
            ],
            'faq-development' => [
                'title' => 'Development FAQ',
                'link' => 'https://www.yireo.com/tutorials/magebridge/development/577-magebridge-faq-development',
                'internal' => 0,
            ],
            'forum' => [
                'title' => 'MageBridge Support Form',
                'link' => 'https://www.yireo.com/forum/',
                'internal' => 0,
            ],
            'tutorials' => [
                'title' => 'Yireo Tutorials',
                'link' => 'https://www.yireo.com/tutorials',
                'internal' => 0,
            ],
            'quickstart' => [
                'title' => 'MageBridge Quick Start Guide',
                'link' => 'https://www.yireo.com/tutorials/magebridge/basics/540-magebridge-quick-start-guide',
                'internal' => 0,
            ],
            'troubleshooting' => [
                'title' => 'MageBridge Troubleshooting Guide',
                'link' => 'https://www.yireo.com/tutorials/magebridge/troubleshooting/723-magebridge-troubleshooting-guide',
                'internal' => 0,
            ],
            'changelog' => [
                'title' => 'MageBridge Changelog',
                'link' => 'https://www.yireo.com/software/magebridge/downloads/changelog',
                'internal' => 0,
            ],
            'subscriptions' => [
                'title' => 'your Yireo Subscriptions page',
                'link' => 'https://www.yireo.com/shop/membership/customer/products/',
                'internal' => 0,
            ],
            'config' => [
                'title' => 'Global Configuration',
                'link' => 'index.php?option=com_config',
                'internal' => 1,
            ],
        ];

        if (!empty($name) && isset($links[$name])) {
            return $links[$name];
        }

        return null;
    }

    /**
     * Helper-method to display Yireo.com-links
     *
     * @param string $name
     * @param string $title
     *
     * @return string
     */
    public static function getHelpLink($name = null)
    {
        $help = MageBridgeHelper::getHelpItem($name);

        return $help['link'];
    }

    /**
     * Helper-method to display Yireo.com-links
     *
     * @param string $name
     * @param string $title
     *
     * @return string
     */
    public static function getHelpText($name = null, $title = null)
    {
        $help = MageBridgeHelper::getHelpItem($name);
        $target = ($help['internal'] == 0) ? ' target="_new"' : '';
        $title = (!empty($title)) ? $title : JText::_($help['title']);

        return '<a href="' . $help['link'] . '"' . $target . '>' . $title . '</a>';
    }

    /**
     * Helper-method to insert notices into the application
     *
     * @param string $text
     *
     * @return string
     */
    public static function help($text = null)
    {
        if (MageBridgeModelConfig::load('show_help') == 1) {
            if (preg_match('/\{([^\}]+)\}/', $text, $match)) {
                $array = explode(':', $match[1]);
                $text = str_replace($match[0], MageBridgeHelper::getHelpText($array[0], $array[1]), $text);
            }

            $html = '<div class="magebridge-help">';
            $html .= $text;
            $html .= '</div>';

            return $html;
        }

        return '';
    }

    /**
     * Helper-method to filter the original Magento content from unneeded/unwanted bits
     *
     * @param string $content
     *
     * @return string
     */
    public static function filterContent($content)
    {
        // Allow to disable this filtering
        if (MageBridgeModelConfig::load('filter_content') == 0) {
            return $content;
        }

        // Get common variables
        $bridge = MageBridgeModelBridge::getInstance();

        // Convert all remaining Magento links to Joomla! links
        $content = str_replace($bridge->getMagentoUrl() . 'index.php/', $bridge->getJoomlaBridgeUrl(), $content);
        $content = str_replace($bridge->getMagentoUrl() . 'magebridge.php/', $bridge->getJoomlaBridgeUrl(), $content);

        // Implement a very dirty hack because PayPal converts URLs "&" to "and"
        $current = MageBridgeUrlHelper::current();

        if (strstr($current, 'paypal') && strstr($current, 'redirect')) {
            // Try to find the distorted URLs
            $matches = [];
            if (preg_match_all('/([^\"\']+)com_magebridgeand([^\"\']+)/', $content, $matches)) {
                foreach ($matches[0] as $match) {
                    // Replace the wrong "and" words with "&" again
                    $url = str_replace('com_magebridgeand', 'com_magebridge&', $match);
                    $url = str_replace('rootand', 'root&', $url);

                    // Replace the wrong URL with its correction
                    $content = str_replace($match, $url, $content);
                }
            }
        }

        // Replace all uenc-URLs from Magento with URLs parsed through JRoute
        $matches = [];
        $replaced = [];

        if (preg_match_all('/\/uenc\/([a-zA-Z0-9\-\_\,]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Decode the match
                $original_url = MageBridgeEncryptionHelper::base64_decode($match);
                $url = $original_url;
                $url = MageBridgeUrlHelper::stripUrl($url);

                // Convert the non-SEF URL to a SEF URL
                if (preg_match('/^index.php\?option=com_magebridge/', $url)) {
                    // Parse the URL but do NOT turn it into SEF because of Mage_Core_Controller_Varien_Action::_isUrlInternal()
                    $url = MageBridgeHelper::filterUrl(str_replace('/', urldecode('/'), $url), false);
                    $url = $bridge->getJoomlaBridgeSefUrl($url);
                } else {
                    if (!preg_match('/^(http|https)/', $url)) {
                        $url = $bridge->getJoomlaBridgeSefUrl($url);
                    }
                    $url = preg_replace('/\?SID=([a-zA-Z0-9\-\_]{12,42})/', '', $url);
                }

                // Extra check on HTTPS
                if (JUri::getInstance()
                        ->isSSL() == true
                ) {
                    $url = str_replace('http://', 'https://', $url);
                } else {
                    $url = str_replace('https://', 'http://', $url);
                }

                // Replace the URL in the content
                if ($original_url != $url && $original_url . '/' != $url && !in_array($match, $replaced)) {
                    MageBridgeModelDebug::getInstance()
                        ->notice('Translating uenc-URL from ' . $original_url . ' to ' . $url);
                    $base64_url = MageBridgeEncryptionHelper::base64_encode($url);
                    $content = str_replace($match, $base64_url, $content);
                    $replaced[] = $match;
                }
            }
        }

        // Match all URLs and filter them
        $matches = [];

        if (preg_match_all('/index.php\?option=com_magebridge([^\'\"\<]+)([\'\"\<]{1})/', $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                $oldurl = 'index.php?option=com_magebridge' . $matches[1][$i];
                $end = $matches[2][$i];
                $newurl = MageBridgeHelper::filterUrl($oldurl);

                if (!empty($newurl)) {
                    $content = str_replace($oldurl . $end, $newurl . $end, $content);
                }
            }
        }

        // Clean-up left-overs
        $content = str_replace('?___SID=U', '', $content);
        $content = str_replace('?___SID=S', '', $content);
        $content = preg_replace('/\?SID=([a-zA-Z0-9\-\_]{12,42})/', '?', $content);
        $content = str_replace('?&amp;', '?', $content);

        // Remove all __store information
        if (MageBridgeModelConfig::load('filter_store_from_url') == 1) {
            $content = preg_replace('/\?___store=([a-zA-Z0-9]+)/', '', $content);
        }

        // Remove double-slashes
        //$basedir = preg_replace('/^([\/]?)(.*)([\/]?)$/', '\2', JUri::base(true));
        //$content = str_replace(JUri::base().$basedir, JUri::base(), $content);
        $content = str_replace(JUri::base() . '/', JUri::base(), $content);

        // Adjust wrong media-URLs
        if (JUri::getInstance()
                ->isSSL() == true
        ) {
            $non_https = preg_replace('/^https:/', 'http:', $bridge->getMagentoUrl());
            $https = preg_replace('/^http:/', 'https:', $bridge->getMagentoUrl());
            $content = str_replace($non_https, $https, $content);
        }

        // Adjust incorrect URLs with parameters starting with &
        if (preg_match_all('/(\'|\")(http|https):\/\/([^\&\?\'\"]+)\&/', $content, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $content = str_replace($matches[3][$index] . '&', $matches[3][$index] . '?', $content);
            }
        }

        return $content;
    }

    /**
     * Helper-method to merge the original Magento URL into the Joomla! URL
     *
     * @param string $url
     * @param bool   $use_sef
     *
     * @return string
     */
    public static function filterUrl($url, $use_sef = true)
    {
        if (empty($url)) {
            return null;
        }

        // Parse the query-part of the URL
        $q = explode('?', $url);
        array_shift($q);

        // Merge the Magento query with the Joomla! query
        $qs = implode('&', $q);
        $qs = str_replace('&amp;', '&', $qs);
        parse_str($qs, $query);

        // Get rid of the annoying SID
        $sids = ['SID', 'sid', '__SID', '___SID'];

        foreach ($sids as $sid) {
            if (isset($query[$sid])) {
                unset($query[$sid]);
            }
        }

        // Construct the URL again
        $url = 'index.php?';
        $url_segments = [];

        foreach ($query as $name => $value) {
            $url_segments[] = $name . '=' . $value;
        }
        $url = 'index.php?' . implode('&', $url_segments);

        if ($use_sef == true) {
            $url = MageBridgeUrlHelper::getSefUrl($url);
        }

        $prefix = JUri::getInstance()
            ->toString(['scheme', 'host', 'port']);
        $path = str_replace($prefix, '', JUri::base());
        $pos = strpos($url, $path);

        if (!empty($path) && $pos !== false) {
            $url = substr($url, $pos + strlen($path));
        }

        return $url;
    }

    /**
     * Helper-method to parse the comma-seperated setting "disable_css_mage" into an array
     *
     * @param null
     *
     * @return array
     */
    public static function getDisableCss()
    {
        $disable_css = MageBridgeModelConfig::load('disable_css_mage');

        if (empty($disable_css)) {
            return [];
        }

        $disable_css = explode(',', $disable_css);

        if (!empty($disable_css)) {
            foreach ($disable_css as $name => $value) {
                $value = trim($value);
                $disable_css[$value] = $value;
            }
        }

        return $disable_css;
    }

    /**
     * Helper-method to find out if some kind of CSS-file is disabled or not
     *
     * @param string $css
     *
     * @return bool
     */
    public static function cssIsDisabled($css)
    {
        $allow = MageBridgeModelConfig::load('disable_css_all');
        $disable_css = self::getDisableCss();

        if (!empty($disable_css)) {
            foreach ($disable_css as $disable) {
                $disable = str_replace('/', '\/', $disable);

                if (preg_match("/$disable$/", $css)) {
                    return ($allow == 3) ? false : true;
                }
            }
        }

        return ($allow == 3) ? true : false;
    }

    /**
     * Helper-method to parse the comma-seperated setting "disable_js_mage" into an array
     *
     * @param null
     *
     * @return array
     */
    public static function getDisableJs()
    {
        $disable_js = MageBridgeModelConfig::load('disable_js_mage');

        if (empty($disable_js)) {
            return [];
        }

        $disable_js = explode(',', $disable_js);

        if (!empty($disable_js)) {
            foreach ($disable_js as $name => $value) {
                $value = trim($value);
                $disable_js[$value] = $value;
            }
        }

        return $disable_js;
    }

    /**
     * Helper-method to find out if some kind of JS-file is disabled or not
     *
     * @param string $js
     *
     * @return bool
     */
    public static function jsIsDisabled($js)
    {
        $disable_js = self::getDisableJs();

        if (!empty($disable_js)) {
            foreach ($disable_js as $disable) {
                $disable = str_replace('/', '\/', $disable);

                if (preg_match("/$disable$/", $js)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper-method to get the current Joomla! core version
     *
     * @param null
     *
     * @return string
     */
    public static function getJoomlaVersion()
    {
        JLoader::import('joomla.version');
        $version = new JVersion();

        return $version->getShortVersion();
    }

    /**
     * Helper-method to get the current Joomla! core version
     *
     * @param $version string|array
     *
     * @return bool
     */
    public static function isJoomlaVersion($version = null)
    {
        JLoader::import('joomla.version');
        $jversion = new JVersion();

        if (!is_array($version)) {
            $version = [$version];
        }

        foreach ($version as $v) {
            if (version_compare($jversion->RELEASE, $v, 'eq')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper-method to get the current Joomla! core version
     *
     * @param null
     *
     * @return bool
     */
    public static function isJoomla35()
    {
        return self::isJoomlaVersion(['3.0', '3.1', '3.2', '3.3', '3.4', '3.5']);
    }

    /**
     * Helper-method to get the current Joomla! core version
     *
     * @param null
     *
     * @return bool
     */
    public static function isJoomla25()
    {
        return self::isJoomlaVersion(['1.6', '1.7', '2.5']);
    }

    /**
     * Helper-method to get the component parameters
     *
     * @param null
     *
     * @return bool
     */
    public static function getParams()
    {
        $params = JFactory::getApplication()
            ->getMenu('site')
            ->getParams(JFactory::getApplication()->input->getInt('Itemid'));

        return $params;
    }

    /**
     * Helper-method to convert an array to a MySQL string
     *
     * @param null
     *
     * @return bool
     */
    public static function arrayToSQl($array)
    {
        $db = JFactory::getDbo();
        $sql = [];

        foreach ($array as $name => $value) {
            $sql[] = '`' . $name . '`=' . $db->Quote($value);
        }

        return implode(',', $sql);
    }

    /**
     * Helper-method to convert a CSV-string to an array
     *
     * @param null
     *
     * @return bool
     */
    public static function csvToArray($csv)
    {
        if (empty($csv)) {
            return [];
        }

        $tmp = explode(',', $csv);
        $array = [];

        if (!empty($tmp)) {
            foreach ($tmp as $t) {
                $t = trim($t);
                if (!empty($t)) {
                    $array[] = $t;
                }
            }
        }

        return $array;
    }
}
