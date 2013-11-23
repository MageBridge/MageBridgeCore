<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Joomla! 1.6 fields conversion (Joomla! 1.6 -> Magento)
$conversion = array(
    'profile.address1' => 'address_street',
    'profile.postal_code' => 'address_postcode',
    'profile.city' => 'address_city',
    'profile.region' => 'address_region',
    'profile.country' => 'address_country',
    'profile.phone' => 'address_telephone',
);

