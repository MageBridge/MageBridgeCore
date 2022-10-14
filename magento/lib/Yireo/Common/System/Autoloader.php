<?php
/**
 * Yireo Autoloader
 *
 * Usage:
 * Yireo\Common\System\Autoloader::init();
 * Yireo\Common\System\Autoloader::addPath('some/path/Yireo');
 */

// Namespace

namespace Yireo\Common\System;

/**
 * Class Autoloader
 *
 * @package Yireo\System
 */
class Autoloader
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * Autoloader constructor.
     */
    public function __construct($debug = false)
    {
        $this->debug = $debug;
        self::$paths[] = dirname(__DIR__) . '/';
    }

    /**
     * @var array
     */
    public static $paths = [];

    /**
     * Initialize the autoloader
     */
    public static function init($debug = false)
    {
        $self = new self($debug);
        spl_autoload_register([$self, 'load']);
    }

    /**
     * Add a new path to the autoloader
     *
     * @param $path
     */
    public static function addPath($path)
    {
        self::$paths[] = $path;
    }

    /**
     * Main autoloading function
     *
     * @param $className
     *
     * @return bool
     */
    public function load($className)
    {
        if (stristr($className, 'yireo') === false) {
            return false;
        }

        // Try to include namespaced files
        $rt = $this->loadNamespaced($className);

        if ($rt === true) {
            return true;
        }

        return false;
    }

    /**
     * Autoloading function for namespaced classes
     *
     * @param $className
     *
     * @return bool
     */
    protected function loadNamespaced($className)
    {
        $prefix = 'Yireo\\';
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0) {
            return false;
        }

        $relativeClass = substr($className, $len);

        $filename = str_replace('\\', '/', $relativeClass) . '.php';

        foreach (self::$paths as $path) {
            if ($this->debug) {
                echo "Yireo path: $path/$filename\n";
            }

            if (file_exists($path . '/' . $filename)) {
                include_once $path . '/' . $filename;

                return true;
            }
        }

        return false;
    }
}
