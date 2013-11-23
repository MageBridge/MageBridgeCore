<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/*
 * Element-class for a dropdown of T3-template layouts
 */
class JElementT3Layout extends JElement
{
    /*
     * Name for this element
     */
    public $_name = 'T3 layout';

    /*
     * Method to get the HTML of this element
     *
     * @param string $name
     * @param string $value
     * @param object $node
     * @param string $control_name
     * @return string
     */
    public function fetchElement($name, $value, &$node, $control_name)
    {
        // Check for the T3 framework
        if(!function_exists('t3_import')) {
            return '- No configuration needed -';
        }

        // Add the control name
        if(!empty($control_name)) $name = $control_name.'['.$name.']';
                
        t3_import('core/admin/util');

        $adminutil = new JAT3_AdminUtil();
        $template  = $adminutil->get_active_template();
        $layouts = $adminutil->getLayouts();
        foreach($layouts as $layoutIndex => $layoutObject) {
            $options[] = array( 'value' => $layoutIndex, 'label' => $layoutIndex);
        }

        return JHTML::_('select.genericlist', $options, $name, null, 'value', 'label', $value);
    }
}
