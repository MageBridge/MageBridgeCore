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
 * Bridge caching class
 */
class MageBridgeModelCache
{
    /**
     * Default cache directory
     */
    protected $cache_folder = null;

    /**
     * Default caching time
     */
    protected $cache_time = 300;

    /**
     * Caching name to be used
     */
    protected $cache_name = null;

    /**
     * Caching file to be used
     */
    protected $cache_file = null;

    /**
     * List of pages that should never be cached
     */
    private $deny_pages = [
        '^checkout',
        '^customer',
        '^persistent',
        '^wishlist',
        '^contacts',
        '^paypal',
    ];

    /**
     * Constructor
     *
     * @access public
     * @param $name string
     * @param $request string
     * @param @cache_time int
     * @return null
     */
    public function __construct($name = '', $request = null, $cache_time = null)
    {
        $this->request = (!empty($request)) ? $request : MageBridgeUrlHelper::getRequest();
        $this->cache_name = $name.'_'.md5($this->request);
        $this->cache_folder = JPATH_SITE.'/cache/com_magebridge';
        $this->cache_file = $this->cache_folder.'/'.$this->cache_name.'.php';
        $this->cache_time = (!empty($cache_time)) ? (int)$cache_time : MageBridgeModelConfig::load('cache_time');
    }

    /**
     * Method to validate whether the cache is allowed
     *
     * @param null
     * @return bool
     */
    public function validate()
    {
        // Check whether caching is enabled
        if ($this->isEnabled() == 0) {
            return false;
        }

        // Try to create the cache directory when needed
        if (!is_dir($this->cache_folder)) {
            jimport('joomla.filesystem.folder');
            $rt = Joomla\Filesystem\Folder::create($this->cache_folder);
            if ($rt == false) {
                return false;
            }
        }

        // Do not allow caching on certain pages
        foreach ($this->deny_pages as $deny) {
            $deny = str_replace('/', '\/', $deny);
            if (preg_match('/'.$deny.'/', $this->request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to return the cached data
     *
     * @param null
     * @return mixed
     */
    public function load()
    {
        // Validate the cache at first
        if ($this->validate() == false) {
            return null;
        }

        // Check whether the caching file is there
        if (!file_exists($this->cache_file)) {
            return null;
        }

        // Check whether the caching file is not yet expired
        if (filemtime($this->cache_file) < (time() - $this->cache_time)) {
            return null;
        }

        $data = file_get_contents($this->cache_file);

        return $data;
    }

    /**
     * Method to store the data to cache
     *
     * @param mixed $data
     * @return bool
     */
    public function store($data)
    {
        if ($this->validate() == false) {
            return false;
        }

        if (!is_writable(dirname($this->cache_file))) {
            return false;
        }

        file_put_contents($this->cache_file, $data);

        return true;
    }

    /**
     * Method to empty the cache
     *
     * @param null
     * @return mixed
     */
    public function flush()
    {
        // If the cache file is there, remove it
        if (file_exists($this->cache_file)) {
            jimport('joomla.filesystem.file');
            return JFile::delete($this->cache_file);
        }
        return false;
    }

    /**
     * Method to check whether caching is allowed
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)MageBridgeModelConfig::load('enable_cache');
    }
}
