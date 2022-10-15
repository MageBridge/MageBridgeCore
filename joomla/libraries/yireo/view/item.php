<?php
/**
 * Joomla! Yireo Library
 *
 * @author    Yireo (http://www.yireo.com/)
 * @package   YireoLib
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com/
 * @version   1.0.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the parent view
require_once dirname(dirname(__FILE__)) . '/loader.php';

/**
 * Item View class
 *
 * @package Yireo
 */
class YireoViewItem extends YireoView
{
    /**
     * Main display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Automatically fetch item
        $this->fetchItem();

        parent::display($tpl);
    }
}
