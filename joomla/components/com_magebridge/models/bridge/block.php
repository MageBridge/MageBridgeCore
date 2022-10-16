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
class MageBridgeModelBridgeBlock extends MageBridgeModelBridgeSegment
{
    /**
     * Singleton
     *
     * @param string $name
     *
     * @return object
     */
    public static function getInstance($name = null)
    {
        return parent::getInstance('MageBridgeModelBridgeBlock');
    }

    /**
     * Load the data from the bridge
     *
     * @param string $name
     * @param mixed  $arguments
     *
     * @return mixed
     */
    public function getResponseData($name, $arguments = null)
    {
        return MageBridgeModelRegister::getInstance()
            ->getData('block', $name, $arguments);
    }

    /**
     * Check wheather this block is cachable
     *
     * @param string $name
     *
     * @return bool
     */
    public function isCachable($name)
    {
        $response = parent::getResponse('block', $name);

        if (isset($response['meta']['allow_caching']) && $response['meta']['allow_caching'] == 1 && isset($response['meta']['cache_lifetime']) && $response['meta']['cache_lifetime'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * Method to return a specific block
     *
     * @param string $block_name
     * @param mixed  $arguments
     *
     * @return string
     */
    public function getBlock($block_name, $arguments = null)
    {
        // Make sure the bridge is built
        MageBridgeModelBridge::getInstance()
            ->build();

        // Get the response-data
        $segment = $this->getResponse('block', $block_name, $arguments);

        if (!isset($segment['data'])) {
            return null;
        }

        // Parse the response-data
        $block_data = $segment['data'];
        if (!empty($block_data)) {
            if (!isset($segment['cache'])) {
                $block_data = self::decode($block_data);
                $block_data = self::filterHtml($block_data);
            }
        }

        // Parse blocks
        $block_data = MageBridgeBlockHelper::parseBlock($block_data);

        // Replace Joomla! jdoc:include tags
        if (MageBridgeModelConfig::load('enable_jdoc_tags') == 1) {
            $block_data = MageBridgeBlockHelper::parseJdocTags($block_data);
        }

        // Run Content Plugins on this block-html
        if (MageBridgeModelConfig::load('enable_content_plugins') == 1) {
            // Prepare a simple item (like an article) for use with Content Plugins
            $item = (object) null;
            $item->text = $block_data;

            // Get a list of all Content Plugins except MageBridge plugins
            $plugins = MageBridgeModelBridgeBlock::getContentPlugins();

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    JPluginHelper::importPlugin('content', $plugin);
                }
            }

            // Once the plugins are imported, trigger the content-event
            $dispatcher = JEventDispatcher::getInstance();

            $item->params = YireoHelper::toRegistry();
            $result = $dispatcher->trigger('onContentPrepare', [
                'com_magebridge.block',
                &$item,
                &$item->params,
                0, ]);

            // Move the modified contents into $block_data
            $block_data = $item->text;
            unset($item);
        }

        // Filter the block throw the "magebridge" plugin group
        if (MageBridgeModelConfig::load('enable_block_rendering') == 1) {
            JPluginHelper::importPlugin('magebridge');
            JFactory::getApplication()
                ->triggerEvent('onBeforeDisplayBlock', [&$block_name, $arguments, &$block_data]);
        }

        return $block_data;
    }

    /**
     * Method to decode the block-output
     *
     * @param string $block_data
     *
     * @return string
     */
    public function decode($block_data)
    {
        $block_data = MageBridgeEncryptionHelper::base64_decode($block_data);

        return $block_data;
    }

    /**
     * Method to filter the HTML with the MageBridge URL filter but also generic Content Filters
     *
     * @param string $html
     *
     * @return string
     */
    public function filterHtml($html)
    {
        // Fix everything regarding URLs
        $html = MageBridgeHelper::filterContent($html);

        // Replace URLs where necessary
        $replacement_urls = MageBridgeUrlHelper::getReplacementUrls();

        if (!empty($replacement_urls)) {
            foreach ($replacement_urls as $replacement_url) {
                $source = $replacement_url->source;
                $destination = $replacement_url->destination;

                // Prepare the source URL
                if ($replacement_url->source_type == 0) {
                    $source = MageBridgeUrlHelper::route($source);
                } else {
                    $source = str_replace('/', '\/', $source);
                }

                // Prepare the destination URL
                if (preg_match('/^index\.php\?option=/', $destination)) {
                    $destination = JRoute::_($destination);
                }

                // Replace the actual URLs
                if ($replacement_url->source_type == 0) {
                    $html = str_replace($source . '\'', $destination . '\'', $html);
                    $html = str_replace($source . '"', $destination . '"', $html);
                } else {
                    $html = preg_replace('/href=\"([^\"]+)' . $source . '([^\"]+)/', 'href="' . $destination, $html);
                }
            }
        }

        return $html;
    }

    /**
     * Method to get a list of Content Plugins except the MageBridge Content Plugins
     *
     * @param null
     *
     * @return array
     */
    private function getContentPlugins()
    {
        static $plugins = null;

        if (!empty($plugins)) {
            return $plugins;
        }

        // Get system variables
        $db = JFactory::getDbo();

        $query = 'SELECT `element` FROM `#__extensions`' . ' WHERE `type` = "plugin" AND `enabled` = 1 AND `element` NOT LIKE "magebridge%" AND `element` != "emailcloak"' . ' ORDER BY `ordering`';

        $db->setQuery($query);
        $plugins = $db->loadColumn();

        if ($plugins == false) {
            return false;
        }

        return $plugins;
    }
}
