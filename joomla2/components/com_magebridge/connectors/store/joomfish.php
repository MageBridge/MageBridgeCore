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

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Store-connector for Joom!Fish
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreJoomfish extends MageBridgeConnectorStore
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if (is_dir(JPATH_SITE.'/components/com_joomfish')) {
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
        $db->setQuery("SELECT * FROM #__languages WHERE 1=1");
        $rows = $db->loadObjectList();

        $this->options = array(); 
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $value = null;
                $title = null;
                if (isset($row->sef) && empty($value)) $value = $row->sef;
                if (isset($row->shortcode) && empty($value)) $value = $row->shortcode;
                if (isset($row->title) && empty($title)) $title = $row->title;
                if (isset($row->name) && empty($title)) $title = $row->name;
                if (empty($title)) $title = $value;
                $this->options[] = array('title' => $title, 'value' => $value);
            }
        }
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
        return JHTML::_('select.genericlist', $this->getOptions(), 'joomfish_language', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return string
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['joomfish_language'])) {
            return $post['joomfish_language'];
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
        // Fetch the current language
        $language = JFactory::getLanguage();

        // Fetch the languages
        $joomfish = JoomFishManager::getInstance();
        $languages = $joomfish->getActiveLanguages();
        if (!empty($languages)) {
            foreach ($languages as $l) {
                if ($language->getTag() == $l->code || $language->getTag() == $l->lang_code) {
                    if (!empty($l->shortcode)) {
                        $language_code = $l->shortcode;
                        break;
                    } else if (!empty($l->sef)) {
                        $language_code = $l->sef;
                        break;
                    }
                }
            }
        } else {
            $language_code = JRequest::getCmd('lang');
        }

        if ($condition == $language_code) {
            return true;
        }
        return false;
    }
}
