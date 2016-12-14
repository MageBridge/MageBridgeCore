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

/**
 * Bridge configuration class
 */
class MagebridgeModelConfig extends YireoAbstractModel
{
	/**
	 * Array of configured data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Array of default values
	 *
	 * @var array
	 */
	protected $_defaults = null;

	/**
	 * Constructor
	 *
	 * @param null
	 *
	 * @retun array
	 */
	public function __construct()
	{
		$this->_defaults = (new MagebridgeModelConfigDefaults)->getDefaults();

		parent::__construct();
	}

	/**
	 * Method to fetch the data
	 *
	 * @return MageBridgeModelConfig
	 */
	static public function getSingleton()
	{
		static $instance;

		if ($instance === null)
		{
			$instance = new MageBridgeModelConfig();
		}

		return $instance;
	}

	/**
	 * Method to set data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function setData($data)
	{
		$this->_data = $data;
	}

	/**
	 * Method to get data
	 *
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_db->getQuery(true);
			$query->select($this->_db->quoteName(['id', 'name']));
			$query->from($this->_db->quoteName('#__magebridge_config', 'c'));

			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObjectList();
		}

		return $this->_data;
	}

	/**
	 * Method to get the defaults
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		return $this->_defaults;
	}

	/**
	 * Static method to get data
	 *
	 * @param string $element
	 * @param
	 *
	 * @return mixed
	 */
	static public function load($element = null, $overload = null)
	{
		$application = JFactory::getApplication();

		static $config = null;
		if (empty($config))
		{

			// Parse the defaults
			$config = array();
			$model  = self::getSingleton();
			foreach ($model->getDefaults() as $name => $value)
			{
				$config[$name] = array(
					'id'          => null,
					'name'        => $name,
					'value'       => $value,
					'core'        => 1,
					'description' => null,
				);

				if ($application->isAdmin())
				{
					$config[$name]['description'] = JText::_(strtoupper($name) . '_DESCRIPTION');
				}
			}

			// Fetch the current data
			$data = $model->getData();

			// Parse the current data into the config
			foreach ($config as $name => $c)
			{
				if (!empty($data))
				{
					foreach ($data as $d)
					{
						if ($d->name == $c['name'])
						{
							$core          = ($config[$name]['value'] == $d->value) ? 1 : 0;
							$config[$name] = array(
								'id'          => $d->id,
								'name'        => $d->name,
								'value'       => $d->value,
								'core'        => $core,
								'description' => $c['description'],
							);
							break;
						}
					}
				}
			}
		}

		// Override certain values
		$config['method']['value'] = 'post';

		// Determine the right update format
		if ($config['update_format']['value'] == '')
		{

			jimport('joomla.application.component.helper');
			$component = JComponentHelper::getComponent('com_magebridge');
			$params = YireoHelper::toRegistry($component->params);

			$value                            = $params->get('update_format', 'tar.gz');
			$config['update_format']['value'] = $value;
		}

		// Disable widgets if needed
		if (JFactory::getApplication()->input->getInt('widgets', 1) == 0)
		{
			$config['api_widgets']['value'] = 0;
		}

		// Overload a certain values when the Magento Admin Panel needs to be loaded
		if ($application->isAdmin() && $application->input->getCmd('option') == 'com_magebridge' && $application->input->getCmd('view') == 'root')
		{
			//$config['debug']['value'] = 0;
			$config['disable_js_all']['value']      = 1;
			$config['disable_js_mootools']['value'] = 1;
		}

		// Allow overriding values
		if (!empty($element) && isset($config[$element]) && $overload !== null)
		{
			$config[$element]['value'] = $overload;
		}

		// Return the URL
		if ($element == 'url')
		{
			$url = null;
			if (!empty($config['host']['value']))
			{
				$url = $config['protocol']['value'] . '://' . $config['host']['value'] . '/';
				if (!empty($config['basedir']['value']))
				{
					$url .= $config['basedir']['value'] . '/';
				}
			}

			return $url;

			// Return the port-number
		}
		else
		{
			if ($element == 'port')
			{
				return ($config['protocol']['value'] == 'http') ? 80 : 443;

				// Return any other element
			}
			else
			{
				if ($element != null && isset($config[$element]))
				{
					return $config[$element]['value'];

					// Return no value
				}
				else
				{
					if (!empty($element))
					{
						return null;

						// Return the configuration itself
					}
					else
					{
						return $config;
					}
				}
			}
		}
	}

