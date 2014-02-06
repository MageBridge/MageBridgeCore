<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** 
 * Yireo View Helper
 */
class YireoHelperView
{
    /*
     * Helper method to return a select-list
     *
     * @access public
     * @subpackage Yireo
     * @param 
     * @return array
     */
    static public function getSelectList($name, $options, $value = null, $js = false, $selectNone = true, $multipleSelect = false)
    {
        // Add a select-none option 
        if ($selectNone) {
            if (is_bool($selectNone) || is_numeric($selectNone)) {
                $selectNone = '';
            } else {
                $selectNone = '- '.$selectNone.' -';
            }
            array_unshift($options, array('value' => '', 'title' => $selectNone));
        }

        // Construct the attributes
        $attributes = array();
        if ($js == true) $attributes[] = 'onchange="document.adminForm.submit();"';
        if ($multipleSelect == true) {
            $multipleSelect = (int)$multipleSelect;
            if ($multipleSelect == 1) $multipleSelect = 4;
            if ($multipleSelect < count($options) && count($options) < 20) $multipleSelect = count($options);
            $attributes[] = 'multiple="multiple" size="'.$multipleSelect.'"';
        }

        // Return the select-box
        return JHTML::_('select.genericlist', $options, $name, implode(' ', $attributes), 'value', 'title', $value);
    }

    /*
     * Helper method to return select-options
     *
     * @access public
     * @subpackage Yireo
     * @param 
     * @return array
     */
    static public function getSelectOptions($items, $value = 'id', $title = 'title', $alt_title = 'name')
    {
        $options = array();
        if (!empty($items)) {
            foreach ($items as $item) {
                if (!empty($title) && isset($item->$value) && !empty($item->$title)) {
                    $option = array('value' => $item->$value, 'title' => $item->$title);

                } else if (!empty($alt_title) && isset($item->$value) && !empty($item->$alt_title)) {
                    $option = array('value' => $item->$value, 'title' => $item->$alt_title);

                } else if (empty($title) || (isset($item->$value) && !isset($item->$title))) {
                    $option = array('value' => $item->$value, 'title' => $item->$value);
                }

                if (isset($item->published) && $item->published == 0) $option['disable'] = 1;
                $options[] = $option;
            }
        }
        return $options;
    }

    /*
     * Helper method to trim text
     *
     * @access public
     * @subpackage Yireo
     * @param 
     * @return array
     */
    static public function trim($text)
    {
        $text = trim($text);
        $text = preg_replace('/^\<p\>\&nbsp\;\<\/p\>/','', $text);
        $text = preg_replace('/\<p\>\&nbsp\;\<\/p\>$/','', $text);
        return $text;
    }

    /*
     * Add the AJAX-script to the page
     *
     * @access public
     * @subpackage Yireo
     * @param string $url
     * @param string $div
     * @return null
     */
    static public function ajax($url = null, $div = null)
    {
        $document = JFactory::getDocument();
        if(stristr(get_class($document), 'html') == false) {
            return false;
        }

        if (YireoHelper::isJoomla15()) {
            JHtml::_('behavior.mootools');
            $script = "<script type=\"text/javascript\">\n"
                . "window.addEvent('domready', function(){\n"
                . "    var MBajax = new Ajax( '".$url."', {onSuccess: function(r){\n"
                . "        $('".$div."').innerHTML = r;\n"
                . "    }});\n"
                . "    MBajax.request();\n"
                . "});\n"
                . "</script>";
        } elseif (YireoHelper::isJoomla25()) {
            JHtml::_('behavior.mootools');
            $script = "<script type=\"text/javascript\">\n"
                . "window.addEvent('domready', function(){\n"
                . "    var MBajax = new Request({\n"
                . "        url: '".$url."', \n"
                . "        onComplete: function(r){\n"
                . "            $('".$div."').innerHTML = r;\n"
                . "        }\n"
                . "    }).send();\n"
                . "});\n"
                . "</script>";
        } else {
            $script = "<script type=\"text/javascript\">\n"
                . "jQuery(document).ready(function() {\n"
                . "    var MBajax = jQuery.ajax({\n"
                . "        url: '".$url."', \n"
                . "        method: 'get', \n"
                . "        success: function(result){\n"
                . "            if (result == '') {\n"
                . "                alert('Empty result');\n"
                . "            } else {\n"
                . "                jQuery('#".$div."').html(result);\n"
                . "            }\n"
                . "        }\n"
                . "    });\n"
                . "});\n"
                . "</script>";
        }

        $document->addCustomTag( $script );
    }
}
