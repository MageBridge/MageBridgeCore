<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Software License
 * @link http://www.yireo.com
 */

/*
 * MageBridge observer to various Magento events
 */
class Yireo_MageBridge_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Method to list all current events
     *
     * @access public
     * @param null
     * @return array
     */
    public function getEvents()
    {
        $auth = Mage::helper('core')->__('Authentication');
        $customer = Mage::helper('core')->__('Customer Synchronization');
        $catalog = Mage::helper('core')->__('Catalog Synchronization');
        $sales = Mage::helper('core')->__('Sales Connectors');
        $newsletter = Mage::helper('core')->__('Newsletter Connectors');

        return array(
            array('address_save_after', 0, $customer),
            array('admin_session_user_login_success', 0, $auth),
            array('adminhtml_customer_save_after', 1, $customer),
            array('adminhtml_customer_delete_after', 1, $customer),
            array('catalog_product_save_after', 0, $catalog),
            array('catalog_product_delete_after', 0, $catalog),
            array('catalog_category_save_after', 0, $catalog),
            array('catalog_category_delete_after', 0, $catalog),
            array('catalog_product_status_update', 0, $catalog),
            array('catalog_product_is_salable_before', 0, $catalog),
            array('checkout_cart_add_product_complete', 0, $sales),
            array('checkout_controller_onepage_save_shipping_method', 0, $sales),
            array('checkout_onepage_controller_success_action', 1, $sales),
            array('checkout_type_onepage_save_order_after', 0, $sales),
            array('customer_delete_after', 1, $customer),
            array('customer_save_after', 1, $customer),
            array('customer_login', 1, $auth),
            array('customer_logout', 1, $auth),
            array('sales_convert_order_to_quote', 0, $sales),
            array('sales_order_complete_after', 1, $sales),
            array('sales_order_cancel_after', 1, $sales),
            array('sales_order_save_after', 0, $sales),
            array('sales_order_place_after', 0, $sales),
            array('newsletter_subscriber_save_after', 1, $newsletter),
        );
    }

    /**
     * Method fired on the event <address_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function addressSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $address = $observer->getEvent()->getObject();
        $arguments = array(
            'address' => Mage::helper('magebridge/event')->getAddressArray($address),
        );

        $this->fireEvent('address_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <admin_session_user_login_success>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminSessionUserLoginSuccess($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $user = $observer->getEvent()->getUser();
        $arguments = array(
            'user' => Mage::helper('magebridge/event')->getUserArray($user),
        );

        $this->fireEvent('admin_session_user_login_success', $arguments);
        return $this;
    }

	/**
     * Method fired on the event <adminhtml_customer_save_before>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
	public function adminhtmlCustomerSaveBefore($observer)
	{
		return $this;
	}

    /**
     * Method fired on the event <adminhtml_customer_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminhtmlCustomerSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

		// Check for syncing customer groups
		if(Mage::helper('magebridge/user')->allowSyncCustomerGroup($customer->getGroupId()) == false) {
			Mage::getSingleton('magebridge/debug')->trace('Customer group not allowed syncing', $customer->getGroupId());
			return $this;
		}

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        // Perform the API-call and fetch the result
        $rt = $this->fireEvent('customer_save_after', $arguments);
    
        // If this looks like a Joomla! ID, store it 
        if($rt > 0) {
            Mage::helper('magebridge/user')->saveUserMap(array(
                'customer_id' => $customer->getId(),
                'joomla_id' => $rt,
                'website_id' => $customer->getWebsiteId(),
            ));
        }

        return $this;
    }

    /**
     * Method fired on the event <adminhtml_customer_delete_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function adminhtmlCustomerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();

		// Check for syncing customer groups
		if(Mage::helper('magebridge/user')->allowSyncCustomerGroup($customer->getGroupId()) == false) {
			Mage::getSingleton('magebridge/debug')->trace('Customer group not allowed syncing', $customer->getGroupId());
			return $this;
		}

        // Check for duplicate records and stop if there are any
        $duplicateCustomers = Mage::helper('magebridge/user')->getCustomersByEmail($customer->getEmail());
        if ($duplicateCustomers->getSize() > 1) {
            Mage::getSingleton('magebridge/debug')->trace('Skipping user sync because of duplicate records', $customer->getEmail());
            return $this;
        }

        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_is_salable_before>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductIsSalableBefore($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $arguments = array(
            'product' => Mage::helper('magebridge/event')->getProductArray($product),
        );

        $this->fireEvent('catalog_product_is_salable_before', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $arguments = array(
            'product' => Mage::helper('magebridge/event')->getProductArray($product),
        );

        $this->fireEvent('catalog_product_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_delete_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $arguments = array(
            'product' => Mage::helper('magebridge/event')->getProductArray($product),
        );

        $this->fireEvent('catalog_product_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_category_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogCategorySaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $category = $observer->getEvent()->getObject();
        $arguments = array(
            'category' => Mage::helper('magebridge/event')->getCategoryArray($category),
        );

        $this->fireEvent('catalog_category_save_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_category_delete_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogCategoryDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $category = $observer->getEvent()->getObject();
        $arguments = array(
            'category' => Mage::helper('magebridge/event')->getCategoryArray($category),
        );

        $this->fireEvent('catalog_category_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <catalog_product_status_update>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function catalogProductStatusUpdate($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $product_id = $observer->getEvent()->getProductId();
        $store_id = $observer->getEvent()->getStoreId();
        $arguments = array(
            'product' => $product_id,
            'store_id' => $store_id,
        );

        $this->fireEvent('catalog_product_status_update', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_cart_add_product_complete>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutCartAddProductComplete($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        $request = $observer->getEvent()->getRequest();

        $arguments = array(
            'product' => Mage::helper('magebridge/event')->getProductArray($product),
            'request' => $request->getParams(),
        );

        $this->fireEvent('checkout_cart_add_product_complete', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_controller_onepage_save_shipping_method>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutControllerOnepageSaveShippingMethod($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $quote = $observer->getEvent()->getQuote();
        $request = $observer->getEvent()->getRequest();

        $arguments = array(
            'quote' => Mage::helper('magebridge/event')->getQuoteArray($quote),
            'request' => $request->getParams(),
        );

        $this->fireEvent('checkout_controller_onepage_save_shipping_method', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_onepage_controller_success_action>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutOnepageControllerSuccessAction($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId(); 
        $order = Mage::getModel('sales/order')->load($orderId);
        $arguments = array(
            'order' => Mage::helper('magebridge/event')->getOrderArray($order),
        );

        Mage::helper('magebridge')->setStore($order->getStoreId());
        $this->fireEvent('checkout_onepage_controller_success_action', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <checkout_type_onepage_save_order_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        $arguments = array(
            'order' => Mage::helper('magebridge/event')->getOrderArray($order),
            'quote' => Mage::helper('magebridge/event')->getQuoteArray($quote),
        );

        Mage::helper('magebridge')->setStore($order->getStoreId());
        $this->fireEvent('checkout_type_onepage_save_order_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <controller_action_predispatch>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        // Run the feed
        Mage::getModel('magebridge/feed')->updateIfAllowed();

        // Get the variables
        $controller = $observer->getEvent()->getControllerAction();
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();

        // Remote SSO login within native Magento frontend
        $mb_postlogin = Mage::getModel('core/cookie')->get('mb_postlogin');
        if(!empty($mb_postlogin) && Mage::helper('magebridge')->isBridge() == false) {

            // Remove the cookie
            Mage::getModel('core/cookie')->delete('mb_postlogin', '/');

            // Check if remote SSO is enabled
            if(Mage::getStoreConfig('magebridge/joomla/remotesso') == 1) {

                // Redirect to the Joomla! SSO URL
                $arguments = array('controller' => 'sso', 'task' => 'login', 'token' => $mb_postlogin, 'redirect' => base64_encode($currentUrl));
                $url = Mage::helper('magebridge')->getApiUrl($arguments);
                if(!empty($url)) {
                    Mage::app()->getResponse()->setRedirect($url);
                }
            }
        }

        // Remote SSO logout
        if (preg_match('/customer\/account\/logoutSuccess/i', $currentUrl) == true) {

            // No action
            if(empty($_COOKIE) || Mage::getModel('core/cookie')->get('mb_remotelogout') == 1) {
                Mage::getModel('core/cookie')->delete('mb_remotelogout', '/');
                Mage::getModel('core/cookie')->delete('mb_postlogin', '/');
                return $this;
            }

            // Check if bridge is NOT loaded
            if(Mage::helper('magebridge')->isBridge() == true) {
                return $this;
            }

            // Check if remote SSO is enabled
            if(Mage::getStoreConfig('magebridge/joomla/remotesso') == 1) {

                // Set a cookie
                Mage::getModel('core/cookie')->set('mb_remotelogout', 1, null, '/');

                // Redirect to the Joomla! SSO URL
                $url = Mage::helper('magebridge')->getApiUrl(array('controller' => 'sso', 'task' => 'logout', 'redirect' => base64_encode($currentUrl)));
                if(!empty($url)) {
                    Mage::app()->getResponse()->setRedirect($url);
                }
            }
        }

        return $this;
    }

    /**
     * Method fired on the event <controller_action_layout_render_before>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionLayoutRenderBefore($observer)
    {
        Mage::getSingleton('magebridge/debug')->notice('MB Listener receives event "controller_action_layout_render_before"');
        return $this;
    }

    /**
     * Method fired on the event <controller_action_layout_load_before>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function controllerActionLayoutLoadBefore($observer)
    {
        Mage::getSingleton('magebridge/debug')->notice('MB Listener receives event "controller_action_layout_load_before"');
        return $this;
    }

    /**
     * Method fired on the event <customer_delete_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerDeleteAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        // Get the customer
        $customer = $observer->getEvent()->getCustomer();

        // Delete the mapping
        $map = Mage::getModel('magebridge/customer_joomla')->load($customer->getId());
        if($map->getId() > 0) {
            $map->delete();
        }

		// Check for syncing customer groups
		if(Mage::helper('magebridge/user')->allowSyncCustomerGroup($customer->getGroupId()) == false) {
			Mage::getSingleton('magebridge/debug')->trace('Customer group not allowed syncing', $customer->getGroupId());
			return $this;
		}

        // Check for duplicate records and stop if there are any
        $duplicateCustomers = Mage::helper('magebridge/user')->getCustomersByEmail($customer->getEmail());
        if ($duplicateCustomers->getSize() > 1) {
            Mage::getSingleton('magebridge/debug')->trace('Skipping user sync because of duplicate records', $customer->getEmail());
            return $this;
        }

        // Build the API arguments
        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        $this->fireEvent('customer_delete_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_login>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerLogin($observer)
    {
        // Set the postlogin-cookie
        $customer_email = $observer->getEvent()->getCustomer()->getEmail();
        $encrypted = Mage::helper('magebridge/encryption')->encrypt($customer_email);
        Mage::getModel('core/cookie')->set('mb_postlogin', $encrypted, null, '/');

        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        // Fire only when in the bridge
        if(Mage::helper('magebridge')->isBridge() == false) {
            return $this;
        }

		$customer = $observer->getEvent()->getCustomer();

		// Check for syncing customer groups
		if(Mage::helper('magebridge/user')->allowSyncCustomerGroup($customer->getGroupId()) == false) {
			Mage::getSingleton('magebridge/debug')->trace('Customer group not allowed syncing', $customer->getGroupId());
			return $this;
		}

        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        $this->fireEvent('customer_login', $arguments);
        $this->addEvent('magento', 'customer_login_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_logout>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerLogout($observer)
    {
        // Unset the postlogin-cookie
        Mage::getModel('core/cookie')->delete('mb_postlogin', '/');

        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        // Fire only when in the bridge
        if(Mage::helper('magebridge')->isBridge() == false) {
            return $this;
        }

        $customer = $observer->getEvent()->getCustomer();
        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        $this->fireEvent('customer_logout', $arguments);
        $this->addEvent('magento', 'customer_logout_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <customer_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function customerSaveAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        // Build the API arguments
        $customer = $observer->getEvent()->getCustomer();

		// Check for syncing customer groups
		if(Mage::helper('magebridge/user')->allowSyncCustomerGroup($customer->getGroupId()) == false) {
			Mage::getSingleton('magebridge/debug')->trace('Customer group not allowed syncing', $customer->getGroupId());
			return $this;
		}

        $arguments = array(
            'customer' => Mage::helper('magebridge/event')->getCustomerArray($customer),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($customer->getStoreId());

        // Forward the event
        $rt = $this->fireEvent('customer_save_after', $arguments);

        // Save the user-mapping if it's there
        if($rt > 0) {
            Mage::helper('magebridge/user')->saveUserMap(array(
                'customer_id' => $customer->getId(),
                'joomla_id' => $rt,
                'website_id' => $customer->getWebsiteId(),
            ));
        }

        return $this;
    }

	/**
	 * Method fired on the event <customer_save_before>
	 *
	 * @access public
	 * @param Varien_Event_Observer $observer
	 * @return Yireo_MageBridge_Model_Observer
	 */
	public function customerSaveBefore($observer)
	{
		return $this;
	}

    /**
     * Method fired on the event <joomla_on_after_delete_user>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function joomlaOnAfterDeleteUser($arguments)
    {
        return $this;
    }

    /**
     * Method fired on the event <newsletter_subscriber_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function newsletterSubscriberSaveAfter($observer)
    {
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $subscriber = $observer->getEvent()->getSubscriber();
        if($subscriber->getIsStatusChanged() == false) {
            return false;
        }

        if($subscriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
            $state = 1;
        } else {
            $state = 0;
        }

        $arguments = array(
            'subscriber' => array(
                'email' => $subscriber->getSubscriberEmail(),
                'state' => $state,
            ),
        );

        $this->fireEvent('newsletter_subscriber_change_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_convert_order_to_quote>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesConvertOrderToQuote($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        
        $arguments = array(
            'order' => Mage::helper('magebridge/event')->getOrderArray($order),
            'quote' => Mage::helper('magebridge/event')->getQuoteArray($quote),
        );

        Mage::helper('magebridge')->setStore($order->getStoreId());
        $this->fireEvent('sales_convert_order_to_quote', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_order_place_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesOrderPlaceAfter($observer)
    {
        // Check if this event is enabled
        if($this->isEnabled($observer) == false) {
            return $this;
        }

        // Get the object from event
        $order = $observer->getEvent()->getOrder();

        // Set the current scope
        Mage::helper('magebridge')->setStore($order->getStoreId());

        // Construct the arguments
        $arguments = array(
            'order' => Mage::helper('magebridge/event')->getOrderArray($order),
        );

        // Fire the event
        $this->fireEvent('sales_order_place_after', $arguments);
        return $this;
    }

    /**
     * Method fired on the event <sales_order_save_after>
     *
     * @access public
     * @param Varien_Event_Observer $observer
     * @return Yireo_MageBridge_Model_Observer
     */
    public function salesOrderSaveAfter($observer)
    {
        // Get the order from this event and convert it to an array
        $order = $observer->getEvent()->getOrder();
        $arguments = array(
            'order' => Mage::helper('magebridge/event')->getOrderArray($order),
        );

        // Set the current scope
        Mage::helper('magebridge')->setStore($order->getStoreId());

        // Event that is fired every time when the order is saved
        if($this->isEnabled('sales_order_save_after')) {
            $this->fireEvent('sales_order_save_after', $arguments);
            $this->addEvent('magento', 'sales_order_save_after', $arguments);
        }

        // Event that is fired once the order is completed
        if($this->isEnabled('sales_order_complete_after') && $order->getData('state') == 'complete' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_complete_after', $arguments);
            $this->addEvent('magento', 'sales_order_complete_after', $arguments);
        }

        // Event that is fired once the order is cancelled
        if($this->isEnabled('sales_order_cancel_after') && $order->getData('state') == 'cancel' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_cancel_after', $arguments);
            $this->addEvent('magento', 'sales_order_cancel_after', $arguments);
        }

        // Event that is fired once the order is closed
        if($this->isEnabled('sales_order_closed_after') && $order->getData('state') == 'closed' && $order->getData('state') != $order->getOrigData('state')) {
            $this->fireEvent('sales_order_closed_after', $arguments);
            $this->addEvent('magento', 'sales_order_closed_after', $arguments);
        }

        return $this;
    }

    /**
     * Method that adds this event to the Joomla! bridge-reply
     *
     * @access public
     * @param string $group
     * @param string $event
     * @param mixed $arguments
     * @return bool
     */
    public function addEvent($group = null, $event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        if(empty($event)) {
            Mage::getSingleton('magebridge/debug')->notice('Listener: Empty event');
            return false; 
        }

        // Convert the lower-case event-name to camelCase
        $event = Mage::helper('magebridge/event')->convertEventName($event);

        // Add this event to the response-data
        Mage::getSingleton('magebridge/debug')->notice('Listener: Adding event "'.$event.'" to the response-data');
        Mage::getSingleton('magebridge/session')->addEvent($group, $event, $arguments);
        return true;
    }

    /**
     * Method that forwards the event to Joomla! straight-away through RPC
     * 
     * @access public
     * @param string $event
     * @param mixed $arguments
     * @return bool
     */
    public function fireEvent($event = null, $arguments = null)
    {
        // Exit if the event-name is empty
        if(empty($event)) {
            return false; 
        }

        // Force the argument as struct
        if(!is_array($arguments)) {
            $arguments = array('null' => 'null');
        }

        $api_url = Mage::helper('magebridge')->getApiUrl();
        Mage::getSingleton('magebridge/debug')->notice('Listener: Forwarding event "'.$event.'" through RPC ('.$api_url.')');

        // Convert the lower-case event-name to camelCase
        $event = Mage::helper('magebridge/event')->convertEventName($event);

        // Gather the pending logs
        $logs = array();
        foreach(Mage::getSingleton('magebridge/debug')->getData() as $log) {
            foreach(array('type', 'message', 'section', 'time') as $index) {
                if(!isset($log[$index])) $log[$index] = '';
            }
            $logs[] = $log;
        }

        // Clean the logs for now
        Mage::getSingleton('magebridge/debug')->clean();

        // Initialize the API call
        $rt = Mage::getSingleton('magebridge/client')->call('magebridge.event', array($event, $arguments, $logs));

        return $rt;
    }

    /**
     * Method to check if an event is enabled or not
     * 
     * @access public
     * @param string $event
     * @return bool
     */
    public function isEnabled($event)
    {
        if(is_object($event)) {
            $event = $event->getEvent()->getName();
        }

        // Check if event forwarding is disabled globally
        if(Mage::getSingleton('magebridge/core')->isEnabledEvents() == false) {
            Mage::getSingleton('magebridge/debug')->notice('Listener: All events are disabled');
            return false;
        }

        // Check if this event is enabled through the System Configuration
        $enabled = Mage::getStoreConfig('magebridge/settings/event_forwarding/'.$event);

        // If nothing is set in the System Configuration, take the default
        if(!is_numeric($enabled)) {
            foreach($this->getEvents() as $eventDefault) {
                if($eventDefault[0] == $event) {
                    $enabled = $eventDefault[1];
                    break;
                }
            }
        }

        // Convert the integer to a boolean
        if(is_numeric($enabled)) {
            return (boolean)$enabled;
        }

        return false;
    }
}
