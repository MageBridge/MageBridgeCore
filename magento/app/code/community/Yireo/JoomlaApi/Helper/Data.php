<?php
/**
 * JoomlaApi
 *
 * @author Yireo
 * @package JoomlaApi
 * @copyright Copyright 2015
 * @license Open Source License v3
 * @link http://www.yireo.com
 */

class Yireo_JoomlaApi_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Helper-method to return the Joomla! path
     *
     * @access public
     * @param null
     * @return bool
     */
    public function getJoomlaPath()
    {
        return Mage::getStoreConfig('joomlaapi/settings/path');
    }
}