	/**
	 * Method to check a specific configuratione-element
	 *
	 * @param string $element
	 * @param string $value
	 *
	 * @return string|null
	 */
	static public function check($element, $value = null)
	{
		// Reset an empty value to its original value
		if (empty($value))
		{
			$value = MagebridgeModelConfig::load($element);
		}

		// Check for settings that should not be kept empty
		$nonempty = array('host', 'website', 'api_user', 'api_key');
		if (MagebridgeModelConfig::allEmpty() == false && in_array($element, $nonempty) && empty($value))
		{
			return JText::sprintf('Setting "%s" is empty - Please configure it below', JText::_($element));
		}

		// Check host
		if ($element == 'host')
		{
			if (preg_match('/([^a-zA-Z0-9\.\-\_\:]+)/', $value) == true)
			{
				return JText::_('Hostname contains illegal characters. Note that a hostname is not an URL, but only a fully qualified domainname.');

			}
			else
			{
				if (gethostbyname($value) == $value && !preg_match('/([0-9\.]+)/', $value))
				{
					return JText::sprintf('DNS lookup of hostname %s failed', $value);

				}
				else
				{
					if (MagebridgeModelConfig::load('api_widgets') == true)
					{

						$bridge = MageBridgeModelBridge::getInstance();
						$data   = $bridge->build();
						if (empty($data))
						{
							$url = $bridge->getMagentoBridgeUrl();

							return JText::sprintf('Unable to open a connection to <a href="%s" target="_new">%s</a>', $url, $url);
						}
					}
				}
			}
		}

		// Check supportkey
		if ($element == 'supportkey' && empty($value))
		{
			return JText::sprintf('Please configure your support-key. Your support-key can be obtained from %s', MageBridgeHelper::getHelpText('subscriptions'));
		}

		// Check API widgets
		if ($element == 'api_widgets' && $value != 1)
		{
			return JText::_('API widgets are disabled');
		}

		// Check offline
		if ($element == 'offline' && $value == 1)
		{
			return JText::_('Bridge is disabled through settings');
		}

		// Check website
		if ($element == 'website' && !empty($value))
		{
			if (is_numeric($value) == false)
			{
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
		if ($element == 'basedir')
		{
			if (empty($value))
			{
				return null;
			}

			if (preg_match('/([a-zA-Z0-9\.\-\_]+)/', $value) == false)
			{
				return JText::_('Basedir contains illegal characters');
			}

			$root         = MageBridgeUrlHelper::getRootItem();
			$joomla_host  = JFactory::getURI()
				->toString(array('host'));
			$magento_host = MagebridgeModelConfig::load('host');

			// Check whether the Magento basedir conflicts with the MageBridge alias
			if (!empty($root) && !empty($root->route) && $root->route == $value && $joomla_host == $magento_host)
			{
				return JText::_('Magento basedir is same as MageBridge alias, which is not possible');
			}
		}

		return null;
	}

	/**
	 * Helper method to detect whether the whole configuration is empty
	 *
	 * @param null
	 *
	 * @return bool
	 */
	static public function allEmpty()
	{
		static $_allempty = null;
		if (empty($_allempty))
		{
			$_allempty = true;
			$config    = MagebridgeModelConfig::load();
			foreach ($config as $c)
			{
				if ($c['core'] == 0)
				{
					$_allempty = false;
					break;
				}
			}
		}

		return $_allempty;
	}

	/**
	 * Method to store the configuration in the database
	 *
	 * @param array $post
	 *
	 * @return bool
	 */
	public function store($post)
	{
		// If the custom list is empty, set another value
		if (isset($post['disable_js_custom']) && isset($post['disable_js_all']))
		{
			if ($post['disable_js_all'] == 2 && empty($post['disable_js_custom']))
			{
				$post['disable_js_all'] = 0;
			}

			if ($post['disable_js_all'] == 3 && empty($post['disable_js_custom']))
			{
				$post['disable_js_all'] = 1;
			}
		}

		// Convert "disable_css_mage" array into comma-seperated string
		if (isset($post['disable_css_mage']) && is_array($post['disable_css_mage']))
		{
			if (empty($post['disable_css_mage'][0]))
			{
				array_shift($post['disable_css_mage']);
			}

			if (empty($post['disable_css_mage']))
			{
				$post['disable_css_mage'] = '';
			}
			else
			{
				$post['disable_css_mage'] = implode(',', $post['disable_css_mage']);
			}
		}

		// Convert "disable_js_mage" array into comma-seperated string
		if (isset($post['disable_js_mage']) && is_array($post['disable_js_mage']))
		{
			if (empty($post['disable_js_mage'][0]))
			{
				array_shift($post['disable_js_mage']);
			}

			if (empty($post['disable_js_mage']))
			{
				$post['disable_js_mage'] = '';
			}
			else
			{
				$post['disable_js_mage'] = implode(',', $post['disable_js_mage']);
			}
		}

		// Clean the basedir
		if (!empty($post['basedir']))
		{
			$post['basedir'] = preg_replace('/^\//', '', $post['basedir']);
			$post['basedir'] = preg_replace('/\/$/', '', $post['basedir']);
		}

		// Check whether the URL-table contains entries
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__magebridge_urls'));
		$query->where($db->quoteName('published') . ' = 1');
		$rows = $db->loadObjectList();
		
		if (!empty($rows))
		{
			$post['load_urls'] = 1;
		}
		else
		{
			$post['load_urls'] = 0;
		}

		// Check whether the stores-table contains entries
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__magebridge_stores'));
		$query->where($db->quoteName('published') . ' = 1');
		$rows = $db->loadObjectList();
		
		if (!empty($rows))
		{
			$post['load_stores'] = 1;
		}
		else
		{
			$post['load_stores'] = 0;
		}

		// Load the existing configuration
		$config = MagebridgeModelConfig::load();

		// Overload each existing value with the posted value (if it exists)
		foreach ($config as $name => $c)
		{
			if (isset($post[$name]) && isset($config[$name]))
			{
				$config[$name]['value'] = $post[$name];
			}
		}

		// Detect changes in API-settings and if so, dump and clean the cache
		$detect_values = array('host', 'basedir', 'api_user', 'api_password');
		$detect_change = false;
		
		foreach ($detect_values as $d)
		{
			if (isset($post[$d]) && isset($config[$d]) && $post[$d] != $config[$d])
			{
				$detect_change = true;
			}
		}

		// Clean the cache if changes are detected
		if ($detect_change)
		{
			/** @var JCache $cache */
			$cache = JFactory::getCache('com_magebridge.admin');
			$cache->clean();
		}

		// Store the values row-by-row
		foreach ($config as $name => $data)
		{

			if (!isset($data['name']) || empty($data['name']))
			{
				continue;
			}

    		$table = JTable::getInstance('config', 'MagebridgeTable');

			if (!$table->bind($data))
			{
				throw new Exception('Unable to bind configuration to component');
			}

			if (!$table->store())
			{
				throw new Exception('Unable to store configuration to component');
			}
		}

		return true;
	}

	/**
	 * Method to store a single value in the database
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveValue($name, $value)
	{
		$data = array(
			'name'  => $name,
			'value' => $value,
		);

		$config = MagebridgeModelConfig::load();

		if (isset($config[$name]))
		{
			$data['id'] = $config[$name]['id'];
		}

		$table = JTable::getInstance('config', 'MagebridgeTable');

		if ($table === false)
		{
			throw new Exception('No table found');
		}

		if (!$table->bind($data))
		{
			throw new Exception('Unable to bind configuration to component');
		}

		if (!$table->store())
		{
			throw new Exception('Unable to store configuration to component');
		}

		return true;
	}
}

