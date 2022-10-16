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

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

// Import namespaces
use Joomla\Utilities\ArrayHelper;

/**
 * Form Field-class for selecting Magento CSS-stylesheets
 */
class MagebridgeFormFieldStylesheets extends MageBridgeFormFieldAbstract
{
    /**
     * Form field type
     */
    public $type = 'Magento stylesheets';

    /**
     * Method to get the output of this element
     *
     * @return string
     */
    protected function getInput()
    {
        $name      = $this->name;
        $options   = null;

        if ($this->getConfig('api_widgets') == true) {
            $cache   = JFactory::getCache('com_magebridge.admin');
            $options = $cache->call(['MagebridgeFormFieldStylesheets', 'getResult']);

            if (empty($options) && !is_array($options)) {
                $this->debugger->warning('Unable to obtain MageBridge API Widget "stylesheets"', $options);
            }
        }

        MageBridgeTemplateHelper::load('jquery');
        JHtml::script('media/com_magebridge/js/backend-customoptions.js');

        $html = '';
        $html .= $this->getRadioHTML();
        $html .= '<br/><br/>';
        $html .= $this->getSelectHTML($options);

        return $html;
    }

    /**
     * Method to get the HTML of the disable_css_mage element
     *
     * @return string
     */
    public function getRadioHTML()
    {
        $name  = 'disable_css_all';
        $value = $this->getConfig('disable_css_all');

        $options = [
            ['value' => '0', 'label' => 'JNO'],
            ['value' => '1', 'label' => 'JYES'],
            ['value' => '2', 'label' => 'JONLY'],
            ['value' => '3', 'label' => 'JALL_EXCEPT'],
        ];

        foreach ($options as $index => $option) {
            $option['label'] = JText::_($option['label']);
            $options[$index] = ArrayHelper::toObject($option);
        }

        $attributes = null;

        return JHtml::_('select.radiolist', $options, $name, $attributes, 'value', 'label', $value);
    }

    /**
     * Method to get the HTML of the disable_css_all element
     *
     * @param array  $options
     *
     * @return string
     */
    public function getSelectHTML($options)
    {
        $name  = 'disable_css_mage';
        $value = MageBridgeHelper::getDisableCss();

        $current = $this->getConfig('disable_css_all');
        $disabled = null;

        if ($current == 1 || $current == 0) {
            $disabled = 'disabled="disabled"';
        }

        if (!empty($options) && is_array($options)) {
            $size = (count($options) > 10) ? 10 : count($options);
            array_unshift($options, ['value' => '', 'label' => '- ' . JText::_('JNONE') . ' -']);

            return JHtml::_('select.genericlist', $options, $name . '[]', 'multiple="multiple" size="' . $size . '" ' . $disabled, 'value', 'label', $value);
        }

        return '<input type="text" name="' . $name . '" value="' . implode(',', $value) . '" />';
    }

    /**
     * Method to get a list of scripts from the API
     *
     * @return array
     */
    public static function getResult()
    {
        $bridge  = MageBridgeModelBridge::getInstance();
        $headers = $bridge->getHeaders();

        if (empty($headers)) {
            // Send the request to the bridge
            $register = MageBridgeModelRegister::getInstance();
            $register->add('headers');

            $bridge->build();

            $headers = $bridge->getHeaders();
        }

        $stylesheets = [];

        if (!empty($headers['items'])) {
            foreach ($headers['items'] as $item) {
                if (strstr($item['type'], 'css')) {
                    $stylesheets[] = [
                        'value' => $item['name'],
                        'label' => $item['name'],
                    ];
                }
            }
        }

        return $stylesheets;
    }
}
