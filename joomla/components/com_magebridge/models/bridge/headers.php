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
 * Main bridge class
 */
class MageBridgeModelBridgeHeaders extends MageBridgeModelBridgeSegment
{
    /**
     * Boolean to see if ProtoType has been loaded
     *
     * @var $_has_prototype
     */
    private $has_prototype = false;

    /**
     * List of scripts
     *
     * @var $_scripts
     */
    private $scripts = null;

    /**
     * List of stylesheets
     *
     * @var $_stylesheets
     */
    private $stylesheets = null;

    /**
     * Singleton method
     *
     * @param string $name
     *
     * @return object
     */
    public static function getInstance($name = null)
    {
        return parent::getInstance('MageBridgeModelBridgeHeaders');
    }

    /**
     * Load the data from the bridge
     *
     * @return array
     */
    public function getResponseData()
    {
        return $this->register->getData('headers');
    }

    /**
     * Get the Base JavaScript URL from the bridge
     *
     * @return string
     */
    public function getBaseJsUrl()
    {
        $url = $this->bridge->getSessionData('base_js_url');
        $uri = JUri::getInstance();

        if (empty($url)) {
            $url = $this->bridge->getMagentoUrl() . 'js/';
        }

        if ($uri->isSSL()) {
            return preg_replace('/^(http|https)\:\/\//', 'https://', $url);
        }

        return preg_replace('/^(http|https)\:\/\//', 'http://', $url);
    }

    /**
     * Determine whether headers can be set
     *
     * @return bool
     */
    protected function allowSetHeaders()
    {
        // Dot not load if this is not the right document-class
        if ($this->doc->getType() != 'html') {
            return false;
        }

        // Check whether the bridge is offline
        $offline = $this->bridge->isOffline();

        if ($offline == true) {
            return false;
        }

        return true;
    }

    /**
     * Method to set the headers
     *
     * @param string $type
     *
     * @return bool
     */
    public function setHeaders($type = 'all')
    {
        // Make sure the bridge is built
        $this->bridge->build();

        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        static $set = [];

        if (in_array($type, $set)) {
            return false;
        }

        $headers = $this->getResponseData();

        switch ($type) {
            case 'css':
                $set[] = 'css';
                $this->loadCss($headers);
                break;

            case 'js':
                $set[] = 'js';
                $this->loadJs($headers);
                break;

            case 'rss':
                $set[] = 'js';
                $this->loadRss($headers);
                break;

            default:
                $set[] = 'all';
                $set[] = 'css';
                $set[] = 'js';
                $this->loadCommon($headers);
                $this->loadCss($headers);
                $this->loadJs($headers);
                $this->loadRss($headers);
                break;
        }

        return true;
    }

    /**
     * Method to load the common headers
     *
     * @param array $headers
     *
     * @return bool
     */
    public function loadCommon($headers)
    {
        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        // Add common META-tags
        if (!empty($headers['title'])) {
            $this->doc->setTitle($headers['title']);
        }

        if (!empty($headers['keywords'])) {
            $this->doc->setMetaData('keywords', $headers['keywords']);
        }

        if (!empty($headers['description'])) {
            $this->setMetaDescription($headers['description']);
        }

        if (!empty($headers['robots'])) {
            $this->setMetaRobots($headers['robots']);
        }

        // Add canonical tag
        if (MageBridgeModelConfig::load('enable_canonical') == 1 && !empty($headers['items'])) {
            $this->setCanonicalLinks($headers['items']);
        }

        return true;
    }

    /**
     * Set canonical links in the header
     *
     * @param array $items
     */
    protected function setCanonicalLinks($items)
    {
        foreach ($items as $item) {
            if ($item['type'] == 'link_rel' && !empty($item['name']) && stripos($item['params'], 'rel="canonical"') !== false) {
                $this->doc->addHeadLink($item['name'], 'canonical');
            }
        }
    }

    /**
     * Method to set the META robots
     *
     * @param string $robots
     */
    protected function setMetaRobots($robots)
    {
        $this->doc->setMetaData('robots', htmlspecialchars($robots));

        if (headers_sent() == false) {
            header('X-Robots-Tag: ' . $robots, true);
        }
    }

