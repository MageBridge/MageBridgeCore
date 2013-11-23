CREATE TABLE IF NOT EXISTS `#__magebridge_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `value` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_connectors` (
    `id` int(11) NOT NULL auto_increment,
    `title` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `access` tinyint(3) NOT NULL default '0',
    `ordering` int(11) NOT NULL default '0',
    `published` tinyint(3) NOT NULL default '0',
    `iscore` tinyint(3) NOT NULL default '0',
    `checked_out` int(11) NOT NULL default '0',
    `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
    `params` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__magebridge_log`;

CREATE TABLE IF NOT EXISTS `#__magebridge_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `message` TEXT NOT NULL DEFAULT '',
    `type` INT(2) NOT NULL DEFAULT 0 COMMENT 'Type that equals debugging level',
    `origin` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Which application',
    `section` VARCHAR(255) NOT NULL DEFAULT '',
    `remote_addr` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'IP-address as logged by Apache',
    `http_agent` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'HTTP_AGENT as logged by Apache',
    `session` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'API session',
    `timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_products` (
    `id` int(11) NOT NULL auto_increment,
    `label` varchar(255) NOT NULL,
    `sku` varchar(255) NOT NULL,
    `connector` varchar(255) NOT NULL,
    `connector_value` varchar(255) NOT NULL,
    `access` tinyint(3) NOT NULL default '0',
    `ordering` int(11) NOT NULL default '0',
    `published` tinyint(1) NOT NULL,
    `checked_out` int(11) NOT NULL default '0',
    `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
    `params` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_stores` (
    `id` int(11) NOT NULL auto_increment,
    `label` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `connector` varchar(255) NOT NULL,
    `connector_value` varchar(255) NOT NULL,
    `access` tinyint(3) NOT NULL default '0',
    `ordering` int(11) NOT NULL default '0',
    `published` tinyint(1) NOT NULL,
    `checked_out` int(11) NOT NULL default '0',
    `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
    `params` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_urls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `access` tinyint(3) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL,
  `checked_out` int(11) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_products_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `create_date` int(11) NOT NULL DEFAULT '0',
  `expire_date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__magebridge_usergroups` (
    `id` int(11) NOT NULL auto_increment,
    `joomla_group` int(11) NOT NULL default '0',
    `magento_group` int(11) NOT NULL default '0',
    `description` varchar(255) NOT NULL,
    `ordering` int(11) NOT NULL default '0',
    `published` tinyint(3) NOT NULL default '0',
    `checked_out` int(11) NOT NULL default '0',
    `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
    `params` text NOT NULL,
    PRIMARY KEY  (`id`)
) DEFAULT CHARSET=utf8;

