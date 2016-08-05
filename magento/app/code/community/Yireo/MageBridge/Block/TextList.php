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

/**
 * MageBridge rewrite of the default core/text_list-block
 */
class Yireo_MageBridge_Block_TextList extends Mage_Core_Block_Text_List
{
    /**
     * @var array
     */
    protected $cachableBlocks = array('content');

    /**
     * @return mixed|string
     */
    protected function _toHtml()
    {
        if ($this->allowBlockCache($this->getNameInLayout())) {
            return $this->loadBlockCache();
        }

        return parent::_toHtml();
    }

    /**
     * @return false|mixed|string
     */
    protected function loadBlockCache()
    {
        $cached = $this->_loadCache();
        if ($cached) {
            return $cached;
        }

        $html = parent::_toHtml();
        $html = trim($html);
        $compressed = @base64_encode($html);
        $compressed = @gzcompress($compressed, 6);
        if ($compressed) {
            return $compressed;
        }

        return $html;
    }

    /**
     * Determine whether a block can be cached or not
     */
    protected function allowBlockCache($blockName)
    {
        if (Mage::getStoreConfig('magebridge/cache/caching_gzip') !== 1) {
            return false;
        }

        if (!in_array($blockName, $this->cachableBlocks)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $html
     *
     * @return string
     */
    protected function _afterToHtml($html)
    {
        if (Mage::getStoreConfig('magebridge/cache/caching_gzip') == 1) {
            if (!empty($html)) {
                $uncompressed = @gzuncompress($html);
                $uncompressed = @base64_decode($uncompressed);
                if ($uncompressed != FALSE && !empty($uncompressed)) {
                    return $uncompressed;
                }
            }
            return $html;
        }
        return parent::_afterToHtml($html);
    }
}
