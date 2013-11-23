<?php
/**
 * Joomla! MageBridge - Search plugin
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Import the parent class
jimport( 'joomla.plugin.plugin' );

// Import the MageBridge autoloader
include_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge User Plugin
 */
class plgSearchMageBridge extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $subject
	 * @param array $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

    /**
     * Handle the event when searching for items
     *
     * @access public
     * @param null
     * @return array
     */
    public function onContentSearchAreas()
    {
        // Do not continue if not enabled
        if ($this->isEnabled() == false) {
            return false;
        }

        static $areas = array(
            'mage-products' => 'PLG_SEARCH_MAGEBRIDGE_PRODUCTS',
            'mage-categories' => 'PLG_SEARCH_MAGEBRIDGE_CATEGORIES',
        );
        return $areas;
    }

    /**
     * Handle the event when searching for items
     *
     * @access public
     * @param string $text
     * @param string $phrase
     * @param string $ordering
     * @param array $areas
     * @return array
     */
    public function onContentSearch($text, $phrase, $ordering = '', $areas = null)
    {
        // Do not continue if not enabled
        if ($this->isEnabled() == false) {
            return array();
        }

        // Check if the areas match
        if (!empty($areas) && is_array($areas)) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return array();
            }
        }

        // Do not continue with an empty search string
        if (empty($text)) {
            return array();
        }

        // Load the plugin parameters
        $params = $this->getParams();
        $search_limit = $params->get('search_limit', 50);

        // Build the search array
        $search_options = array(
            'store' => MageBridgeConnectorStore::getInstance()->getStore(),
            'website' => MagebridgeModelConfig::load('website'),
            'text' => $text,
            'search_limit' => $search_limit,
        );

        // Include the MageBridge register
        MageBridgeModelDebug::getInstance()->trace( 'Search plugin' );
        $register = MageBridgeModelRegister::getInstance();
        $segment_id = $register->add('api', 'magebridge_product.search', $search_options);

        // Include the MageBridge bridge
        $bridge = MageBridgeModelBridge::getInstance();
        $bridge->build(true);

        // @todo: Include created/metadesc/metakey in results
        $results = $register->getDataById($segment_id);

        // Do not continue if the result is empty
        if (empty($results)) {
            return array();
        }

        // Only show the maximum amount
        $results = array_slice( $results, 0, $search_limit );
        $objects = array();
        foreach ($results as $index => $result) {
            $object = (object)null;
            $object->title = $result['name'];
            $object->text = $result['description'];
            $url = preg_replace('/^(.*)index.php/', 'index.php', $result['url']);
            $object->href = $url;
            $object->created = null;
            $object->metadesc = null;
            $object->metakey = null;
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
     * Load the parameters
     *
     * @access private
     * @param null
     * @return JParameter
     */
    private function getParams()
    {
        if (!MageBridgeHelper::isJoomla15()) {
            return $this->params;
        } else {
            jimport('joomla.html.parameter');
            $plugin = JPluginHelper::getPlugin('search', 'magebridge');
            $params = new JParameter($plugin->params);
            return $params;
        }
    }

    /**
     * Joomla! 1.5 alias
     *
     * @access public
     * @param null
     * @return array
     */
    public function onSearchAreas()
    {
        return $this->onContentSearchAreas();
    }

    /**
     * Joomla! 1.5 alias
     *
     * @access public
     * @param string $text
     * @param string $phrase
     * @param string $ordering
     * @param array $areas
     * @return array
     */
    public function onSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        return $this->onContentSearch($text, $phrase, $ordering, $areas);
    }

    /**
     * Return whether MageBridge is available or not
     * 
     * @access private
     * @param null
     * @return mixed $value
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
