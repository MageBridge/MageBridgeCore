<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

class Yireo_MageBridge_Helper_Test extends Mage_Core_Helper_Abstract
{
    /*
     * Return whether the current page is seen as an internal page or not
     *
     * @access public
     * @param null
     * @return bool
     */
    public function isInternalPage()
    {
        $url = Mage::getSingleton('magebridge/core')->getMetaData('joomla_current_url');
        if (strpos($url, 'http') !== false) {
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;

    }
}
