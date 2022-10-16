<?php
/**
 * Joomla! module MageBridge: Menu
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

class ModMageBridgeMenuHelper extends MageBridgeModuleHelper
{
    /**
     * Method to get the API-arguments based upon the module parameters
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function getArguments($params = null)
    {
        static $arguments = [];
        $id = md5(var_export($params, true));

        if (!isset($arguments[$id])) {
            $arguments[$id] = ['count' => (int) $params->get('count', 0), 'levels' => (int) $params->get('levels', 1), 'startlevel' => (int) $params->get('startlevel', 1),];

            if ($params->get('include_product_count') == 1) {
                $arguments[$id]['include_product_count'] = 1;
            }

            if (empty($arguments[$id])) {
                $arguments[$id] = null;
            }
        }

        return $arguments[$id];
    }

    /**
     * Method to be called once the MageBridge is loaded
     *
     * @access public
     * @param JRegistry $params
     * @return array
     */
    public static function register($params = null)
    {
        $arguments = ModMageBridgeMenuHelper::getArguments($params);

        return [['api', 'magebridge_category.tree', $arguments],];
    }

    /**
     * Fetch the content from the bridge
     *
     * @access public
     * @param JRegistry $params
     * @return mixed
     */
    public static function build($params = null)
    {
        $arguments = ModMageBridgeMenuHelper::getArguments($params);

        return parent::getCall('getAPI', 'magebridge_category.tree', $arguments);
    }

    /**
     * Helper-method to return a specified root-category from a tree
     *
     * @access public
     * @param array $tree
     * @param int $root_id
     * @return array
     */
    public static function setRoot($tree = null, $root_id = null)
    {
        // If no root-category is configured, just return all children
        if (!$root_id > 0) {
            return $tree['children'];
        }

        // If the current level contains the configured root-category, return it's children
        if (isset($tree['category_id']) && $tree['category_id'] == $root_id) {
            return $tree['children'];
        }

        // Loop through the children to find the configured root-category
        if (isset($tree['children']) && is_array($tree['children']) && count($tree['children']) > 0) {
            foreach ($tree['children'] as $item) {
                $subtree = ModMageBridgeMenuHelper::setRoot($item, $root_id);
                if (!empty($subtree)) {
                    return $subtree;
                }
            }
        }

        return [];
    }

    /**
     * Parse the categories of a tree for display
     *
     * @access public
     * @param array $tree
     * @param int $endLevel
     * @return mixed
     */
    public static function parseTree($tree, $startLevel = 1, $endLevel = 99)
    {
        $current_category_id = ModMageBridgeMenuHelper::getCurrentCategoryId();
        $current_category_path = ModMageBridgeMenuHelper::getCurrentCategoryPath();

        if (is_array($tree) && count($tree) > 0) {
            foreach ($tree as $index => $item) {
                $item['path'] = explode('/', $item['path']);

                if (empty($item)) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove disabled categories
                if ($item['is_active'] != 1) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove categories that should not be in the menu
                if (isset($item['include_in_menu']) && $item['include_in_menu'] != 1) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove items from the wrong start-level
                if ($startLevel > 0 && $item['level'] < $startLevel && !in_array($current_category_id, $item['path'])) {
                    unset($tree[$index]);
                    continue;
                }

                // Remove items from the wrong end-level
                if ($item['level'] > $endLevel) {
                    unset($tree[$index]);
                    continue;
                }

                // Handle HTML-entities in the title
                if (isset($item['name'])) {
                    $item['name'] = htmlspecialchars($item['name']);
                }

                // Parse the children-tree
                if (!empty($item['children'])) {
                    $item['children'] = ModMageBridgeMenuHelper::parseTree($item['children'], $startLevel, $endLevel);
                } else {
                    $item['children'] = [];
                }

                // Translate the URL into Joomla! SEF URL
                if (empty($item['url'])) {
                    $item['url'] = '';
                } else {
                    $item['url'] = MageBridgeUrlHelper::route($item['url']);
                }

                $tree[$index] = $item;
            }
        }

        return $tree;
    }

    /**
     * Helper-method to return a CSS-class string
     *
     * @access public
     * @param JParameter $params
     * @param array $item
     * @param int $level
     * @param int $counter
     * @param array $tree
     * @return string
     */
    public static function getCssClass($params, $item, $level, $counter, $tree)
    {
        $current_category_id = ModMageBridgeMenuHelper::getCurrentCategoryId();
        $current_category_path = ModMageBridgeMenuHelper::getCurrentCategoryPath();

        $class = [];

        if (isset($item['entity_id'])) {
            if ($item['entity_id'] == $current_category_id) {
                $class[] = 'current';
                $class[] = 'active';
            } elseif (in_array($item['entity_id'], $current_category_path)) {
                $class[] = 'active';
            }

            $class[] = 'category-' . $item['entity_id'];
            $class[] = 'category-' . $item['url_key'];
        }

        if (isset($item['children_count']) && $item['children_count'] > 0) {
            $class[] = 'parent';
        }

        if ($params->get('css_level', 0) == 1) {
            $class[] = 'level' . $level;
        }

        if ($params->get('css_firstlast', 0) == 1) {
            if ($counter == 0) {
                $class[] = 'first';
            }

            if ($counter == count($tree)) {
                $class[] = 'last';
            }
        }

        if ($params->get('css_evenodd', 0) == 1) {
            if ($counter % 2 == 0) {
                $class[] = 'even';
            }

            if ($counter % 2 == 1) {
                $class[] = 'odd';
            }
        }

        $class = array_unique($class);
        $class = implode(' ', $class);

        return $class;
    }

    /**
     * Helper-method to return the current category ID
     *
     * @access public
     * @param null
     * @return int
     */
    public static function getCurrentCategoryId()
    {
        static $current_category_id = false;

        if ($current_category_id == false) {
            $config = MageBridge::getBridge()->getSessionData();
            $current_category_id = (isset($config['current_category_id'])) ? $config['current_category_id'] : 0;
        }

        return $current_category_id;
    }

    /**
     * Helper-method to return the current category path
     *
     * @access public
     * @param null
     * @return array
     */
    public static function getCurrentCategoryPath()
    {
        static $current_category_path = false;

        if ($current_category_path == false) {
            $config = MageBridge::getBridge()->getSessionData();
            $current_category_path = (isset($config['current_category_path'])) ? explode('/', $config['current_category_path']) : [];
        }

        return $current_category_path;
    }
}
