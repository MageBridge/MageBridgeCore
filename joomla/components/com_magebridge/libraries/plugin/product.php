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

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * Parent plugin-class
 */
class MageBridgePluginProduct extends MageBridgePlugin
{
    /**
     * Deprecated variable to migrate from the original connector-architecture to new Product Plugins
     */
    protected $connector_field = null;

    /**
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Constructor
     *
     * @access      protected
     *
     * @param       object $subject The object to observe
     * @param       array  $config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();
        $this->db = JFactory::getDbo();
    }

    /**
     * Method to check whether this plugin is enabled or not
     *
     * @param null
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Method to manipulate the MageBridge Product Relation backend-form
     *
     * @param JForm $form The form to be altered
     * @param JForm $data The associated data for the form
     *
     * @return boolean
     */
    public function onMageBridgeProductPrepareForm(&$form, $data)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Add the plugin-form to main form
        $this->loadFormFile($form);

        // Load the original values from the deprecated connector-architecture
        if (!empty($this->connector_field)) {
            return true;
        }

        $pluginName = $this->_name;

        if (empty($data['connector']) || empty($data['connector_value']) || $pluginName !== $data['connector']) {
            return true;
        }

        $form->bind(['actions' => [$this->connector_field => $data['connector_value']]]);

        return true;
    }

    /**
     * Method to manipulate the MageBridge Product Relation backend-form
     *
     * @param object $connector The connector-row
     *
     * @return boolean
     */
    public function onMageBridgeProductConvertField($connector, $actions)
    {
        // Check if this plugin can be used
        if ($this->isEnabled() === false) {
            return false;
        }

        // Load the original values from the deprecated connector-architecture
        if (empty($this->connector_field)) {
            return true;
        }

        $pluginName = $this->_name;

        if (empty($connector->connector) || empty($connector->connector_value) || $pluginName !== $connector->connector) {
            return true;
        }

        $actions = [$this->connector_field => $connector->connector_value];

        return $actions;
    }

    /**
     * @param JForm $form
     */
    protected function loadFormFile(&$form)
    {
        $formFile = JPATH_SITE . '/plugins/magebridgeproduct/' . $this->_name . '/form/form.xml';

        if (file_exists($formFile)) {
            $form->loadFile($formFile, false);
        }
    }
}
