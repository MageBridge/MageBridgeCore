<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2014
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge API-model for attribute resources
 */
class Yireo_MageBridge_Model_Attribute_Api extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve list of attribute sets
     *
     * @access public
     * @param null
     * @return array
     */
    public function getAttributeSets()
    {
        $entityType = Mage::getModel('catalog/product')->getResource()->getTypeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter($entityType);
        $defaultId = (int)Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();

        $res = array();
        foreach ($collection as $item) {
            $data['value'] = $item->getId();
            $data['label'] = $item->getAttributeSetName();
            $data['default'] = ($item->getId() == $defaultId) ? 1 : 0;
            $res[] = $data;
        }

        return $res;
    }

    /**
     * Retrieve list of attribute groups
     *
     * @access public
     * @param null
     * @return array
     */
    public function getAttributeGroups()
    {
        $collection = Mage::getModel('eav/entity_attribute_group')->getCollection()
            ->setOrder('sort_order', 'ASC')
        ;

        foreach ($collection as $item) {
            $data['value'] = $item->getId();
            $data['label'] = $item->getAttributeGroupName();
            $data['set'] = $item->getAttributeSetId();
            $res[] = $data;
        }

        return $res;
    }

    /**
     * Retrieve list of attributes
     *
     * @access public
     * @param null
     * @return array
     */
    public function getAttributes($data = null)
    {
        $attributesetId = 0;
        if(!empty($data['attributeset_id'])) {
            $attributesetId = (int)$data['attributeset_id'];
        } elseif(!empty($data['default'])) {
            $attributesetId = (int)Mage::getModel('catalog/product')->getResource()->getEntityType()->getDefaultAttributeSetId();
        }

        $attributeGroups = $this->getAttributeGroups();
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        if($attributesetId > 0) {
            $attributes->setAttributeSetFilter($attributesetId);
        }

        $res = array();
        foreach ($attributes as $attribute) {

            // Skip invisible attributes
            if($attribute->getIsVisible() == 0) {
                continue;
            }

            $data['code'] = $attribute->getAttributecode();
            $data['label'] = $attribute->getFrontendLabel();
            $data['input'] = $attribute->getFrontendInput();
            $data['required'] = $attribute->getIsRequired();
            $data['user_defined'] = $attribute->getIsUserDefined();
            $data['wysiwyg'] = $attribute->getIsWysiwygEnabled();
            $data['apply_to'] = $attribute->getApplyTo();
            $data['position'] = $attribute->getPosition();
            $data['group_value'] = null;
            $data['group_label'] = null;

            $groupId = $attribute->getAttributeGroupId();
            foreach($attributeGroups as $attributeGroup) {
                if($attributeGroup['value'] == $groupId) {
                    $data['group_value'] = $attributeGroup['value'];
                    $data['group_label'] = $attributeGroup['label'];
                    break;
                }
            }

            $data['options'] = array();
            foreach ( $attribute->getSource()->getAllOptions(true, true) as $option){
                $data['options'][$option['value']] = $option['label'];
            }

            $res[] = $data;
        }

        return $res;
    }
}
