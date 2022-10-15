<?php
// Namespace
namespace Yireo\Common\System;

/**
 * Class Autoloader
 *
 * @package Yireo\System
 */
class Autoloader
{
    public function __construct()
    {
        self::$paths[] = dirname(__DIR__) . '/';
    }

    static public $paths = [];

    static public function init()
    {
        spl_autoload_register(array(new self, 'load'));
    }

    static public function addPath($path)
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
        $baseDir = dirname(__DIR__) . '/';
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0) {
            return false;
        }

        $relativeClass = substr($className, $len);

        $filename = str_replace('\\', '/', $relativeClass) . '.php';

        foreach (self::$paths as $path) {
            if (file_exists($path . '/' . $filename)) {
                include_once $path . '/' . $filename;

                return true;
            }
        }

        return false;
    }
}
