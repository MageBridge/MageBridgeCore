<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// Load the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

// Get the sh404sef configuration
$sefConfig = shRouter::shGetConfig(); 
$component_prefix = shGetComponentPrefix($option);
if (empty($component_prefix)) $component_prefix = 'shop';

// Build the URL
$segments = array();
$segments[] = $component_prefix;

// Set the alias if it is not present
if (!empty($vars['request'])) {
    $request = explode('/', urldecode($vars['request']));
    if (!empty($request)) {
        foreach ($request as $r) {
            if (!empty($r)) $segments[] = $r;
        }
    } else {
        $segments[] = $vars['request'];
    }
} else if ($vars['view'] == 'content' && !empty($vars['layout'])) {
    $segments[] = 'content';
    $segments[] = $vars['layout'];
}

// Add the extra segments
$system = array('Itemid', 'lang', 'option', 'request', 'view', 'layout', 'task');
if (!empty($vars)) {
    foreach ($vars as $name => $value) {
        if (!in_array($name, $system)) {
            $segments[] = "$name-$value";
        }
    }
}


// Convert the segments into the URL-string
if (count($segments) > 0) {
    $string = sef_404::sefGetLocation($string, $segments, null);
}

// End
