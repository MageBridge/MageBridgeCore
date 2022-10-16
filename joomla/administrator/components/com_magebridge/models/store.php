<?php
/**
 * Joomla! component MageBridge
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Import Joomla! libraries
jimport('joomla.utilities.date');

/**
 * MageBridge Store model
 */
class MagebridgeModelStore extends YireoModel
{
    /**
     * Indicator if this is a model for multiple or single entries
     */
    protected $_single = true;

    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct('store');
    }

    /**
     * Method to remove multiple items
     *
     * @param array $cid
     *
     * @return bool
     */
    public function delete($cid = [])
    {
        if (is_array($cid) && in_array(0, $cid)) {
            $data = [
                'storegroup' => '',
                'storeview'  => '',
            ];

            MageBridgeModelConfig::getSingleton()->store($data);
        }

        return parent::delete($cid);
    }

    /**
     * Method to store the item
     *
     * @param array $data
     *
     * @return bool
     */
    public function store($data)
    {
        if (!empty($data['store'])) {
            $values        = explode(':', $data['store']);
            $data['type']  = ($values[0] == 'g') ? 'storegroup' : 'storeview';
            $data['name']  = $values[1];
            $data['title'] = $values[2];
            unset($data['store']);
        } else {
            $this->setError(JText::_('COM_MAGEBRIDGE_MODEL_STORE_NO_STORE_SELECTED'));

            return false;
        }

        if (!empty($data['default']) && $data['default']) {
            $this->storeDefault($data['type'], $data['name']);

            return true;
        }

        if (empty($data['name']) || empty($data['title'])) {
            $this->setError(JText::_('COM_MAGEBRIDGE_MODEL_STORE_INVALID_STORE'));

            return false;
        }

        if (empty($data['label'])) {
            $data['label'] = $data['title'];
        }

        $rt = parent::store($data);

        if ($rt == true && $data['published'] == 1) {
            MageBridge::getConfig()
                ->saveValue('load_stores', 1);
        }

        return $rt;
    }

    /**
     * Method to save the default store-name
     *
     * @param string $type
     * @param string $name
     *
     * @return bool
     */
    private function storeDefault($type, $name)
    {
        if ($type == 'storeview') {
            $post = [
                'storegroup' => '',
                'storeview'  => $name,
            ];
        } else {
            $post = [
                'storegroup' => $name,
                'storeview'  => '',
            ];
        }

        MageBridgeModelConfig::getSingleton()->store($post);

        return;
    }
}
