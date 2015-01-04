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

/*
 * MageBridge rewrite of the default core/text_list-block
 */
class Yireo_MageBridge_Block_TextList extends Mage_Core_Block_Text_List
{
    protected function _toHtml()
    {
        $allowed_blocks = array('content');
        if(Mage::getStoreConfig('magebridge/cache/caching_gzip') == 1 && in_array($this->getNameInLayout(), $allowed_blocks)) {
            $cached = $this->_loadCache();
            if(!$cached) {
                $html = parent::_toHtml();
                $html = trim($html);
                $compressed = @base64_encode($html);
                $compressed = @gzcompress($compressed, 6);
                if($compressed) {
                    return $compressed;
                }
            }
            return $html;
        }
        return parent::_toHtml();
    }

    protected function _afterToHtml($html)
    {
        if(Mage::getStoreConfig('magebridge/cache/caching_gzip') == 1) {
            if(!empty($html)) {
                $uncompressed = @gzuncompress($html);
                $uncompressed = @base64_decode($uncompressed);
                if($uncompressed != FALSE && !empty($uncompressed)) {
                    return $uncompressed;
                }
            }
            return $html;
        }
        return parent::_afterToHtml($html);
    }
}
