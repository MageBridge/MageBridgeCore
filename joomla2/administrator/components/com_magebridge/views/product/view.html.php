<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT.'/view.php';

// Import the needed libraries
jimport('joomla.filter.output');

/**
 * HTML View class
 */
class MageBridgeViewProduct extends MageBridgeView
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
	public function display($tpl = null)
	{
        // Fetch this item
        $this->fetchItem();

        // Initialize parameters
        $file = JPATH_ADMINISTRATOR.'/components/com_magebridge/models/product.xml';
        if(YireoHelper::isJoomla15()) {
            $params = YireoHelper::toRegistry($this->item->params, $file);
		    $this->assignRef('params', $params);
        } else {
            $form = JForm::getInstance('params', $file);
		    $this->assignRef('params_form', $form);
        }

        // Build the fields
        $this->lists['product'] = MageBridgeFormHelper::getField('product', 'sku', $this->item->sku, null);

        $connectors = MageBridgeConnectorProduct::getInstance()->getConnectors();
		$this->assignRef('connectors', $connectors);

		parent::display($tpl);
	}
}
