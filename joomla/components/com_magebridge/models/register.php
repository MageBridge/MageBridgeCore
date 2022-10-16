<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 *
 * The MageBridge register contains gathered data from Joomla!, and is sent through the bridge
 * to the Magento side, where it is interpreted by the MageBridge-module in Magento and completed
 * where neccessary. The register has the following structure:
 *
 *     $this->data = array(
 *         $segment => array(                   // A simple word or an unique identifier
 *             'type' => $string,               // A type-string (authenticate|urls|block|filter|breadcrumbs|api|event|headers)
 *             'name' => $string,               // A custom name
 *             'arguments' => $mixed,           // Arguments to be given if needed
 *             'data' => $mixed,                // The response-data received from Magento
 *         );
 *     );
 *
 * An example of a block segment
 *    $this->data['23deff890af23bbd98'] = array(
 *        'type' => 'block',
 *        'name' => 'content',
 *        'data' => null,
 *    );
 *
 * An example of a API segment
 *    $this->data['magebridge.user_save'] = array(
 *        'type' => 'api',
 *        'name' => 'magebridge.user_save',
 *        'arguments' => array('user_id' => 1),
 *        'data' => null,
 *    );
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Bridge register
 */
class MageBridgeModelRegister
{
    /**
     * Singleton
     */
    protected static $_instance = null;

    /**
     * Registry array
     */
    private $data = [];

    /**
     * Constants defining the status of a segment
     */
    public const MAGEBRIDGE_SEGMENT_STATUS_SYNCED = 1;

