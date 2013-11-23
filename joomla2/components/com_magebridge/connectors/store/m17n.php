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
 * MageBridge Store-connector for M17n
 *
 * @package MageBridge
 */
class MageBridgeConnectorStoreM17n extends MageBridgeConnectorStore
{
    /*
     * Method to check whether this connector is enabled or not
     * 
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        if (is_file(JPATH_SITE.'/plugins/system/M17n.php')) {
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
        jimport('joomla.utilities.arrayhelper');
        $languages = JLanguage::getKnownLanguages(JPATH_ROOT);
        foreach ($languages as $index => $language) {
            $language['value'] = $language['tag'];
            $language['title'] = $language['name'];
            $languages[$index] = JArrayHelper::toObject($language);
        }

        $this->options = $languages;
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
        return JHTML::_('select.genericlist', $this->getOptions(), 'm17n_language', null, 'value', 'title', $value);
    }

    /*
     * Method to return the selected value from POST
     *  
     * @param array $post
     * @return string
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['m17n_language'])) {
            return $post['m17n_language'];
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
        $user = JFactory::getUser();
        if (!is_object($user) || !is_object($user->getParameters())) {
            return false;
        }

        if ($condition == $user->getParameters()->get('language')) {
            return true;
        }
        return false;
    }
}
