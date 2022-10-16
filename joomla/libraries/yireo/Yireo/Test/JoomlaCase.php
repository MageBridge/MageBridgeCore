<?php
/**
 * PHPUnit parent class for Joomla tests
 *
 * @author     Jisse Reitsma <jisse@yireo.com>
 * @copyright  Copyright 2017 Jisse Reitsma
 * @license    GNU Public License version 3 or later
 * @link       https://www.yireo.com/
 */

namespace Yireo\Test;

use PHPUnit\Framework\TestCase as ParentTestCase;
use JError;
use JFactory;
use JApplicationCms;
use JDatabaseDriver;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class JoomlaCase
 */
class JoomlaCase extends ParentTestCase
{
    /**
     * @var string
     */
    protected $targetClassName;

    /**
     * @var JApplicationCms
     */
    protected $app;

    /**
     * @var JDatabaseDriver
     */
    protected $db;

    /**
     * Method to initialize Joomla application
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Hack to skip PHP notices
        $_SERVER['HTTP_HOST']      = 'localhost';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '';

        ini_set('display_errors', 1);
        ini_set('magic_quotes_runtime', 0);
        error_reporting(E_ALL & ~E_STRICT);

        // Neccessary definitions
        if (!defined('DOCUMENT_ROOT')) {
            define('DOCUMENT_ROOT', dirname(exec('pwd')) . '/');
        }

        if (!defined('_JEXEC')) {
            define('_JEXEC', 1);
        }

        if (!defined('JPATH_PLATFORM')) {
            define('JPATH_PLATFORM', realpath(DOCUMENT_ROOT . '/libraries'));
        }

        if (!defined('JPATH_LIBRARIES')) {
            define('JPATH_LIBRARIES', realpath(DOCUMENT_ROOT . '/libraries'));
        }

        if (!defined('JPATH_BASE')) {
            define('JPATH_BASE', DOCUMENT_ROOT);
        }

        if (!defined('JPATH_ROOT')) {
            define('JPATH_ROOT', realpath(JPATH_BASE));
        }

        if (!defined('JPATH_CACHE')) {
            define('JPATH_CACHE', JPATH_BASE . '/cache');
        }

        if (!defined('JPATH_CONFIGURATION')) {
            define('JPATH_CONFIGURATION', JPATH_BASE);
        }

        if (!defined('JPATH_SITE')) {
            define('JPATH_SITE', JPATH_ROOT);
        }

        if (!defined('JPATH_ADMINISTRATOR')) {
            define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
        }

        if (!defined('JPATH_INSTALLATION')) {
            define('JPATH_INSTALLATION', JPATH_ROOT . '/installation');
        }

        if (!defined('JPATH_MANIFESTS')) {
            define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
        }

        if (!defined('JPATH_PLUGINS')) {
            define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
        }

        if (!defined('JPATH_THEMES')) {
            define('JPATH_THEMES', JPATH_BASE . '/templates');
        }

        if (!defined('JDEBUG')) {
            define('JDEBUG', false);
        }

        chdir(JPATH_BASE);

        // Include the framework
        require_once JPATH_PLATFORM . '/import.legacy.php';

        JError::setErrorHandling(E_NOTICE, 'message');
        JError::setErrorHandling(E_WARNING, 'message');

        require_once JPATH_LIBRARIES . '/cms.php';

        $this->app = JFactory::getApplication('site');
        $this->app->initialise();
        $this->db = JFactory::getDbo();
    }

    /**
     * @return mixed
     */
    protected function getTargetClassName()
    {
        if (!empty($this->targetClassName)) {
            return $this->targetClassName;
        }

        return preg_replace('/Test$/', '', get_class($this));
    }

    /**
     * @param string $methodName
     *
     * @return ReflectionMethod
     */
    protected static function getClassMethod($methodName)
    {
        $thisObject = new self();

        $class  = new ReflectionClass($thisObject->getTargetClassName());
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param object $object
     * @param string $methodName
     *
     * @return ReflectionMethod
     */
    protected static function getObjectMethod($object, $methodName)
    {
        $class  = new ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
