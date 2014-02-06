<?php
/**
 * Joomla! Yireo Library
 *
 * @author Yireo (http://www.yireo.com/)
 * @package YireoLib
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com/
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once dirname(dirname(__FILE__)).'/loader.php';

/**
 * Home View class
 *
 * @package Yireo
 */
class YireoViewHome extends YireoView
{
    /*
     * Identifier of the library-view
     */
    protected $_viewParent = 'home';

    /*
     * Main constructor method
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    public function __construct()
    {
        $this->loadToolbar = false;

        // Call the parent constructor
        parent::__construct();
            
        // Initialize the toolbar
        if (file_exists(JPATH_COMPONENT.'/config.xml')) {
            if(YireoHelper::isJoomla15() || JFactory::getUser()->authorise('core.admin')) {
                JToolBarHelper::preferences($this->_option, 600, 800);
            }
        }
    }

    /*
     * Main display method
     *
     * @access public
     * @subpackage Yireo
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Variables
        $document = JFactory::getDocument();

        // Generate the backend feeds
        $backend_feed = $this->params->get('backend_feed', 1);
        $this->assignRef( 'backend_feed', $backend_feed);
        if ($backend_feed == 1) {
            $this->ajax('index.php?option='.$this->_option.'&view=home&format=ajax&layout=feeds', 'latest_news');
            $this->ajax('index.php?option='.$this->_option.'&view=home&format=ajax&layout=promotion', 'promotion');
        }

        // Add additional CSS
        $document->addStylesheet('https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700');
        $document->addStylesheet('https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css');

        // Get the current version
        $current_version = YireoHelper::getCurrentVersion();
        $this->assignRef( 'current_version', $current_version );

        parent::display($tpl);
    }

    /*
     * Helper-method to construct a specific icon
     *
     * @param string $view
     * @param string $text
     * @param string $image
     * @param string $folder
     * @return null
     */
    public function icon($view, $text, $image, $folder = null, $target = null)
    {
        $image = 'icon-48-'.$image;
        if (empty($folder)) {
            $folder = '../media/'.$this->_option.'/images/';
        }

        if (!file_exists(JPATH_ADMINISTRATOR.'/'.$folder.'/'.$image)) {
            $folder = '/templates/'.$this->application->getTemplate().'/images/header/';
        }


        $icon = array();
        $icon['link'] = JRoute::_( 'index.php?option='.$this->_option.'&view='.$view );
        $icon['text'] = $text;
        $icon['target'] = $target;
        $icon['icon'] = '<img src="'.$folder.$image.'" title="'.$icon['text'].'" alt="'.$icon['text'].'" />';
        return $icon;
    }

    /*
     * Helper-method to set the page title
     *
     * @access protected
     * @subpackage Yireo
     * @param string $title
     * @return null
     */
    public function setTitle($title = null, $class= 'logo')
    {
        $component_title = YireoHelper::getData('title');
        $title = JText::_('LIB_YIREO_VIEW_HOME');
        if (file_exists( JPATH_SITE.'/media/'.$this->_option.'/images/'.$class.'.png' )) {
            JToolBarHelper::title($component_title.': '.$title, $class);
        } else {
            JToolBarHelper::title($component_title.': '.$title, 'generic.png');
        }
    }
}
