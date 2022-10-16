<?php
/**
 * Joomla! MageBridge - Search plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge User Plugin
 */
class PlgSearchMageBridge extends JPlugin
{
    /**
     * Constructor
     *
     * @param object $subject
     * @param array  $config
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();
    }

    /**
     * Handle the event when searching for items
     *
     * @return array
     */
    public function onContentSearchAreas()
    {
        // Do not continue if not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        static $areas = [
            'mage-products' => 'PLG_SEARCH_MAGEBRIDGE_PRODUCTS',
            'mage-categories' => 'PLG_SEARCH_MAGEBRIDGE_CATEGORIES',];

        return $areas;
    }

    /**
     * Handle the event when searching for items
     *
     * @param string $text
     * @param string $phrase
     * @param string $ordering
     * @param array  $areas
     *
     * @return array
     */
    public function onContentSearch($text, $phrase, $ordering = '', $areas = null)
    {
        // Do not continue if not enabled
        if ($this->isEnabled() == false) {
            return [];
        }

        // Check if the areas match
        if (!empty($areas) && is_array($areas)) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return [];
            }
        }

        // Do not continue with an empty search string
        if (empty($text)) {
            return [];
        }

        // Load the plugin parameters
        $search_limit = (int) $this->params->get('search_limit', 50);
        $search_fields = trim($this->params->get('search_fields'));

        // Determine the search fields
        if (!empty($search_fields)) {
            $search_field_values = explode(',', $search_fields);

            $search_fields = [];
            //$search_fields = array('title', 'description');

            foreach ($search_field_values as $search_field_value) {
                $search_fields[] = trim($search_field_value);
            }

            array_unique($search_fields);
        } else {
            $search_fields = ['title', 'description'];
        }

        // Build the search array
        $search_options = [
            'store' => MageBridgeConnectorStore::getInstance()->getStore(),
            'website' => MageBridgeModelConfig::load('website'),
            'text' => $text,
            'search_limit' => $search_limit,
            'search_fields' => $search_fields,];

        // Include the MageBridge register
        MageBridgeModelDebug::getInstance()->trace('Search plugin');
        $register = MageBridgeModelRegister::getInstance();
        $segment_id = $register->add('api', 'magebridge_product.search', $search_options);

        // Include the MageBridge bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build(true);

        // Get the results
        $results = $register->getDataById($segment_id);

        // Do not continue if the result is empty
        if (empty($results)) {
            return [];
        }

        // Only show the maximum amount
        $results = array_slice($results, 0, $search_limit);
        $objects = [];

        foreach ($results as $index => $result) {
            $object = (object) null;
            $object->title = $result['name'];
            $object->text = $result['description'];
            $url = preg_replace('/^(.*)index.php/', 'index.php', $result['url']);
            $object->href = $url;
            $object->created = $result['created_at'];
            $object->metadesc = $result['meta_description'];
            $object->metakey = $result['meta_keyword'];
            $object->section = null;
            $object->browsernav = 2;
            $object->thumbnail = $result['thumbnail'];
            $object->small_image = $result['small_image'];
            $object->image = $result['image'];
            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Return whether MageBridge is available or not
     *
     * @return boolean
     */
    private function isEnabled()
    {
        if (class_exists('MageBridgeModelBridge')) {
            if (MageBridgeModelBridge::getInstance()->isOffline() == false) {
                return true;
            }
        }

        return false;
    }
}
