<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Handy variables
$request = MageBridgeUrlHelper::getRequest();
$bridge = MageBridgeModelBridge::getInstance();
$page_layout = MageBridgeTemplateHelper::getRootTemplate();
	
/**
 * Developers note: Do NOT edit the contents of this file directly. 
 * Instead, create a override of this file by copying it to:
 *
 * "templates/YOUR_TEMPLATE/html/com_magebridge/fixes.php
 */

// FIX: Magento refers from opcheckout.js to these specific HTML-classes, but currently we do not care
if (strstr($request, 'checkout/onepage') && $bridge->getBlock('checkout.progress') == '') {
	$html .= '<!-- Begin Checkout Progress Fix -->';
	$html .= '<div class="col-right" style="display:none;">';
	$html .= '<div class="one-page-checkout-progress"></div>';
	$html .= '<div id="checkout-progress-wrapper"></div>';
	$html .= '<div id="col-right-opcheckout"></div>';
	$html .= '</div>';
	$html .= '<!-- End Checkout Progress Fix -->';
}

// FIX: Make sure that when "page/one-column.phtml" is used, we set the Joomla! variable "tmpl=component"
if ($page_layout == 'page/one-column.phtml') {
	JFactory::getApplication()->input->set('tmpl', 'component');
}

// Developers note: Make sure the $html variable still contains your data

// End
