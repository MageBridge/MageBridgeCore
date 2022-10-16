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
 * MageBridge JSON-RPC Controller
 * Example: index.php?option=com_magebridge&view=jsonrpc&task=call
 *
 * @package MageBridge
 */
class MageBridgeControllerJsonrpc extends YireoAbstractController
{
    /**
     * @var object Zend_Json_Server
     */
    private $server = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        MageBridgeModelDebug::getDebugOrigin(MageBridgeModelDebug::MAGEBRIDGE_DEBUG_ORIGIN_JOOMLA_JSONRPC);
        $this->debug = MageBridgeModelDebug::getInstance();
        $this->app = JFactory::getApplication();
    }

    /**
     * Method to make a JSON-RPC call
     */
    public function call()
    {
        // Manually configure PHP settings
        ini_set('display_errors', 1);

        // Initialize the JSON-RPC server
        $this->init();

        /** @var Zend_Json_Server_Request $request */
        $request = $this->server->getRequest();

        // Fetch the parameters for authentication
        $params = $request->getParams();

        if (!isset($params['api_auth'])) {
            $this->debug->error('JSON-RPC Call: No authentication data');
            return $this->error('No authentication data', 403);
        }

        // Authenticate the API-credentials
        if ($this->authenticate($params['api_auth']) == false) {
            $this->debug->error('JSON-RPC Call: Authentication failed');
            return $this->error('Authentication failed', 401);
        }

        // Remove the API-credentials from the parameters
        unset($params['api_auth']);
        $params = ['params' => $params];

        $request->setParams($params);

        // Make the actual call
        $this->server->handle($this->server->getRequest());

        return $this->close();
    }

    /**
     * Method to display a listing of all API-methods
     */
    public function servicemap()
    {
        $this->init();
        $smd = $this->server->getServiceMap();

        header('Content-Type: application/json');
        echo $smd;

        return $this->close();
    }

    /**
     * Helper method to get the JSON-RPC server object
     *
     * @param null
     *
     * @return null
     */
    private function init()
    {
        // Include the MageBridge API
        $library = JPATH_SITE . '/components/com_magebridge/libraries';
        require_once $library . '/api.php';

        // Set the include_path to include the Zend Framework
        if (!defined('ZEND_PATH')) {
            set_include_path($library . PATH_SEPARATOR . get_include_path());
        } else {
            set_include_path(ZEND_PATH . PATH_SEPARATOR . get_include_path());
        }

        // Include the Zend Framework classes
        require_once 'Zend/Json/Server.php';
        require_once 'Zend/Json/Server/Error.php';

        $this->server = new Zend_Json_Server();
        $this->server->setClass('MageBridgeApi');
    }

    /**
     * Helper method to close this call
     *
     * @param null
     *
     * @return null
     */
    private function close()
    {
        $this->app->close();
    }

    /**
     * Helper method to authenticate this API call
     *
     * @param string $message
     * @param int $code
     *
     * @return null
     */
    private function error($message, $code = 500)
    {
        // Create a new error-object
        $error = new Zend_Json_Server_Error();
        $error->setCode($code);
        $error->setMessage($message);

        /** @var Zend_Json_Server_Response $response */
        $response = $this->server->getResponse();

        // Add the error to the current response
        $response->setError($error);

        // Set the response
        $this->server->setResponse($response);
        $this->server->handle();

        // Set the HTTP-header
        header('HTTP/1.1 ' . $code . ' ' . $message, true);
        header('Status: ' . $code . ' ' . $message, true);

        // Close the application
        $this->close();
    }

    /**
     * Helper method to authenticate this API call
     *
     * @param array $auth
     *
     * @return bool
     */
    private function authenticate($auth)
    {
        if (!empty($auth) && !empty($auth['api_user']) && !empty($auth['api_key'])) {
            $apiUser = MageBridgeEncryptionHelper::decrypt($auth['api_user']);
            $apiKey = MageBridgeEncryptionHelper::decrypt($auth['api_key']);

            if ($apiUser != MageBridgeModelConfig::load('api_user')) {
                $this->debug->error('JSON-RPC: API-authentication failed: Username "' . $apiUser . '" did not match');
            } else {
                if ($apiKey != MageBridgeModelConfig::load('api_key')) {
                    $this->debug->error('JSON-RPC: API-authentication failed: Key "' . $apiKey . '" did not match');
                } else {
                    return true;
                }
            }
        }

        return false;
    }
}
