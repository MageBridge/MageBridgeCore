<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Store-connector for Joomla! 1.6 or higher
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreJoomla extends MageBridgeConnectorStore
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if (MageBridgeHelper::isJoomla15()) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Method to return options
     *
     * @param null
     * @return array
     */
    public function getOptions()
    {
        $db = JFactory::getDBO();
        $db->setQuery("SELECT `title`, `lang_code` AS `value` FROM #__languages ORDER BY `title`");
        $this->options = $db->loadObjectList();
        return $this->options;
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        return JHTML::_('select.genericlist', $this->getOptions(), 'joomla_language', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return string
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['joomla_language'])) {
            return $post['joomla_language'];
        }
        return null;
    }

    /*
     * Method to check whether the given condition is true
     *
     * @param string $condition
     * @return bool
     */
    public function checkCondition($condition = null)
    {
        $language_code = JRequest::getCmd('language');
        if ($condition == $language_code) {
            return true;
        }
        return false;
    }
}
