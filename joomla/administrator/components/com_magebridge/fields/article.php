<?php
/**
 * @copyright      Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal article picker.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_content
 */
class MagebridgeFormFieldArticle extends JFormField
{
    /**
     * The form field type.
     *
     * @var        string
     */
    protected $type = 'Article';

    /**
     * Article title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        $this->setArticleTitle();

        // Load the modal behavior script.
        JHtml::_('behavior.modal', 'a.modal');
        $this->addScriptDeclaration();

        // Load the article ID
        $value = (int) $this->value;

        if (0 == (int) $this->value) {
            $value = '';
        }

        $html = [];
        $html = array_merge($html, $this->getHtmlButton());

        // Class='required' for client side validation
        $class = $this->getHtmlClass();

        $html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

        return implode("\n", $html);
    }

    /**
     * Set article title
     */
    protected function setArticleTitle()
    {
        // Load the article title
        $db = JFactory::getDbo();
        $db->setQuery('SELECT title FROM #__content WHERE id = ' . (int) $this->value);
        $title = $db->loadResult();

        if (empty($title)) {
            $title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
        }

        $title       = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $this->title = $title;
    }

    /**
     * Add script declaration
     */
    protected function addScriptDeclaration()
    {
        // Build the script.
        $script   = [];
        $script[] = '	function jSelectArticle_' . $this->id . '(id, title, catid, object) {';
        $script[] = '		document.id("' . $this->id . '_id").value = id;';
        $script[] = '		document.id("' . $this->id . '_name").value = title;';
        $script[] = '		SqueezeBox.close();';
        $script[] = '	}';
        $script[] = '	function jResetArticle_' . $this->id . '(id, title, catid, object) {';
        $script[] = '		document.id("' . $this->id . '_id").value = 0;';
        $script[] = '		document.id("' . $this->id . '_name").value = "' . JText::_('COM_CONTENT_SELECT_AN_ARTICLE') . '";';
        $script[] = '	}';

        // Add the script to the document head.
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration(implode("\n", $script));
    }

    /**
     * @return array
     */
    protected function getHtmlButton()
    {
        $title = $this->title;

        // Setup variables for display.
        $sessionToken = JSession::getFormToken();
        $link         = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_' . $this->id;
        $link .= '&amp;' . $sessionToken . '=1';

        $html   = [];
        $html[] = '<span class="input-append">';
        $html[] = '<input type="text" class="input-medium" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
        $html[] = '<a class="modal btn" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">';
        $html[] = '<i class="icon-file"></i> ' . JText::_('JSELECT');
        $html[] = '</a>';
        $html[] = '<button id="' . $this->id . '_clear" class="btn" onclick="return jResetArticle_' . $this->id . '();">';
        $html[] = '<span class="icon-remove"></span>' . JText::_('JCLEAR');
        $html[] = '</button>';
        $html[] = '</span>';

        return $html;
    }

    /**
     * @return string
     */
    protected function getHtmlClass()
    {
        $class = '';

        if ($this->required) {
            $class = ' class="required modal-value"';
        }

        return $class;
    }
}
