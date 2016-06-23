<?php
/*
 * Joomla! Yireo Library
 *
 * @author Yireo (info@yireo.com)
 * @package YireoLib
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 * @version 0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import the loader
require_once dirname(__FILE__).'/loader.php';

/**
 * Yireo Helper
 */
class YireoHelper
{
    /*
     * Helper-method to get the Joomla! DBO
     *
     * @param null
     * @return bool
     */
    static public function getDBO()
    {
        return JFactory::getDbo();
    }


    /*
     * Helper-method to parse the data defined in this component
     *
     * @param null
     * @return bool
     */
    static public function getData($name = null, $option = null)
    {
        if(empty($option)) $option = JFactory::getApplication()->input->getCmd('option');
        $file = JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/abstract.php';
        if (is_file($file)) {
            require_once $file ;
            $class = 'HelperAbstract';
            if (class_exists($class)) {
                $object = new $class;
                $data = $object->getStructure();
                if (isset($data[$name])) {
                    return $data[$name];
                }
            }
        }
        return null;
    }

    /*
     * Helper-method to return the HTML-ending of a form
     *
     * @param null
     * @return bool
     */
    static public function getFormEnd($id = 0)
    {
        echo '<input type="hidden" name="option" value="'.JFactory::getApplication()->input->getCmd('option').'" />';
        echo '<input type="hidden" name="cid[]" value="'.$id.'" />';
        echo '<input type="hidden" name="task" value="" />';
        echo JHtml::_( 'form.token' );
    }

    /*
     * Helper-method to return the current Joomla version
     *
     * @return bool
     */
    static public function getJoomlaVersion()
    {
        JLoader::import( 'joomla.version' );
        $jversion = new JVersion();
        return $jversion->RELEASE;
    }

    /*
     * Helper-method to check whether the current Joomla! version equals some value
     *
     * @param $version string|array
     * @return bool
     */
    static public function isJoomla($version)
    {
        $jversion = self::getJoomlaVersion();
        if (!is_array($version)) $version = array($version);
        foreach($version as $v) {
            if (version_compare( $jversion, $v, 'eq')) {
                return true;
            }
        }
        return false;
    }

    /*
     * Helper-method to check whether the current Joomla! version is 3.5
     *
     * @param null
     * @return bool
     */
    static public function isJoomla35()
    {
        return self::isJoomla(array('3.0', '3.1', '3.2', '3.5'));
    }

    /*
     * Helper-method to check whether the current Joomla! version is 2.5
     *
     * @param null
     * @return bool
     */
    static public function isJoomla25()
    {
        if(self::isJoomla('2.5') || self::isJoomla('1.7') || self::isJoomla('1.6')) {
            return true;
        }
        return false;
    }

    
    /*
     * Helper-method to check whether the current Joomla! version is 1.5
     *
     * @param null
     * @return bool
     */
    static public function isJoomla15()
    {
        return self::isJoomla('1.5');
    }

    /*
     * Helper-method to check whether the current Joomla! version is 1.5
     *
     * @param null
     * @return bool
     */
    static public function compareJoomlaVersion($version, $comparison)
    {
        $jversion = self::getJoomlaVersion();
        return version_compare( $jversion, $version, $comparison);
    }

    /**
     * Method to get the current version 
     *
     * @access public
     * @param null
     * @return string
     */
    static public function getCurrentVersion()
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $name = preg_replace('/^com_/', '', $option);

        $file = JPATH_ADMINISTRATOR.'/components/'.$option.'/'.$name.'.xml';

