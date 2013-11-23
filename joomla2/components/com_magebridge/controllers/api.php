<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent controller
jimport( 'joomla.application.component.controller' );

/**
 * @package MageBridge
 */
class MageBridgeControllerApi extends JController
{
    public function run()
    {
        // Parse the POST-request
        $post = JRequest::get('post');
        $data = array();

        foreach ($post as $name => $value) {
            $value = json_decode($value);
            $data[$name] = $value;
        }

        if ($this->authenticate($data) == false) {
            return false;
        }

        if (is_array($data) && !empty($data)) {
            $this->dispatch($data);
        }
    }

    protected function authenticate()
    {
        if (isset($data['meta']['api_user']) && isset($data['meta']['api_key'])) {
            // @todo: Perform authentication of these data
            return true;
        }

        return false;
    } 

    /*
     * Method to dispatch the incoming request to various parts of the bridge
     *
     * @param null
     * @return null
     */
    protected function dispatch($data)
    {
        foreach ($data as $index => $segment) {

            switch($index) {
                case 'authenticate':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;

                case 'event':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;
    
                case 'log':
                    // @todo: $segment['data'] = MageBridgeModelUser->authenticate($segment['arguments']);
                    break;
            }
        }
    }
}
