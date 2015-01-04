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
 * MageBridge addtocart block
 */
class Yireo_MageBridge_Block_Product_Addtocart extends Mage_Catalog_Block_Product_View
{
    public function __construct()
    {
        $this->setTemplate('magebridge/product/addtocart.phtml');
        parent::__construct();
    }
}
