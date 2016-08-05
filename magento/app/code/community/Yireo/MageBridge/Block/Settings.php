<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

/**
 * MageBridge class for the settings-block
 */
class Yireo_MageBridge_Block_Settings extends Mage_Core_Block_Template
{
    /**
     * Constructor method
     */
    public function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
        $this->setTemplate('magebridge/settings.phtml');
    }

    /**
     * Helper to return the header of this page
     *
     * @param string $title
     *
     * @return string
     */
    public function getHeader($title = null)
    {
        return 'MageBridge - ' . $this->__($title);
    }

    /**
     * Helper to return the menu
     *
     * @return string
     */
    public function getMenu()
    {
        return $this->getLayout()->createBlock('magebridge/menu')->toHtml();
    }

    /**
     * Helper to return the save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/save');
    }

    /**
     * Helper to reset MageBridge values for event forwarding
     *
     * @return string
     */
    public function getResetEventsUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/resetevents');
    }

    /**
     * Helper to reset Joomla! to Magento usermapping by ID
     *
     * @return string
     */
    public function getResetUsermapUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/resetusermap');
    }

    /**
     * Helper to reset some MageBridge values to null
     *
     * @return string
     */
    public function getResetApiUrl()
    {
        return Mage::getModel('adminhtml/url')->getUrl('adminhtml/magebridge/resetapi');
    }

    /**
     * Render block HTML
     *
     * @return mixed
     */
    protected function _toHtml()
    {
        $this->addAccordion();
        $this->addToolbarButtons();

        return parent::_toHtml();
    }

    /**
     * Add the accordion block as child
     */
    protected function addAccordion()
    {
        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')->setId('magebridge');

        $accordion->addItem('joomla', array(
            'title' => Mage::helper('adminhtml')->__('Joomla! API Connections'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_joomla')->toHtml(),
            'open' => true,
        ));

        $accordion->addItem('events', array(
            'title' => Mage::helper('adminhtml')->__('Event Forwarding'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_events')->toHtml(),
            'open' => true,
        ));

        $accordion->addItem('other', array(
            'title' => Mage::helper('adminhtml')->__('Other Settings'),
            'content' => $this->getLayout()->createBlock('magebridge/settings_other')->toHtml(),
            'open' => true,
        ));

        $this->setChild('accordion', $accordion);
    }

    /**
     * Add toolbar buttons
     */
    protected function addToolbarButtons()
    {
        $this->setChild('resetevents_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Reset Events'),
                    'onclick' => 'magebridgeForm.submit(\'' . $this->getResetEventsUrl() . '\')',
                    'class' => 'delete'
                ))
        );

        if (Mage::helper('magebridge')->useJoomlaMap()) {
            $this->setChild('resetusermap_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('catalog')->__('Reset Usermap'),
                        'onclick' => 'magebridgeForm.submit(\'' . $this->getResetUsermapUrl() . '\')',
                        'class' => 'delete'
                    ))
            );
        }

        $this->setChild('resetapi_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Reset API'),
                    'onclick' => 'magebridgeForm.submit(\'' . $this->getResetApiUrl() . '\')',
                    'class' => 'delete'
                ))
        );
    }
}