    /**
     * Method to set the META description
     *
     * @param string $description
     */
    protected function setMetaDescription($description)
    {
        $description = str_replace('&nbsp;', ' ', $description);
        $description = strip_tags($description);
        $description = htmlspecialchars($description);
        $this->doc->setMetaData('description', $description);
    }

    /**
     * Try to load the merged CSS from Magento
     */
    protected function loadMergeCss()
    {
        if (MageBridgeModelConfig::load('merge_css') == 0) {
            return false;
        }

        if (empty($headers['merge_css'])) {
            return false;
        }

        $this->stylesheets[] = $headers['merge_css'];
        $this->doc->addStyleSheet($headers['merge_css']);

        return true;
    }

    /**
     * Method to load the CSS headers
     *
     * @param array $headers
     *
     * @return null
     */
    public function loadCss($headers)
    {
        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        // Initialize the internal array
        $this->stylesheets = [];

        // Load the CSS from the MageBridge component
        $this->loadDefaultCss();

        // Fetch the value of "disable_css_all"
        // * 0 = Do not disable any Magento CSS
        // * 1 = Disable all Magento CSS (so we just skip this step
        // * 2 = Disable only the CSS listed under "disable_css_custom"
        // * 3 = Disable all CSS except for the CSS listed under "disable_css_custom"
        $disable_css_all = MageBridgeModelConfig::load('disable_css_all');

        if ($disable_css_all == 1) {
            return false;
        }

        if ($this->loadMergeCss()) {
            return false;
        }

        if (empty($headers['items'])) {
            return false;
        }

        foreach ($headers['items'] as $item) {
            if ($item['type'] == 'skin_css' || $item['type'] == 'css') {
                if ($disable_css_all != 0 && MageBridgeHelper::cssIsDisabled($item['name']) == true) {
                    continue;
                }

                $this->stylesheets[] = $item['name'];

                $css = '';

                if (!empty($item['if']) && strpos($item['if'], "><!-->") !== false) {
                    $css .= $item['if'] . "\n";
                } elseif (!empty($item['if'])) {
                    $css .= '<!--[if ' . $item['if'] . ' ]>' . "\n";
                }

                $css .= '<link rel="stylesheet" href="' . $item['path'] . '" type="text/css" ' . $item['params'] . '/>' . "\n";

                if (!empty($item['if']) && strpos($item['if'], "><!-->") !== false) {
                    $css .= '<!--<![endif]-->' . "\n";
                } elseif (!empty($item['if'])) {
                    $css .= '<![endif]-->' . "\n";
                }

                $this->doc->addCustomTag($css);

                continue;
            }
        }

        return true;
    }

    /**
     * Method to load the CSS headers
     *
     * @return bool
     */
    public function loadDefaultCss()
    {
        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        // Determine whether to load the default CSS or not
        if (MageBridgeModelConfig::load('disable_default_css') !== 0) {
            return true;
        }

        // Load common stylesheets
        MageBridgeTemplateHelper::load('css', 'default.css');
        MageBridgeTemplateHelper::load('css', 'custom.css');

        // Load specific stylesheets per page
        if (MageBridgeTemplateHelper::isHomePage()) {
            MageBridgeTemplateHelper::load('css', 'homepage.css');
        }

        if (MageBridgeTemplateHelper::isProductPage()) {
            MageBridgeTemplateHelper::load('css', 'product.css');
        }

        if (MageBridgeTemplateHelper::isCategoryPage()) {
            MageBridgeTemplateHelper::load('css', 'category.css');
        }

        // Determine browser-specific stylesheets
        jimport('joomla.environment.browser');
        $browser = JBrowser::getInstance();

        if ($browser->getBrowser() == 'msie') {
            MageBridgeTemplateHelper::load('css', 'default-ie.css');
        }

        if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '6.0') {
            MageBridgeTemplateHelper::load('css', 'default-ie6.css');
        }