    /**
     * Singleton method
     *
     * @param null
     *
     * @return MageBridgeModelRegister
     */
    public static function getInstance()
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
            self::$_instance->init();
        }

        return self::$_instance;
    }

    /**
     * Method to initialize the registrer
     *
     * @param null
     * $return null
     */
    public function init()
    {
        // Initialize the register
        MageBridgeRegisterHelper::preload();
    }

    /**
     * Method to add a segment to the register
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     * $return string $id
     */
    public function add($type = null, $name = null, $arguments = null)
    {
        // Determine the identifier for this new segment
        if (in_array($type, ['filter', 'block', 'api', 'widget'])) {
            $id = md5($type . $name . serialize($arguments));
        } else {
            $id = $type;
        }

        // Add the new segment to the internal data
        $this->data[$id] = [
            'type' => $type,
            'name' => $name,
            'arguments' => $arguments,];

        return $id;
    }

    /**
     * Method to get a segment value from the register by its ID
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getById($id = null)
    {
        if (!empty($this->data)) {
            foreach ($this->data as $index => $segment) {
                if ($index == $id) {
                    return $segment;
                }
            }
        }

        return false;
    }

    /**
     * Fetch the data from segment by its ID
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getDataById($id = null)
    {
        $segment = $this->getById($id);
        if (isset($segment['data'])) {
            return $segment['data'];
        }
    }

    /**
     * Method to get a segment value from the register
     *
     * @param string $type
     * @param string $name
     *
     * @return mixed
     */
    public function get($type = null, $name = null, $arguments = null, $id = null)
    {
        if (!empty($id)) {
            MageBridgeModelDebug::getInstance()
                ->warning('Use of fourth argument in method MageBridgeModelRegister::get() is deprecated');
        }

        // Determine the identifier for this segment
        if (in_array($type, ['filter', 'block', 'api', 'widget'])) {
            $id = md5($type . $name . serialize($arguments));
        } else {
            $id = null;
        }

        // Loop through the registered data to try to find the right segment
        if (!empty($this->data)) {
            foreach ($this->data as $index => $segment) {
                // Check if the segment matches the given search criteria
                $match = false;

                if (!empty($id) && $index == $id) {
                    $match = true;
                } else {
                    if (empty($id) && isset($segment['type']) && $segment['type'] == $type && isset($segment['name']) && $segment['name'] == $name) {
                        $match = true;
                    } else {
                        if (empty($id) && empty($name) && isset($segment['type']) && $segment['type'] == $type) {
                            $match = true;
                        }
                    }
                }

                // Return the actual segment
                if ($match == true) {
                    return $segment;
                }
            }
        }
    }

    /**
     * Fetch the data from segment
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     * @param string $id
     *
     * @return mixed
     */
    public function getData($type = null, $name = null, $arguments = null, $id = null)
    {
        if (!empty($id)) {
            MageBridgeModelDebug::getInstance()
                ->warning('Use of fourth argument in method MageBridgeModelRegister::getData() is deprecated');
        }

        $segment = $this->get($type, $name, $arguments, $id);
        if (!empty($segment['data'])) {
            return $segment['data'];
        }

        return null;
    }

    /**
     * Deprecated method
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     * @param string $id
     *
     * @return mixed
     * @deprecated Use get() instead
     */
    public function getSegment($type = '', $name = null, $arguments = null, $id = null)
    {
        MageBridgeModelDebug::getInstance()
            ->warning('Method MageBridgeModelRegister::getSegment() is deprecated');

        return $this->get($type, $name, $arguments, $id);
    }

    /**
     * Deprecated method
     *
     * @param string $type
     * @param string $name
     * @param mixed  $arguments
     * @param string $id
     *
     * @return mixed
     * @deprecated Use getData() instead
     */
    public function getSegmentData($type = null, $name = null, $arguments = null, $id = null)
    {
        MageBridgeModelDebug::getInstance()
            ->warning('Method MageBridgeModelRegister::getSegmentData() is deprecated');

        return $this->getData($type, $name, $arguments, $id);
    }

    /**
     * Deprecated method
     *
     * @param string $id
     *
     * @return mixed
     * @deprecated Use getById() instead
     */
    public function getSegmentById($id)
    {
        MageBridgeModelDebug::getInstance()
            ->warning('Method MageBridgeModelRegister::getSegmentById() is deprecated');

        return $this->getById($id);
    }

    /**
     * Method to remove a request from the register
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    public function remove($type, $name)
    {
        if (!empty($this->data)) {
            foreach ($this->data as $i => $r) {
                if ($r['type'] == $type && $r['name'] == $name) {
                    unset($this->data[$i]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Method to clean the register
     *
     * @param null
     *
     * @return $this
     */
    public function clean()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Method to fetch the entire register
     *
     * @param null
     *
     * @return array
     */
    public function getRegister()
    {
        return $this->data;
    }

    /**
     * Method to return the pending register-entries
     *
     * @param null
     *
     * @return array
     */
    public function getPendingRegister()
    {
        $data = [];

        // Collect all the segments that do not have data
        if (!empty($this->data)) {
            foreach ($this->data as $id => $segment) {
                // Do not return segments that already have data
                if (isset($segment['status']) && $segment['status'] == self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED) {
                    continue;
                }

                // Do not return segments that already have data inside them
                if (!empty($segment['data'])) {
                    continue;
                }

                $data[$id] = $segment;
            }
        }

        // If this pending register is not empty, we need to add the register to it
        if (!empty($data) && empty($data['meta'])) {
            $data['meta'] = [
                'type' => 'meta',
                'name' => null,
                'arguments' => MageBridgeModelBridgeMeta::getInstance()
                    ->getRequestData(),];
        }

        // Handle exceptions like with Dynamic404
        // @todo: implement and test this hack-function
        //$data = $this->doCorrectRequest($data);

        return $data;
    }

    /**
     * Method to correct the request URI depending on what is asked
     *
     * @param array $data
     *
     * @return array
     */
    private function doCorrectRequest($data)
    {
        // If the data do not include any visual elements, setting the Request URI is not needed
        $url_needed = false;
        foreach ($data as $index => $segment) {
            if ($index == 'breadcrumbs' || $index == 'headers' || $segment['name'] == 'block') {
                $url_needed = true;
                break;
            }
        }

        // Reset the Request URI if it is not needed
        if ($url_needed == false && isset($data['meta']['arguments']['request_uri'])) {
            // Change the META-data
            $data['meta']['arguments']['request_uri'] = '/';

            // Correct the helper so it returns the new value
            MageBridgeUrlHelper::setRequest('/');

            // Reset the proxy to make sure 404s or other status codes are not preventing us from building the bridge again
            MageBridgeModelProxy::getInstance()
                ->reset();
        }

        return $data;
    }

    /**
     * Method to return the pending register-entries
     *
     * @param array $data
     *
     * @return void
     */
    public function merge($data)
    {
        // If there is no data yet, we are done pretty quickly
        if (empty($this->data)) {
            $this->data = $data;
            return;
        }

        MageBridgeModelDebug::getInstance()
            ->notice('Merging register (' . count($data) . ' segments)');

        // Merge the new data with the previous data
        if (!empty($data)) {
            foreach ($data as $id => $segment) {
                // Set the status to 1
                $segment['status'] = self::MAGEBRIDGE_SEGMENT_STATUS_SYNCED;

                // Insert the segment into the internal data
                $this->data[$id] = $segment;

                // Cache the block result
                if (!empty($segment['data']) && !empty($segment['type'])) {
                    // Determine per segment-type whether caching is available
                    switch ($segment['type']) {
                        case 'block':
                            if (isset($segment['meta']['allow_caching']) && $segment['meta']['allow_caching'] == 1 && isset($segment['meta']['cache_lifetime']) && $segment['meta']['cache_lifetime'] > 0) {
                                $cache = new MageBridgeModelCacheBlock($segment['name']);
                            } else {
                                $cache = null;
                            }
                            break;
                        case 'headers':
                            $cache = new MageBridgeModelCacheHeaders();
                            break;
                        case 'breadcrumbs':
                            $cache = new MageBridgeModelCacheBreadcrumbs();
                            break;
                        default:
                            $cache = null;
                            break;
                    }

                    // If there is a caching object, use it to transfer the data to cache-store
                    if (!empty($cache)) {
                        $cache->store($segment['data']);
                    }
                }
            }
        }
    }

    /**
     * Method to load cache data into the register
     *
     * @param null
     *
     * @return null
     */
    public function loadCache()
    {
        if (!empty($this->data)) {
            foreach ($this->data as $index => $segment) {
                if (isset($segment['type'])) {
                    switch ($segment['type']) {
                        case 'block':
                            $cache = new MageBridgeModelCacheBlock($segment['name']);
                            break;
                        case 'headers':
                            $cache = new MageBridgeModelCacheHeaders();
                            break;
                        case 'breadcrumbs':
                            $cache = new MageBridgeModelCacheBreadcrumbs();
                            break;
                        default:
                            $cache = null;
                            break;
                    }

                    if (!empty($cache)) {
                        if ($cache->validate()) {
                            $segment_data = $cache->load();
                            if (!empty($segment_data)) {
                                $this->data[$index]['data'] = $segment_data;
                                $this->data[$index]['cache'] = 1;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Method to return the register as an array
     *
     * @param null
     *
     * @return string
     */
    public function _toString()
    {
        return var_export($this->data, true);
    }
}
