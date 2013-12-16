<?php
/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('JPATH_BASE') or die();

// Import the MageBridge autoloader
require_once JPATH_SITE.'/components/com_magebridge/helpers/loader.php';

/*
 * Form Field-class for choosing a specific Magento customer-group in a selection-box
 */
class JFormFieldUsergroup extends JFormFieldAbstract
{
    /*
     * Form field type
     */
    public $type = 'Joomla! usergroup';

    /*
     * Method to get the HTML of this element
     *
     * @param null
     * @return string
     */
	protected function getInput()
	{
        $name = $this->name;
        $fieldName = $name;
        $value = $this->value;

        $usergroups = MageBridgeFormHelper::getUsergroupOptions();
        return JHTML::_('select.genericlist', $usergroups, $fieldName, null, 'value', 'text', $value);
    }
}
