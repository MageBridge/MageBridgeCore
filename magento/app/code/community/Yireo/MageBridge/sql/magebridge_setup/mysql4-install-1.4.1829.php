<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$installer->getTable('magebridge_customer_joomla')}` (
    `customer_id` int(10) UNSIGNED DEFAULT '0' NOT NULL,
    `joomla_id` int(10) UNSIGNED DEFAULT '0' NOT NULL,
    `website_id` int(10) UNSIGNED DEFAULT '0' NOT NULL,
    PRIMARY KEY  (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Relation between Joomla user and Magento customer';
");
$installer->endSetup();
