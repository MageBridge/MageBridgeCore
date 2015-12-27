<?php
/**
 * @copyright    Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @package        Joomla.Administrator
 * @subpackage    com_content
 * @since        1.6
 */
if (!class_exists('JFormFieldArticle'))
{
    class JFormFieldArticle extends JFormField
    {
        /**
         * The form field type.
         *
         * @var        string
         * @since    1.6
         */
        protected $type = 'Article';

        /**
         * Method to get the field input markup.
         *
         * @return    string    The field input markup.
         * @since    1.6
         */
        protected function getInput()
        {
            // Load the modal behavior script.
            JHtml::_('behavior.modal', 'a.modal');

            // Build the script.
            $script = array();
            $script[] = '    function jSelectArticle_'.$this->id.'(id, title, catid, object) {';
            $script[] = '        document.id("'.$this->id.'_id").value = id;';
            $script[] = '        document.id("'.$this->id.'_name").value = title;';
            $script[] = '        SqueezeBox.close();';
            $script[] = '    }';
            $script[] = '    function jResetArticle_'.$this->id.'(id, title, catid, object) {';
            $script[] = '        document.id("'.$this->id.'_id").value = 0;';
            $script[] = '        document.id("'.$this->id.'_name").value = "'.JText::_('COM_CONTENT_SELECT_AN_ARTICLE').'";';
            $script[] = '    }';

            // Add the script to the document head.
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

            // Setup variables for display.
            $html = array();
            $link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$this->id;
            $link .= '&amp;'.JSession::getFormToken().'=1';

            // Load the article title
            $db = JFactory::getDBO();
            $db->setQuery('SELECT title FROM #__content WHERE id = '.(int) $this->value);
            $title = $db->loadResult();
            if (empty($title)) $title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
            $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

            // Load the article ID
            $value = $this->value;
            if (0 == (int)$this->value) {
                $value = '';
            } else {
                $value = (int)$this->value;
            }

            // HTML for Joomla! 2.5
            if(YireoHelper::isJoomla25()) {
                // The current user display field
                $html[] = '<div class="fltlft">';
                $html[] = '  <input type="text" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
                $html[] = '</div>';

                // The user select button
                $html[] = '<div class="button2-left">';
                $html[] = '  <div class="blank">';
                $html[] = '    <a class="modal btn" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('COM_CONTENT_CHANGE_ARTICLE_BUTTON').'</a>';
                $html[] = '  </div>';
                $html[] = '</div>';

                // The reset button
                $html[] = '<div class="button2-left">';
                $html[] = '  <div class="blank">';
                $html[] = '    <a class="btn" onclick="jResetArticle_'.$this->id.'(); return false;">'.JText::_('JNONE').'</a>';
                $html[] = '  </div>';
                $html[] = '</div>';

            // HTML for other Joomla!
            } else {
                $html[] = '<span class="input-append">';
                $html[] = '<input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
                $html[] = '<a class="modal btn" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> '.JText::_('JSELECT').'</a>';
                $html[] = '<button id="'.$this->id.'_clear" class="btn" onclick="return jResetArticle_'.$this->id.'();"><span class="icon-remove"></span>'.JText::_('JCLEAR').'</button>';
                $html[] = '</span>';

            }

            // class='required' for client side validation
            $class = '';
            if ($this->required) {
                $class = ' class="required modal-value"';
            }

            $html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

            return implode("\n", $html);
        }
    }
}
