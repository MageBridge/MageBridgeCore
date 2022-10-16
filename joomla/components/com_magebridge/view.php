<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class
 * @package MageBridge
 */
class MageBridgeView extends YireoAbstractView
{
    protected $block_name = null;
    protected $block = null;
    protected $block_built = false;

    /**
     * Main constructor method
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        // Call the parent constructor
        parent::__construct($config);

        // Import use full variables from JFactory
        $this->db = JFactory::getDbo();
        $this->doc = JFactory::getDocument();
        $this->user = JFactory::getUser();
        $this->app = JFactory::getApplication();
        $this->input = $this->app->input;
    }

    /**
     * Method to display the requested view
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Add debugging
        $debugHelper = new MageBridgeDebugHelper();
        $debugHelper->addDebug();

        // Build the block
        $this->block = $this->build();
        if (!empty($this->block)) {
            $this->block = $this->addFixes($this->block);
        }

        // Display the view
        parent::display($tpl);
    }

    /**
     * Helper-method to build the bridge
     *
     * @param string $block_name
     * @return null
     */
    public function build()
    {
        static $block = null;
        if (empty($block)) {
            // Get the register and add all block-requirements to it
            $register = MageBridgeModelRegister::getInstance();
            $register->add('headers');
            $register->add('block', $this->block_name);

            // Only request breadcrumbs if we are loading another page than the homepage
            if (MageBridgeModelConfig::load('enable_breadcrumbs') == 1) {
                $request = MageBridgeUrlHelper::getRequest();
                if (!empty($request)) {
                    $register->add('breadcrumbs');
                }
            }

            // Build the bridge
            MageBridgeModelDebug::getInstance()->notice('Building view');
            $bridge = MageBridge::getBridge();
            $bridge->build();
            $bridge->setHeaders();

            // Add things for the frontend specifically
            $application = JFactory::getApplication();
            if ($application->isSite()) {
                if (MageBridgeModelConfig::load('enable_breadcrumbs') == 1) {
                    $bridge->setBreadcrumbs();
                }
            }

            // Query the bridge for the block
            $block = $bridge->getBlock($this->block_name);

            // Empty blocks
            if (empty($block)) {
                MageBridgeModelDebug::getInstance()->warning('JView: Empty block: '.$this->block_name);
                $block = JText::_($this->getOfflineMessage());
            }
        }

        return $block;
    }

    /**
     * Helper-method to fetch add block to the bridge-register
     *
     * @param string $block_name
     * @return null
     */
    public function setBlock($block_name)
    {
        // Set the block-name for internal usage
        $this->block_name = $block_name;
    }

    /**
     * Helper-method to set the request as REQUEST-variable
     *
     * @param string $request
     * @return null
     */
    public function setRequest($request)
    {
        $segments = explode('/', $request);
        if (!empty($segments)) {
            foreach ($segments as $index => $segment) {
                $segments[$index] = preg_replace('/^([a-zA-Z0-9]+)\:/', '\1-', $segment);
            }
            $request = implode('/', $segments);
        }

        MageBridgeUrlHelper::setRequest($request);
    }

    /**
     * Helper-method to add specific fixes to the current page
     *
     * @param string $html
     * @return string
     */
    public function addFixes($html)
    {
        // Check for a template-override of this file
        $application = JFactory::getApplication();
        $file = JPATH_BASE.'/templates/'.$application->getTemplate().'/html/com_magebridge/fixes.php';
        if (!file_exists($file)) {
            $file = JPATH_SITE.'/components/com_magebridge/views/fixes.php';
        }

        // Include the file and allow $html to be altered
        require_once $file;
        return $html;
    }

    /**
     * Helper-method to get the offline message
     *
     * @param null
     * @return string
     */
    public function getOfflineMessage()
    {
        return MageBridgeModelConfig::load('offline_message');
    }
}
