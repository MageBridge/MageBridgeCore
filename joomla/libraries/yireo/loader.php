<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   0.6.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Load the Yireo Library loader if possible
if (is_file(JPATH_LIBRARIES . '/yireo/loader.php'))
{
	require_once JPATH_LIBRARIES . '/yireo/loader.php';
}

// Include the original Joomla! loader
require_once JPATH_LIBRARIES . '/loader.php';

// If the Joomla! autoloader exists, add it to SPL
if (function_exists('__autoload'))
{
	spl_autoload_register('__autoload');
}

// Detect our own autoloader
if (!class_exists('\Yireo\System\Autoloader'))
{
	include_once __DIR__ . '/Yireo/System/Autoloader.php';
}

// Add our own loader-function to SPL
spl_autoload_register(array(new \Yireo\System\Autoloader, 'load'));
