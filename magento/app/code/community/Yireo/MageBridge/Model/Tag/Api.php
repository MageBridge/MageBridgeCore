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
 * MageBridge API-model for product tag resources
 */
class Yireo_MageBridge_Model_Tag_Api extends Mage_Api_Model_Resource_Abstract
{
    /*
     * Method to get a list of products based on an array of tags
     *
     * @access public
     * @param array $tags
     * @return array
     */
    public function items($tags = array())
    {
        if(empty($tags) || !is_array($tags)) {
            return false;
        }

        $result = array();
        foreach($tags as $tag) {

            $tagModel = Mage::getModel('tag/tag')->loadByName((string)$tag);
            $products = $tagModel->getEntityCollection()->addTagFilter($tagModel->getTagId());

            foreach($products as $product) {
                $p = array();
                $p['name'] = $product->getName();
                $p['url'] = $product->getProductUrl(false);
                $result[$product->getId()] = $p;
            }
        }

        return $result;
    }
}
