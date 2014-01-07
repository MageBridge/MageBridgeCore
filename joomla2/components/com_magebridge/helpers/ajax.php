<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Helper for dealing with AJAX lazy-loading
 */
class MageBridgeAjaxHelper
{
    /*
     * Helper-method to return the right AJAX-URL
     *
     * @param mixed $user
     * @return bool
     */
    static public function getLoaderImage()
    {
        $template = JFactory::getApplication()->getTemplate();
        if (file_exists(JPATH_SITE.'/templates/'.$template.'/images/com_magebridge/loader.gif')) {
            return 'templates/'.$template.'/images/com_magebridge/loader.gif';
        } else {
            return 'media/com_magebridge/images/loader.gif';
        }
    }

    /*
     * Helper-method to return the right AJAX-URL
     *
     * @param mixed $user
     * @return bool
     */
    static public function getUrl($block)
    {
        $url = 'index.php?option=com_magebridge&view=ajax&tmpl=component&block='.$block;
        $request = MageBridgeUrlHelper::getRequest();
        if (!empty($request)) $url .= '&request='.$request;
        return $url;
    }

    /*
     * Helper-method to return the right AJAX-script
     *
     * @param string $block
     * @param string $element
     * @param string $url
     * @return bool
     */
    static public function getScript($block, $element, $url = null)
    {
        // Set the default AJAX-URL
        if (empty($url)) $url = self::getUrl($block);

        // Load ProtoType
        if (MageBridgeTemplateHelper::hasPrototypeJs() == true) {
            $script = "Event.observe(window,'load',function(){new Ajax.Updater('$element','$url',{method:'get'});});";

        // Load jQuery
        } else if (JFactory::getApplication()->get('jquery') == true) {
            $script = "jQuery(document).ready(function(){\n"
                . "    jQuery('#".$element."').load('".$url."');"
                . "});\n"
            ;

        // Load MooTools (Joomla!)
        } else if (MageBridgeHelper::isJoomla15()) {
            JHTML::_('behavior.mootools');
            $script = "window.addEvent('domready', function(){\n"
                . "    var MBajax = new Ajax( '".$url."', {onSuccess: function(r){\n"
                . "        $('".$element."').innerHTML = r;\n"
                . "    }});\n"
                . "    MBajax.request();\n"
                . "});\n"
            ;

        // Load MooTools (Joomla! 1.6)
        } else {
            JHTML::_('behavior.mootools');
            $script = "window.addEvent('domready', function(){\n"
                . "    var MBajax = new Request({\n"
                . "        url: '".$url."', \n"
                . "        onComplete: function(r){\n"
                . "            $('".$element."').innerHTML = r;\n"
                . "        }\n"
                . "    }).send();\n"
                . "});\n"
            ;
        }

        return $script;
    }
}
