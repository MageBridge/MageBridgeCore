<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * MageBridge Form Helper
 */
class MageBridgeFormHelper
{
    /*
     * Method to get the HTML of a certain field
     *
     * @param null
     * @return string
     */
    static public function getField($type, $name, $value = null, $array = 'magebridge')
    {
        if (MageBridgeHelper::isJoomla15()) {

            require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/elements/'.$type.'.php';
            $fake = null;
            $class = 'JElement'.ucfirst($type);
            $object = new $class;
            return call_user_func(array($object, 'fetchElement'), $name, $value, $fake, $array);

        } else {

            jimport('joomla.form.helper');
            jimport('joomla.form.form');

            require_once JPATH_ADMINISTRATOR.'/components/com_magebridge/fields/'.$type.'.php';

            $form = new JForm('magebridge');
            $field = JFormHelper::loadFieldType($type);
            $field->setName($name);
            $field->setValue($value);
    
            return $field->getHtmlInput();
        }
    }

    /*
     * Get an object-list of all Joomla! usergroups
     *
     * @param null
     * @return string
     */
    static public function getUsergroupOptions()
    {
        if (MageBridgeHelper::isJoomla15()) {
            $query = 'SELECT `id` AS `value`, `name` AS `text` FROM `#__core_acl_aro_groups` WHERE `parent_id` NOT IN (0, 17, 28)';
        } else {
            $query = 'SELECT `id` AS `value`, `title` AS `text` FROM `#__usergroups` WHERE `parent_id` > 0';
        }
            
        $db = JFactory::getDBO();
        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
