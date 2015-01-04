<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge model for outputting widgets
 */
class Yireo_MageBridge_Model_Widget 
{
    /*
     * Output a certain widgets HTML
     * 
     * @access public
     * @param int $widget_id
     * @param array $arguments
     * @return string
     */
    public function getOutput($widget_id, $arguments = array())
    {
        $widget = Mage::getModel('widget/widget_instance')->load($widget_id);

        $parameters = $widget->getWidgetParameters();
        if(!isset($parameters['template'])) {
            $templates = $widget->getWidgetTemplates();
            if(isset($templates['default']['value'])) $parameters['template'] = $templates['default']['value'];
        }

        $htmlParameters = array();
        foreach($parameters as $name => $value) {
            if(is_array($value)) $value = implode(',', $value);
            $htmlParameters[] = $name.'="'.$value.'"';
        }
        
        $html = '{{widget type="'.$widget->getType().'" '.implode(' ',$htmlParameters).'}}';
        $debug = false;
        if($debug == true) {
            $response = $html;
        } else {
            $response = null;
            if($processor = Mage::getModel('widget/template_filter')) {
                $response = $processor->filter($html);
            }
        }

        // Check for non-string output
        if(empty($response) || !is_string($response)) {
            return null;
        }

        // Prepare the response for the bridge
        $response = Mage::helper('magebridge/encryption')->base64_encode($response);
        return $response;
    }
}
