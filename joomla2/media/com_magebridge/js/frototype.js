/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @link http://www.yireo.com
 */

/*
 * A simple replication of some ProtoType calls to render ProtoType-based calls harmless
 */

// Decoration functions
decorateGeneric = function() {};
decorateList = function() {};
decorateTable = function() {};
decorateDataList = function() {};

// Redirect functions
setLocation = function(url) {window.location = url;};
