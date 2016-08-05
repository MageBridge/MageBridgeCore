/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @link https://www.yireo.com
 */

/*
 * A simple replication of some MooTools calls to render MooTools-based scripts harmless
 */

// Generic MooTools replacement
window.addEvent = function() {};

// Stuff for rokutils.js
var InputsExclusion = ['.foo'];

// Class-spoofing
function Class() {};
