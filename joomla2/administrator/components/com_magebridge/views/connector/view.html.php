<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
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
class MageBridgeViewConnector extends MageBridgeView
{
    /*
     * Flag to determine whether to load the toolbar
     */
    protected $loadToolbar = false;

    /*
    /*
     * Main constructor method
     *
     * @access public
     * @subpackage Yireo
     * @param array $config
     * @return null
     */
    public function __construct($config = array())
    {
        // Call the parent constructor
        parent::__construct($config);
    }

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
	public function display($tpl = null)
	{
        // Load the toolbar
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();

        // Fetch this item
        $this->fetchItem();

        // Read the connector-parameters 
        $params = null;
        if (!empty($this->item->name) && !empty($this->item->type)) {
            $file = JPATH_SITE.'/components/com_magebridge/connectors/'.$this->item->type.'/'.$this->item->name.'.xml';
            if (is_file($file)) {
                $params = YireoHelper::toRegistry($this->item->params, $file);
                if(YireoHelper::isJoomla15()) {
		            $this->assignRef('params', $params);
                } else {
                    $paramsArray = $params->toArray();
                    if(!empty($paramsArray)) {
                        $form = JForm::getInstance('params', $file);
                        $form->bind(array('params' => $paramsArray));
                        $this->assignRef('params_form', $form);
                    }
                }
            }
        }

		parent::display($tpl);
	}
}
