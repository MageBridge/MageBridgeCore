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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* MageBridge Table class
*
* @package MageBridge
*/
class TableProduct extends YireoTable
{
    /**
     * Constructor
     *
     * @param JDatabase $db
     * @return null
     */
    public function __construct(& $db) 
    {
        $this->_required = array('sku', 'connector');
        parent::__construct('#__magebridge_products', 'id', $db);
    }
}
