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

// JomSocial fields conversion (JomSocial -> Magento)
$conversion = array(
    'FIELD_EMAIL' => 'email',

    'FIELD_NAME' => 'name',
    'FIELD_PREFIX' => 'prefix',
    'FIELD_FIRSTNAME' => 'firstname',
    'FIELD_MIDDLENAME' => 'middlename',
    'FIELD_LASTNAME' => 'lastname',
    'FIELD_SUFFIX' => 'suffix',

    'FIELD_ADDRESS_PREFIX' => 'address_prefix',
    'FIELD_ADDRESS_FIRSTNAME' => 'address_firstname',
    'FIELD_ADDRESS_MIDDLENAME' => 'address_middlename',
    'FIELD_ADDRESS_LASTNAME' => 'address_lastname',
    'FIELD_ADDRESS_SUFFIX' => 'address_suffix',
    'FIELD_ADDRESS_COMPANY' => 'address_company',
    'FIELD_ADDRESS_STREET' => 'address_street',
    'FIELD_ADDRESS_POSTCODE' => 'address_postcode',
    'FIELD_ADDRESS_CITY' => 'address_city',
    'FIELD_ADDRESS_STATE' => 'address_region',
    'FIELD_ADDRESS_COUNTRY' => 'address_country',
    'FIELD_ADDRESS_COUNTRY_ID' => 'address_country_id',
    'FIELD_ADDRESS_TELEPHONE' => 'address_telephone',
    'FIELD_ADDRESS_FAX' => 'address_fax',

    'FIELD_TAXVAT' => 'taxvat',
    'FIELD_NICKNAME' => 'nickname',
    'FIELD_SHORTPROFILE' => 'shortprofile',
);

// JomSocial MageBridge tab (url, systemname, title)
$tab = array();
$tab['name'] = 'Shop';
$tab['url'] = 'customer/account';
$tab['children'] = array(
    array( 'customer/account', 'ACCOUNT', 'My Account' ),
    array( 'customer/address', 'ADDRESSES', 'My Addresses' ),
    array( 'sales/order/history', 'ORDERS', 'Order History' ),
    array( 'checkout/cart', 'CART', 'My Cart' ),
    array( 'wishlist', 'WISHLIST', 'My Wishlist' ),
    array( 'downloadable/customer/products', 'DOWNLOADS', 'Downloadable Products' ),
);