        if(class_exists('JInstaller') && method_exists('JInstaller', 'parseXMLInstallFile')) {
            $data = JInstaller::parseXMLInstallFile($file);
            return $data['version'];
        } elseif(method_exists('JApplicationHelper', 'parseXMLInstallFile')) {
            $data = JApplicationHelper::parseXMLInstallFile($file);
            return $data['version'];
        }
        return null;
    }

    /**
     * Method to fetch a specific page
     *
     * @access public
     * @param string $url
     * @param string $useragent
     * @return bool
     */
    static public function fetchRemote($url, $useragent = null)
    {
        if (function_exists('curl_init') == true) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_MAXCONNECTS, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, (!empty($useragent)) ? $useragent : $_SERVER['HTTP_USER_AGENT']);
            $contents = curl_exec($ch);
        } else {
            $contents = file_get_contents($url);
        }
        return $contents;
    }

    /*
     * Convert an object or string to JParameter or JRegistry
     *
     * @param mixed $params
     * @param string $file
     * @return JParameter|JRegistry
     */
    static public function toRegistry($params = null, $file = null)
    {
        if ($params instanceof JParameter || $params instanceof JRegistry) {
            return $params;
        }

        if (is_string($params)) $params = trim($params);

        jimport('joomla.registry.registry');
        $registry = @new JRegistry();
        if(!empty($params) && is_string($params)) $registry->loadString($params);
        if(!empty($params) && is_array($params)) $registry->loadArray($params);

        if(is_file($file) && is_readable($file)) {
            $fileContents = file_get_contents($file);
        } else {
            $fileContents = null;
        }

        if(preg_match('/\.xml$/', $fileContents)) {
            $registry->loadFile($file, 'XML');
        } elseif(preg_match('/\.json$/', $fileContents)) {
            $registry->loadFile($file, 'JSON');
        }

        $params = $registry;

        return $params;
    }

    /*
     * Deprecated shortcut for self::toRegistry()
     *
     * @param mixed $params
     * @param string $file
     * @return JParameter|JRegistry
     * @deprecated
     */
    static public function toParameter($params = null, $file = null)
    {
        return self::toRegistry($params, $file);
    }

    /*
     * Add in Bootstrap
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    static public function bootstrap()
    {
        if (self::isJoomla25()) {

            // Check if bootstrap is loaded already
            $application = JFactory::getApplication();
            if(method_exists($application, 'set')) $application->set('bootstrap', true);

            $option = JFactory::getApplication()->input->getCmd('option');
            $document = JFactory::getDocument();
            $document->addStyleSheet('//netdna.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css');
            $document->addStyleSheet(JUri::root().'media/'.$option.'/css/backend-bootstrap-j25.css');
            $document->addScript('//netdna.bootstrapcdn.com/bootstrap/2.3.2/js/bootstrap.min.js');

        } else {
          JHtml::_('bootstrap.framework');
        }

        self::jquery();
    }

    /*
     * Method to check whether Bootstrap is used
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return boolean
     */
    static public function hasBootstrap()
    {
        $application = JFactory::getApplication();
        if (method_exists($application, 'get') && $application->get('bootstrap') == true) {
            return true;
        }
        return false;
    }

    /*
     * Add in jQuery
     *
     * @access public
     * @subpackage Yireo
     * @param null
     * @return null
     */
    static public function jquery()
    {
        // Do not load when having no HTML-document
        $document = JFactory::getDocument();
        if(stristr(get_class($document), 'html') == false) {
            return;
        }

        // Load jQuery using the framework (Joomla! 3.0 and higher)
        if(YireoHelper::isJoomla25() == false) {
            return JHtml::_('jquery.framework');
        }

        // Check if jQuery is loaded already
        $application = JFactory::getApplication();
        if (method_exists($application, 'get') && $application->get('jquery') == true) {
            return;
        }

        // Do not load this for specific extensions
        if(JFactory::getApplication()->input->getCmd('option') == 'com_virtuemart') return false;

        // Load jQuery
        $option = JFactory::getApplication()->input->getCmd('option');
        if (file_exists(JPATH_SITE.'/media/'.$option.'/js/jquery.js')) {
            $document->addScript(JUri::root().'media/'.$option.'/js/jquery.js');
            $document->addCustomTag('<script type="text/javascript">jQuery.noConflict();</script>');

            // Set the flag that jQuery has been loaded
            if(method_exists($application, 'set')) $application->set('jquery', true);
        }
    }

    /*
     * Helper-method to load additional language-files
     *
     * @access public
     * @subpackage Yireo
     * @param string $title
     * @return null
     */
    public static function loadLanguageFile()
    {
        $application = JFactory::getApplication();
        $language = JFactory::getLanguage();
        $extension = 'lib_yireo';

        $folder = ($application->isSite()) ? JPATH_SITE : JPATH_ADMINISTRATOR;
        $tag = $language->getTag();
        $reload = true;
        $language->load($extension, $folder, $tag, $reload);
    }

    public static function strlen($string)
    {
        if (function_exists('mb_strlen'))
        {
            return mb_strlen($string);
        }

        return strlen($string);
    }
}
