<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/*
 * Parent plugin-class
 */
class MageBridgePluginProduct extends MageBridgePlugin
{
    /**
     * Constructor
     *
     * @access      protected
     * @param       object  $subject The object to observe
     * @param       array   $config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        $this->db = JFactory::getDBO();
    }

    /*
     * Method to check whether this plugin is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /*
     * Method to manipulate the MageBridge Product Relation backend-form
     *
     * @param JForm $form The form to be altered
     * @param JForm $data The associated data for the form
     * @return boolean
     */
    public function onMageBridgeProductPrepareForm($form, $data)
    {   
        $formFile = JPATH_SITE.'/plugins/magebridgeproduct/'.$this->_name.'/form.xml';
        if(file_exists($formFile)) {
            $form->loadFile($formFile, false);
        }
    }
}
