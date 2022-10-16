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

/**
 * Main bridge class
 */
class MageBridgeApi
{
    /**
     * Constructor
     */
    public function __construct()
    {
        MageBridgeModelDebug::getDebugOrigin(MageBridgeModelDebug::MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC);
        $this->debug = MageBridgeModelDebug::getInstance();
        $this->app = JFactory::getApplication();
    }

    /**
     * Test method
     *
     * @return string
     */
    public function test()
    {
        $this->debug->notice('JSON-RPC test');

        return 'OK received from Joomla!';
    }

    /**
     * Login method
     *
     * @param array $params
     *
     * @return false|array
     */
    public function login($params = [])
    {
        $credentials = [
            'username' => $params[0],
            'password' => $params[1],];

        $rt = $this->app->login($credentials);

        if ($rt === true) {
            return ['email' => $params[0]];
        }

        return false;
    }

    /**
     * Event method
     *
     * @param array $params
     *
     * @return bool
     */
    public function event($params = [])
    {
        // Parse the parameters
        $event = (isset($params[0]) && is_string($params[0])) ? $params[0] : null;
        $arguments = (isset($params[1]) && is_array($params[1])) ? $params[1] : [];

        // Check if this call is valid
        if (empty($event)) {
            return false;
        }

        // Start debugging
        $this->debug->trace('JSON-RPC: firing mageEvent ', $event);

        // Initialize the plugin-group "magento"
        JPluginHelper::importPlugin('magento');
        $application = JFactory::getApplication();

        // Trigger the event and return the result
        $result = $application->triggerEvent($event, [$arguments]);

        if (!empty($result[0])) {
            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * Logs a MageBridge message on the Joomla! side
     *
     * @param array $params
     *
     * @return bool
     */
    public function log($params = [])
    {
        // Parse the parameters
        $type = (isset($params['type'])) ? $params['type'] : MAGEBRIDGE_DEBUG_NOTICE;
        $message = (isset($params['message'])) ? $params['message'] : null;
        $section = (isset($params['section'])) ? $params['section'] : null;
        $time = (isset($params['time'])) ? $params['time'] : null;
        $origin = MAGEBRIDGE_DEBUG_ORIGIN_MAGENTO;

        // Log this message
        return (bool) $this->debug->add($type, $message, $section, $origin, $time);
    }

    /**
     * Output modules on a certain position
     *
     * @param array $params
     *
     * @return string
     */
    public function position($params = [])
    {
        if (empty($params) || empty($params[0])) {
            $this->debug->error('JSON-RPC: position-method called without parameters');

            return null;
        }

        $position = $params[0];
        $style = (isset($params[1])) ? $params[1] : null;

        jimport('joomla.application.module.helper');
        $modules = JModuleHelper::getModules($position);

        $outputHtml = null;
        $attributes = ['style' => $style];

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $moduleHtml = JModuleHelper::renderModule($module, $attributes);
                $moduleHtml = preg_replace('/href=\"\/([^\"]{0,})\"/', 'href="' . JUri::root() . '\1"', $moduleHtml);
                $outputHtml .= $moduleHtml;
            }
        }

        return $outputHtml;
    }

    /**
     * Method to get a list of all users
     *
     * @param array $params
     *
     * @return array
     */
    public function getUsers($params = [])
    {
        $rows = $this->loadUsersFromQuery($params['search']);

        foreach ($rows as $index => $row) {
            require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

            $params = YireoHelper::toRegistry($row->params);
            $row->params = $params->toArray();
            $rows[$index] = $row;
        }

        return $rows;
    }

    /**
     * @param null $search
     *
     * @return array
     */
    protected function loadUsersFromQuery($search = null)
    {
        // System variables
        $db = JFactory::getDbo();

        // Construct the query
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__users'));

        if (isset($params['search'])) {
            $query->where($db->quoteName('username') . ' LIKE ' . $db->quote($search));
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList();

        return $rows;
    }
}
