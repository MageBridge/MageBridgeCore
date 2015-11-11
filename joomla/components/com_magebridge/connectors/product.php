<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Product-connector class
 *
 * @package MageBridge
 */
class MageBridgeConnectorProduct extends MageBridgeConnector
{
	/**
	 * Singleton variable
	 */
	private static $_instance = null;

	/**
	 * Singleton method
	 *
	 * @param null
	 * @return MageBridgeConnectorProduct
	 */
	public static function getInstance()
	{
		static $instance;

		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Method to do something on purchase
	 *
	 * @param string $sku
	 * @param JUser $user
	 * @param string $status
	 * @return mixed
	 */
	public function runOnPurchase($sku = null, $qty = 1, $user = null, $status = null, $arguments = null)
	{
		// Get the conditions
		$conditions = $this->getConditions($sku);
		if (empty($conditions)) {
			return null;
		}

		// Import the plugins
		JPluginHelper::importPlugin('magebridgeproduct');

		// Foreach of these conditions, run the product-plugins
		foreach ($conditions as $condition) {

			// Extract the parameters and make sure there's something to do
			$actions = YireoHelper::toRegistry($condition->actions)->toArray();

			// Detect the deprecated connector-architecture
			if(!empty($condition->connector) && !empty($condition->connector_value)) {
				JFactory::getApplication()->triggerEvent('onMageBridgeProductConvertField', array($condition, &$actions));
			}

			// With empty actions, there is nothing to do
			if(empty($actions)) {
				continue;
			}

			// Check for the parameters
			if (!empty($condition->params)) {
				$params = YireoHelper::toRegistry($condition->params);
				$allowed_statuses = $params->get('allowed_status', array('any'));
				$expire_amount = $params->get('expire_amount', 0);
				$expire_unit = $params->get('expire_unit', 'day');
			} else {
				$allowed_statuses = array('any');
				$expire_amount = 0;
				$expire_unit = null;
			}

			// Do not continue if the order-status is not matched
			if(!empty($allowed_statuses) && !in_array('any', $allowed_statuses) && !in_array($status, $allowed_statuses)) {
				continue;
			}

			// Run the product plugins
			JFactory::getApplication()->triggerEvent('onMageBridgeProductPurchase', array($actions, $user, $status, $sku));
 
			// Log this event
			$this->saveLog($user->id, $sku, $expire_unit, $expire_amount);
		}
	}

	/**
	 * Method to save the actions of this connector
	 *
	 * @param int $user_id
	 * @param string $sku
	 * @param string $expire_unit
	 * @param int $expire_amount
	 * @return mixed
	 */
	public function saveLog($user_id = 0, $sku = null, $expire_unit = null, $expire_amount = null)
	{
		// Save this connector-value in the database
		if ($user_id > 0 && $expire_amount > 0) {

			switch($expire_unit) {
				case 'week':
					$expire_seconds = $expire_amount * 7 * 24 * 60 * 60;
					break;
				case 'day':
				default:
					$expire_seconds = $expire_amount * 24 * 60 * 60;
					break;
			}			

			$create_date = time();
			$expire_date = time() + $expire_seconds;

			$db = JFactory::getDBO();
			$query = "INSERT INTO #__magebridge_products_log "
				. " SET `user_id`=".(int)$user_id.", `sku`=".$db->Quote($sku).", `create_date`=".(int)$create_date.", `expire_date`=".(int)$expire_date;
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Overload methods to add an argument to it
	 */
	public function getConnectors($type = null) { return parent::_getConnectors('product'); }
	public function getConnector($name) { return parent::_getConnector('product', $name); }
	public function getConnectorObject($name) { return parent::_getConnectorObject('product', $name); }
	public function getPath($file) { return parent::_getPath('product', $file); }
	public function getParams() { return parent::_getParams('product'); }

	/**
	 * Method to get the current conditions
	 *
	 * @param string $sku
	 * @return array
	 */
	protected function getConditions($sku)
	{
		// Fetch all published product relations
		static $rows = null;
		if ($rows == null) {
			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__magebridge_products WHERE `published`=1 ORDER BY `ordering`";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}

		// Filter all product relations to return only applicable matches
		$conditions = array();
		if (!empty($rows)) {
			foreach ($rows as $row) {
				if($this->matchSku($sku, $row->sku) == true) {
					$conditions[] = $row;
				}
			}
		}

		return $conditions;
	}

	/**
	 * Method to get the current conditions
	 *
	 * @param string $sku
	 * @param string $rule
	 * @return boolean
	 */
	protected function matchSku($sku, $rule)
	{
		$sku = trim($sku);
		$rule = trim($rule);

		// Comma-seperated listing of rules
		if (strstr($rule, ',')) {
			$subrules = explode(',', $rule);
			foreach ($subrules as $subrule) {
				$match = $this->matchSku($sku, $subrule);
				if(!empty($match) && $match == true) {
					return true;
				}
			}

		// Match the filter ALL
		} elseif (strtoupper($rule) == 'ALL') {
			return true;

		// A direct match of the SKU
		} else if ($rule == $sku) {
			return true;

		// Simple simulation of LIKE-statement
		} else if (strstr($rule, '%')) {
			$s = str_replace('%', '', $rule);

			// Start with %
			if (preg_match('/^\%/', $rule)) {
				if (substr($sku, strlen($sku) - strlen($s)) == $s) {
					return true;
				}
			// End with %
			} else if (preg_match('/\%$/', $rule)) {
				if (substr($sku, 0, strlen($s)) == $s) {
					return true;
				}
			}
		}

		return false;
	}
}
