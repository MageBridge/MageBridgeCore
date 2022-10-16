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

// Require the parent view
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';
require_once JPATH_SITE . '/components/com_magebridge/models/config/value.php';

/**
 * Bridge configuration class
 */
class MageBridgeModelConfig extends YireoAbstractModel
{
    /**
     * Array of configured data
     *
     * @var array
     */
    protected $data = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Array of default values
     *
     * @var array
     */
    protected $defaults = null;

    /**
     * Constructor
     *
     * @param null
     *
     * @retun array
     */
    public function __construct()
    {
        $this->defaults = (new MageBridgeModelConfigDefaults())->getDefaults();

        parent::__construct();
    }

    /**
     * Method to fetch the data
     *
     * @return MageBridgeModelConfig
     */
    public static function getSingleton()
    {
        static $instance;

        if ($instance === null) {
            $instance = new MageBridgeModelConfig();
        }

        return $instance;
    }

    /**
     * Method to set data
     *
     * @param array $data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Method to get data
     *
     * @return array
     */
    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->data)) {
            $query = $this->_db->getQuery(true);
            $query->select($this->_db->quoteName(['id', 'name', 'value']));
            $query->from($this->_db->quoteName('#__magebridge_config', 'c'));

            $this->_db->setQuery($query);
            $this->data = $this->_db->loadObjectList();
        }

        return $this->data;
    }

    /**
     * Method to get the defaults
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @return array
     */
    private function loadDefaultConfig()
    {
        foreach ($this->getDefaults() as $name => $value) {
            $this->config[$name] = (new MagebridgeModelConfigValue(['name' => $name, 'value' => $value]))->toArray();
        }

        return $this->config;
    }

    /**
     * @param $data
     *
     * @return array
     */
    private function pushDataIntoConfig($data)
    {
        foreach ($this->config as $name => $c) {
            if (!empty($data)) {
                foreach ($data as $d) {
                    if ($d->name == $c['name']) {
                        $d->isOriginal  = ($this->config[$name]['value'] == $d->value) ? 1 : 0;
                        $d->description = $c['description'];

                        $this->config[$name] = (new MagebridgeModelConfigValue((array) $d))->toArray();
                        break;
                    }
                }
            }
        }

        return $this->config;
    }

    private function overrideConfig()
    {
        $this->config['method'] = (new MagebridgeModelConfigValue(['value' => 'post']))->toArray();

        // Determine the right update format
        if ($this->config['update_format']['value'] == '') {
            jimport('joomla.application.component.helper');
            $component = JComponentHelper::getComponent('com_magebridge');
            $params    = YireoHelper::toRegistry($component->params);
            $value     = $params->get('update_format', 'tar.gz');

            $this->config['update_format'] = (new MagebridgeModelConfigValue(['value' => $value]))->toArray();
        }

        // Disable widgets if needed
        if ($this->input->getInt('widgets', 1) == 0) {
            $this->config['api_widgets'] = (new MagebridgeModelConfigValue(['value' => 0]))->toArray();
        }

        // Overload a certain values when the Magento Admin Panel needs to be loaded
        if ($this->app->isAdmin() && $this->input->getCmd('option') == 'com_magebridge' && $this->input->getCmd('view') == 'root') {
            //$this->config['debug'] = (new MagebridgeModelConfigValue(['value' => 0]))->toArray();
            $this->config['disable_js_all'] = (new MagebridgeModelConfigValue(['value' => 1]))->toArray();
            $this->config['disable_js_mootools'] = (new MagebridgeModelConfigValue(['value' => 1]))->toArray();
        }

        // Return the URL
        if (!isset($this->config['url'])) {
            $url = '';

            if (!empty($this->config['host']['value'])) {
                $url = $this->config['protocol']['value'] . '://' . $this->config['host']['value'] . '/';

                if (!empty($this->config['basedir']['value'])) {
                    $url .= $this->config['basedir']['value'] . '/';
                }
            }

            $this->config['url'] = (new MagebridgeModelConfigValue(['value' => $url]))->toArray();
        }

        // Return the port-number
        if (!isset($this->config['port'])) {
            $value = ($this->config['protocol']['value'] == 'http') ? 80 : 443;
            $this->config['port'] = (new MagebridgeModelConfigValue(['value' => $value]))->toArray();
        }

        return $this->config;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        if (empty($this->config)) {
            $this->loadDefaultConfig();
            $this->pushDataIntoConfig($this->getData());
            $this->overrideConfig();
        }

        return $this->config;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setConfigValue($name, $value)
    {
        $this->config[$name]['value'] = $value;
    }

    /**
     * Static method to get data
     *
     * @param string $element
     * @param mixed  $overloadValue
     *
     * @return mixed
     */
    public static function load($element = null, $overloadValue = null)
    {
        static $config = null;
        $configModel = self::getSingleton();

        if (empty($config)) {
            $config = $configModel->getConfiguration();
        }

        // Allow overriding values
        if (!empty($element) && isset($config[$element]) && $overloadValue !== null) {
            $configModel->setConfigValue($element, $overloadValue);

            return $overloadValue;
        }

        // Return any other element
        if ($element != null && isset($config[$element])) {
            if (!isset($config[$element]['value'])) {
                //print_r($config[$element]);
            }

            return $config[$element]['value'];
        }

        // Return no value
        if (!empty($element)) {
            return null;
        }

        // Return the configuration itself
        return $config;
    }

    /**
     * Method to check a specific configuratione-element
     *
     * @param string $element
     * @param string $value
     *
     * @return string|null
     */
    public static function check($element, $value = null)
    {
        // Reset an empty value to its original value
        if (empty($value)) {
            $value = MageBridgeModelConfig::load($element);
        }

        // Check for settings that should not be kept empty
        $nonempty = ['host', 'website', 'api_user', 'api_key'];
        if (MageBridgeModelConfig::allEmpty() == false && in_array($element, $nonempty) && empty($value)) {
            return JText::sprintf('Setting "%s" is empty - Please configure it below', JText::_($element));
        }

        // Check host
        if ($element == 'host') {
            if (preg_match('/([^a-zA-Z0-9\.\-\_\:]+)/', $value) == true) {
                return JText::_('Hostname contains illegal characters. Note that a hostname is not an URL, but only a fully qualified domainname.');
            }

            if (gethostbyname($value) == $value && !preg_match('/([0-9\.]+)/', $value)) {
                return JText::sprintf('DNS lookup of hostname %s failed', $value);
            }

            if (MageBridgeModelConfig::load('api_widgets') == true) {
                $bridge = MageBridgeModelBridge::getInstance();
                $data   = $bridge->build();

                if (empty($data)) {
                    $url = $bridge->getMagentoBridgeUrl();

                    return JText::sprintf('Unable to open a connection to <a href="%s" target="_new">%s</a>', $url, $url);
                }
            }
        }

        // Check supportkey
        if ($element == 'supportkey' && empty($value)) {
            return JText::sprintf('Please configure your support-key. Your support-key can be obtained from %s', MageBridgeHelper::getHelpText('subscriptions'));
        }

        // Check API widgets
        if ($element == 'api_widgets' && $value != 1) {
            return JText::_('API widgets are disabled');
        }

        // Check offline
        if ($element == 'offline' && $value == 1) {
            return JText::_('Bridge is disabled through settings');
        }

        // Check website
        if ($element == 'website' && !empty($value)) {
            if (is_numeric($value) == false) {
                return JText::sprintf('Website ID needs to be a numeric value. Current value is "%s"', $value);
            }
        }

        /**
         * if ($element == 'storeview' && !empty($value)) {
         * if ( preg_match( '/([a-zA-Z0-9\.\-\_]+)/', $value ) == false ) {
         * return JText::_( 'Store-name contains illegal characters: '.$value );
         * } else {
         * $storeviews = MagebridgeModelConfig::getStoreNames();
         * if (!is_array($storeviews) && $storeviews != 0) {
         * return JText::_($storeviews);
         *
         * } else {
         *
         * $match = false;
         * if (!empty($storeviews)) {
         * foreach ($storeviews as $storeview) {
         * if ($storeview['value'] == $value) {
         * $match = true;
         * break;
         * }
         * }
         * }
         *
         * if ($match == false) {
         * $msg = JText::sprintf( 'Store-names detected, but "%s" is not one of them', $value );
         * return $msg;
         * }
         * }
         * }
         * }
         */

        // Check basedir
        if ($element !== 'basedir') {
            return null;
        }

        if (empty($value)) {
            return null;
        }

        if (preg_match('/([a-zA-Z0-9\.\-\_]+)/', $value) == false) {
            return JText::_('Basedir contains illegal characters');
        }

        $root         = MageBridgeUrlHelper::getRootItem();
        $joomla_host  = JUri::getInstance()
            ->toString(['host']);
        $magento_host = MageBridgeModelConfig::load('host');

        // Check whether the Magento basedir conflicts with the MageBridge alias
        if (!empty($root) && !empty($root->route) && $root->route == $value && $joomla_host == $magento_host) {
            return JText::_('Magento basedir is same as MageBridge alias, which is not possible');
        }
    }

    /**
     * Helper method to detect whether the whole configuration is empty
     *
     * @param null
     *
     * @return bool
     */
    public static function allEmpty()
    {
        static $allEmpty = null;

        if (empty($allEmpty)) {
            $allEmpty = true;
            $config   = MageBridgeModelConfig::load();
            foreach ($config as $c) {
                if ($c['core'] == 0) {
                    $allEmpty = false;
                    break;
                }
            }
        }

        return $allEmpty;
    }

    /**
     * Method to store the configuration in the database
     *
     * @param array $post
     *
     * @return bool
     * @throws Exception
     */
    public function store($post)
    {
        // If the custom list is empty, set another value
        if (isset($post['disable_js_custom']) && isset($post['disable_js_all'])) {
            if ($post['disable_js_all'] == 2 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 0;
            }

            if ($post['disable_js_all'] == 3 && empty($post['disable_js_custom'])) {
                $post['disable_js_all'] = 1;
            }
        }

        // Convert "disable_css_mage" array into comma-seperated string
        if (isset($post['disable_css_mage']) && is_array($post['disable_css_mage'])) {
            if (empty($post['disable_css_mage'][0])) {
                array_shift($post['disable_css_mage']);
            }

            if (empty($post['disable_css_mage'])) {
                $post['disable_css_mage'] = '';
            } else {
                $post['disable_css_mage'] = implode(',', $post['disable_css_mage']);
            }
        }

        // Convert "disable_js_mage" array into comma-seperated string
        if (isset($post['disable_js_mage']) && is_array($post['disable_js_mage'])) {
            if (empty($post['disable_js_mage'][0])) {
                array_shift($post['disable_js_mage']);
            }

            if (empty($post['disable_js_mage'])) {
                $post['disable_js_mage'] = '';
            } else {
                $post['disable_js_mage'] = implode(',', $post['disable_js_mage']);
            }
        }

        // Clean the basedir
        if (!empty($post['basedir'])) {
            $post['basedir'] = preg_replace('/^\//', '', $post['basedir']);
            $post['basedir'] = preg_replace('/\/$/', '', $post['basedir']);
        }

        // Check whether the URL-table contains entries
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__magebridge_urls'));
        $query->where($db->quoteName('published') . ' = 1');
        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            $post['load_urls'] = 1;
        } else {
            $post['load_urls'] = 0;
        }

        // Check whether the stores-table contains entries
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__magebridge_stores'));
        $query->where($db->quoteName('published') . ' = 1');
        $rows = $db->loadObjectList();

        if (!empty($rows)) {
            $post['load_stores'] = 1;
        } else {
            $post['load_stores'] = 0;
        }

        // Load the existing configuration
        $config = MageBridgeModelConfig::load();

        // Overload each existing value with the posted value (if it exists)
        foreach ($config as $name => $c) {
            if (isset($post[$name]) && isset($config[$name])) {
                $config[$name]['value'] = $post[$name];
            }
        }

        // Detect changes in API-settings and if so, dump and clean the cache
        $detect_values  = ['host', 'basedir', 'api_user', 'api_password'];
        $changeDetected = false;

        foreach ($detect_values as $d) {
            if (isset($post[$d]) && isset($config[$d]) && $post[$d] != $config[$d]) {
                $changeDetected = true;
            }
        }

        // Clean the cache if changes are detected
        if ($changeDetected) {
            /** @var JCache $cache */
            $cache = JFactory::getCache('com_magebridge.admin');
            $cache->clean();
        }

        // Store the values row-by-row
        foreach ($config as $name => $data) {
            if (!isset($data['name']) || empty($data['name'])) {
                continue;
            }

            $table = JTable::getInstance('config', 'MagebridgeTable');

            if (!$table->bind($data)) {
                throw new Exception('Unable to bind configuration to component');
            }

            if (!$table->store()) {
                throw new Exception('Unable to store configuration to component');
            }
        }

        return true;
    }

    /**
     * Method to store a single value in the database
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool
     * @throws Exception
     */
    public function saveValue($name, $value)
    {
        $data = [
            'name'  => $name,
            'value' => $value,
        ];

        $config = MageBridgeModelConfig::load();

        if (isset($config[$name])) {
            $data['id'] = $config[$name]['id'];
        }

        $table = JTable::getInstance('config', 'MagebridgeTable');

        if ($table === false) {
            throw new Exception('No table found');
        }

        if (!$table->bind($data)) {
            throw new Exception('Unable to bind configuration to component');
        }

        if (!$table->store()) {
            throw new Exception('Unable to store configuration to component');
        }

        return true;
    }
}
