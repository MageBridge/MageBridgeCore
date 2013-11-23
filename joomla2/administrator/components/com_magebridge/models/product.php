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
defined('_JEXEC') or die();

// Import Joomla! libraries
jimport('joomla.utilities.date');

/*
 * MageBridge Product model
 */
class MagebridgeModelProduct extends YireoModel
{
    /**
     * Constructor method
     *
     * @access public
     * @param null
     * @return null
     */
    public function __construct()
    {
        $this->_orderby_title = 'label';
        parent::__construct('product');
    }

    /**
     * Method to store the item
     *
     * @package MageBridge
     * @access public
     * @param array $data
     * @return bool
     */
    public function store($data)
    {
        if(!empty($data['connector'])) {
            $productConnector = new MageBridgeConnectorProduct();
            $connector = $productConnector->getConnector($data['connector']);
            if ($connector == false) {
                $this->setError(JText::_('Failed to load connector'));
                return false;
            }

            $data['connector_value'] = $connector->getFormPost($data);
        }

        if (empty($data['label'])) {
            $data['label'] = $data['sku'];
        }

        return parent::store($data);
    }
}
