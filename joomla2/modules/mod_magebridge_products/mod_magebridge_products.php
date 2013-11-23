<?php
/**
 * Joomla! module MageBridge: Products block
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2012
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Read the parameters
$layout = $params->get('layout', 'default');

// Call the helper
require_once (dirname(__FILE__).'/helper.php');
$data = modMageBridgeProductsHelper::build($params);
$products = $data['products'];
$category = $data['category'];

// Add CSS and JavaScript 
$templateHelper = new MageBridgeTemplateHelper();
if (strstr($layout,'slideshow')) {
    if ($params->get('load_slideshow_jquery', 1) == 1) $templateHelper->load('jquery');
    if ($params->get('load_slideshow_jquery_easing', 1) == 1) $templateHelper->load('jquery-easing');
    if ($params->get('load_slideshow_jquery_cycle', 1) == 1) $templateHelper->load('js', 'jquery/jquery.cycle.all.min.js');
    if ($params->get('load_slideshow_css', 1) == 1) $templateHelper->load('css', 'mod-products-slideshow.css');
} else {
    if ($params->get('load_default_css', 1) == 1) $templateHelper->load('css', 'mod-products-default.css');
}

// Exit if no data have been received
if(empty($products) && $params->get('show_noitems', 1) == 0) {
    return;
}

// Include the layout-file
require(JModuleHelper::getLayoutPath('mod_magebridge_products', $layout));
