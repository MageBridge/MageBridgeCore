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
 * MageBridge Store-connector for Nooku
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreNooku extends MageBridgeConnectorStore
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if (is_dir(JPATH_SITE.'/components/com_nooku')) {
            return true;
        } else {
            return false;
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
        $db->setQuery("SELECT name AS title, iso_code AS value FROM #__nooku_languages");
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
        return JHTML::_('select.genericlist', $this->getOptions(), 'nooku_language', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *  
     * @param array $post
     * @return string
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['nooku_language'])) {
            return $post['nooku_language'];
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
        if ($condition == JRequest::getCmd('lang')) {
            return true;
        }
        return false;
    }
}