        if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '7.0') {
            MageBridgeTemplateHelper::load('css', 'default-ie7.css');
        }

        if ($browser->getBrowser() == 'msie' && $browser->getVersion() == '8.0') {
            MageBridgeTemplateHelper::load('css', 'default-ie8.css');
        }

        return true;
    }

    /**
     * Method to load the JavaScript headers
     *
     * @param array $headers
     *
     * @return bool
     */
    public function loadJs($headers)
    {
        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        // Check whether all scripts are disabled
        $disable_js = MageBridgeModelConfig::load('disable_js_mage');
        if (strtolower($disable_js) == 'all') {
            return false;
        }

        // Initialize the internal array
        $this->scripts = [];

        // Get system variables
        $bridge = MageBridge::getBridge();

        $html = "<script type=\"text/javascript\">\n" . "//<![CDATA[\n" . "var BLANK_URL = '" . $this->getBaseJsUrl() . "blank.html';\n" . "var BLANK_IMG = '" . $this->getBaseJsUrl() . "spacer.gif';\n" . "//]]>\n" . "</script>\n";
        $this->doc->addCustomTag($html);

        // Load Prototype
        if ($this->loadPrototype() == true) {
            $this->has_prototype = true;
        }

        // Loop through all the header-items fetched from Magento
        if (!empty($headers['items'])) {
            $jslist = [];
            $jstags = [];

            foreach ($headers['items'] as $item) {
                if ($item['type'] == 'skin_js' || $item['type'] == 'js') {
                    if (MageBridgeHelper::jsIsDisabled($item['name']) == true) {
                        continue;
                    }

                    $this->stylesheets[] = $item['name'];
                    $this->scripts[]     = $item['name'];

                    if (empty($item['name'])) {
                        continue;
                    }

                    // If this is a skin-script, construct the tag but add it later to the HTML-header
                    if ($item['type'] == 'skin_js') {
                        if (!preg_match('/^http/', $item['path'])) {
                            $item['path'] = $bridge->getMagentoUrl() . $item['path'];
                        }
                        $tag      = '<script type="text/javascript" src="' . $item['path'] . '"></script>' . "\n";
                        $jstags[] = $tag;
                        continue;
                    }

                    // If this is a conditional script, construct the tag but add it later to the HTML-header
                    if (!empty($item['if'])) {
                        if (!preg_match('/^http/', $item['path'])) {
                            $item['path'] = $bridge->getMagentoUrl() . $item['path'];
                        }

                        $tag = '<script type="text/javascript" src="' . $item['path'] . '"></script>' . "\n";
                        $tag = '<!--[if ' . $item['if'] . ' ]>' . "\n" . $tag . '<![endif]-->' . "\n";

                        $jstags[] = $tag;
                        continue;
                    }

                    // Detect Prototype
                    if (strstr($item['path'], 'prototype') || strstr($item['path'], 'scriptaculous')) {
                        $this->has_prototype = true;

                        // Load an optimized Prototype/script.acul.us version
                        if (MageBridgeModelConfig::load('use_protoaculous') == 1 || MageBridgeModelConfig::load('use_protoculous') == 1) {
                            $skip_scripts = [
                                'prototype/prototype.js',
                                'scriptaculous/builder.js',
                                'scriptaculous/effects.js',
                                'scriptaculous/dragdrop.js',
                                'scriptaculous/controls.js',
                                'scriptaculous/slider.js',
                            ];

                            if (in_array($item['name'], $skip_scripts)) {
                                continue;
                            }
                        }

                        // Skip these, if the Google API is already loaded
                        if (MageBridgeModelConfig::load('use_google_api') == 1) {
                            if (preg_match('/prototype.js$/', $item['name'])) {
                                continue;
                            }
                            if (preg_match('/scriptaculous.js$/', $item['name'])) {
                                continue;
                            }
                        }
                    }

                    // Detect jQuery and replace it
                    if (preg_match('/jquery-([0-9]+)\.([0-9]+)\.([0-9]+)/', $item['path']) || preg_match('/jquery.js$/', $item['path']) || preg_match('/jquery.min.js$/', $item['path'])) {
                        if (MageBridgeModelConfig::load('replace_jquery') == 1) {
                            MageBridgeTemplateHelper::load('jquery');
                            continue;
                        }
                    }

                    // Detect the translation script
                    if (strstr($item['name'], 'translate.js')) {
                        $translate = true;
                    }

                    // Load this script through JS merging or not
                    if (MageBridgeModelConfig::load('merge_js') == 1) {
                        $jslist[] = $item['name'];
                    } else {
                        if (MageBridgeModelConfig::load('merge_js') !== 2 || empty($headers['merge_js'])) {
                            if (!preg_match('/^http/', $item['path'])) {
                                $item['path'] = $bridge->getMagentoUrl() . $item['path'];
                            }

                            $item['path'] = $this->convertUrl($item['path']);
                            $tag          = '<script type="text/javascript" src="' . $item['path'] . '"></script>' . "\n";
                            $jstags[]     = $tag;
                        }
                    }
                }
            }

            if (MageBridgeModelConfig::load('merge_js') == 2 && !empty($headers['merge_js'])) {
                $this->addScript($headers['merge_js']);
            } else {
                if (!empty($jslist)) {
                    $this->addScript($this->getBaseJsUrl() . 'index.php?c=auto&amp;f=,' . implode(',', $jslist));
                }
            }

            if (!empty($jstags)) {
                foreach ($jstags as $tag) {
                    if (!empty($tag)) {
                        $this->doc->addCustomTag($tag);
                    }
                }
            }
        }

        // Load some extra JavaScript tags
        if (isset($headers['custom'])) {
            foreach ($headers['custom'] as $custom) {
                $custom = MageBridgeEncryptionHelper::base64_decode($custom);
                $custom = preg_replace('/Mage.Cookies.domain([^;]+)\;/m', 'Mage.Cookies.domain = null;', $custom);
                $this->doc->addCustomTag($custom);
            }

            return true;
        }

        if (isset($translate) && $translate == true) {
            $html = '<script type="text/javascript">var Translator = new Translate([]);</script>';
            $this->doc->addCustomTag($html);
        }

        return true;
    }

    /**
     * Method to load feeds
     *
     * @param array $headers
     *
     * @return bool
     */
    public function loadRss($headers)
    {
        // Determine whether the headers can be set
        if ($this->allowSetHeaders() == false) {
            return false;
        }

        // Loop through the items
        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                // Fetch RSS-items
                if ($item['type'] == 'rss') {
                    $url = $item['name'];
                    $url = preg_replace('/(.*)index.php?option=com_magebridge/', '', $url);
                    $url = MageBridgeHelper::filterUrl($url);
                    $this->doc->addHeadLink($url, 'alternate', 'rel', [
                        'type'  => 'application/rss+xml',
                        'title' => 'RSS 2.0',
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Return the list of scripts
     *
     * @param null
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Add script
     *
     * @param string
     */
    private function addScript($url)
    {
        $url  = $this->convertUrl($url);
        $html = '<script type="text/javascript" src="' . $url . '"></script>';
        $this->doc->addCustomTag($html);
    }

    /**
     * Return the list of stylesheets
     *
     * @param null
     *
     * @return array
     */
    public function getStylesheets()
    {
        if ($this->stylesheets == null) {
            $this->setHeaders('css');
        }

        return $this->stylesheets;
    }

    /**
     * Return whether ProtoType has been loaded or not
     *
     * @param null
     *
     * @return bool
     */
    public function hasProtoType()
    {
        return $this->has_prototype;
    }

    /**
     * Method to load ProtoType
     */
    public function loadPrototype()
    {
        // Load Prototype through Google API
        if (MageBridgeModelConfig::load('use_google_api') == 1) {
            $this->addScript('https://ajax.googleapis.com/ajax/libs/prototype/1.7.3.0/prototype.js');
            $this->addScript('https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js');

            return true;

        // Load Protoaculous
        } else {
            if (MageBridgeModelConfig::load('use_protoaculous') == 1) {
                $this->addScript('media/com_magebridge/js/protoaculous.1.9.0-1.7.3.0.min.js');

                return true;

            // Load Protoculous
            } else {
                if (MageBridgeModelConfig::load('use_protoculous') == 1) {
                    $this->addScript('media/com_magebridge/js/protoculous-1.0.2-packed.js');

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Guarantee a script or stylesheet loaded through SSL is also loaded through SSL
     *
     * @param null
     *
     * @return bool
     */
    public function convertUrl($url)
    {
        $uri = JUri::getInstance();

        if ($uri->isSSL()) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }
}
