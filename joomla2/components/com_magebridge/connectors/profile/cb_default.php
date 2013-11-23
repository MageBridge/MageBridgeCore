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

// Community Builder fields conversion (CB -> Magento)
$conversion = array(
    'email' => 'email',
    'name' => 'name',
    'cb_prefix' => 'prefix',
    'firstname' => 'firstname',
    'middlename' => 'middlename',
    'lastname' => 'lastname',
    'cb_suffix' => 'suffix',

    'cb_address_prefix' => 'address_prefix',
    'cb_address_firstname' => 'address_firstname',
    'cb_adress_middlename' => 'address_middlename',
    'cb_address_lastname' => 'address_lastname',
    'company' => 'address_company',
    'address' => 'address_street',
    'zipcode' => 'address_postcode',
    'city' => 'address_city',
    'state' => 'address_region',
    'country' => 'address_country',
    'cb_country_id' => 'address_country_id',
    'phone' => 'address_telephone',
    'fax' => 'address_fax',

    'cb_taxvat' => 'taxvat',
    'cb_nickname' => 'nickname',
    'cb_description' => 'shortprofile',
);

