<?php
/**
 * Joomla! MageBridge - Finder plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link      http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

// Import the MageBridge autoloader
include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Finder Plugin
 */
class PlgFinderMageBridge extends FinderIndexerAdapter
{
	/**
	 * @var string
	 */
	protected $context = 'MageBridge';

	/**
	 * @var string
	 */
	protected $extension = 'com_magebridge';

	/**
	 * @var string
	 */
	protected $layout = 'magebridge';

	/**
	 * @var string
	 */
	protected $type_title = 'Product';

	/**
	 * Constructor
	 *
	 * @param object $subject
	 * @param array  $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();
	}

	/**
	 * Method to setup this finder-plugin
	 *
	 * @return bool
	 */
	protected function setup()
	{
		// Import the MageBridge autoloader
		include_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

		return true;
	}

	/**
	 * Method to index a single item
	 *
	 * @param FinderIndexerResult $item
	 * @param string $format
	 *
	 * @return null
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Product');

		// @todo: Add the category taxonomy data.
		//$item->addTaxonomy('Category', $item->category);

		// @todo: Add the language taxonomy data.
		//$item->addTaxonomy('Language', $item->language);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to load all products through the API
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	protected function loadProducts($offset, $limit)
	{
		// Get the main variables
		$bridge = MageBridge::getBridge();
		$register = MageBridge::getRegister();

		// Calculate the Magento page
		$page = round($offset / $limit);

		// Setup the arguments and register this request
		$arguments = array('search' => 1, 'page' => $page, 'count' => $limit, 'visibility' => array(3, 4));
		$id = $register->add('api', 'magebridge_product.list', $arguments);

		// Build the bridge
		$bridge->build();

		// Get the requested data from the register
		$data = $register->getDataById($id);

		return $data;
	}

	/**
	 * Method to index all items
	 *
	 * @param int    $offset
	 * @param int    $limit
	 * @param string $sql
	 *
	 * @return array
	 */
	protected function getItems($offset, $limit, $sql = null)
	{
		$items = array();
		$products = $this->loadProducts($offset, $limit);

		// Loop through the products to build the item-array
		foreach ($products as $product)
		{
			//$this->debug("page [$offset;$limit] ".$product['name']);

			// Construct a basic class
			$item = new FinderIndexerResult();

			// Add basics
			$item->id = $product['product_id'];
			$item->title = $product['name'];

			// Add URLs
			$item->request = $product['url_path'];
			$item->url = 'index.php?option=com_magebridge&view=root&request=' . $item->request;
			$item->route = 'index.php?option=com_magebridge&view=root&request=' . $item->request;

			// Add body-text
			if (!empty($product['short_description']))
			{
				$item->summary = $product['short_description'];
			}
			else
			{
				$item->summary = $product['description'];
			}

			// Add additional data
			$item->image = $product['image'];
			$item->small_image = $product['small_image'];
			$item->layout = $this->layout;
			$item->type_id = $this->getTypeId();

			// Add some flags
			$item->published = 1;
			$item->state = 1;
			$item->access = 1;
			$item->language = 'en-GB'; // @todo

			// Add pricing
			// @todo: Why is in the finder-database but not documented?
			$item->list_price = $product['price_raw'];
			$item->sale_price = $product['price_raw'];

			// Add extra search terms
			if (is_array($product['search']))
			{
				foreach ($product['search'] as $searchName => $searchValue)
				{
					$item->$searchName = $searchValue;
					$item->addInstruction(FinderIndexer::TEXT_CONTEXT, $searchName);
				}
			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Method to get the total of products
	 *
	 * @return int
	 */
	protected function getContentCount()
	{
		// Get the main variables
		$bridge = MageBridge::getBridge();
		$register = MageBridge::getRegister();

		// Register this API-request
		$arguments = array();
		$id = $register->add('api', 'magebridge_product.count', $arguments);

		// Build the bridge
		$bridge->build();

		// Return the product-count
		$count = $register->getDataById($id);

		return $count;
	}

	/**
	 * Helper method for debugging
	 *
	 * @param string $msg
	 * @param mixed  $var
	 */
	protected function debug($msg, $var = null)
	{
		if ($var != null)
		{
			$msg .= ': ' . var_export($var, true);
		}

		$msg = $msg . "\n";
		//file_put_contents('/tmp/magebridge_finder.log', $msg, FILE_APPEND);
	}
}
