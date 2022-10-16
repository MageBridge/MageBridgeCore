<?php
/**
 * Joomla! MageBridge - Magento plugin
 *
 * @author    Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2016
 * @license   GNU Public License
 * @link      https://www.yireo.com
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Import the parent class
jimport('joomla.plugin.plugin');

// Import the MageBridge autoloader
require_once JPATH_SITE . '/components/com_magebridge/helpers/loader.php';

/**
 * MageBridge Magento Plugin
 */
class PlgMagentoMageBridge extends JPlugin
{
    /**
     * @var JApplicationWeb
     */
    protected $app;

    /**
     * Get the MageBridge user-class
     *
     * @return MageBridgeModelUser
     */
    public function getUser()
    {
        return MageBridge::getUser();
    }

    /**
     * Handle the event that is generated after a customer is deleted (JSON-RPC)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageCustomerDeleteAfter($arguments = [])
    {
        // Abort if the input is invalid
        if (empty($arguments) || empty($arguments['customer']['email'])) {
            return false;
        }

        // Fetch the user from the user-table
        $user = $this->getUser()->loadByEmail($arguments['customer']['email']);

        // If there is an user with this email, delete it, but only if we are allowed to
        if ($this->getUser()->allowSynchronization($user, 'delete')) {
            $user->delete();
        }

        return true;
    }

    /**
     * Handle the event that is generated after a customer logs out (bridge-response)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageCustomerLogoutAfter($arguments = [])
    {
        // Abort if the input is invalid
        if (empty($arguments) || empty($arguments['customer']['email'])) {
            return false;
        }

        // Joomla! expects more parameters, so we need to fetch those first
        $customer = $arguments['customer'];
        $user = $this->getUser()->loadByEmail($customer['email']);
        $customer['username'] = $customer['email'];
        $customer['id'] = $user->id;

        // Add options for our own user-plugin
        // @todo: options:
        // - action = core.login.site
        // - remember = 1
        // - return = {URL}
        // - entry_url = {URL}
        $options = ['disable_bridge' => true, 'action' => 'core.login.site', 'clientid' => $this->app->getClientId()];

        // Call the Joomla! event "onLogoutUser"
        JPluginHelper::importPlugin('user');
        $this->app->triggerEvent('onUserLogout', [$customer, $options]);

        return true;
    }

    /**
     * Handle the event that is generated when a customer logs in (JSON-RPC)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageCustomerLogin($arguments = [])
    {
        // Abort if the input is invalid
        if (empty($arguments) || empty($arguments['customer']['email'])) {
            return false;
        }

        // Joomla! expects more parameters, so we need to fetch those first
        $customer = $arguments['customer'];
        $user = $this->getUser()->loadByEmail($customer['email']);

        // We had a succesfull login in Magento, but the user does not exist in Joomla! yet
        if (empty($user)) {
            $customer['username'] = $customer['email'];
            $user = $this->getUser()->create($customer, true);
        }

        // Note: We're in JSON-RPC, so we can't login the customer because the session does not exist
        return true;
    }

    /**
     * Handle the event that is generated after a customer logs in (passed into the bridge-response)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageCustomerLoginAfter($arguments = [])
    {
        // Abort if the input is invalid
        if (empty($arguments) || empty($arguments['customer']['email'])) {
            return false;
        }

        // Joomla! expects more parameters, so we need to fetch those first
        $customer = $arguments['customer'];
        $user = $this->getUser()->loadByEmail($customer['email']);

        // We had a succesfull login in Magento, but the user does not exist in Joomla! yet
        if (empty($user)) {
            $customer['username'] = $customer['email'];
            $customer['fullname'] = $customer['name'];
            $this->getUser()->create($customer, true);
            $user = $this->getUser()->loadByEmail($customer['email']);
        } else {
            $customer['fullname'] = $user->get('name');
            $customer['email'] = $user->get('email');
            $customer['username'] = $user->get('username');
        }

        // Check for the right user-ID
        if ($user->id > 0) {
            $customer['id'] = $user->id;
        } else {
            return false;
        }

        // Do a post-login
        MageBridge::getUser()->postlogin(null, $user->id, false);

        return true;
    }

    /**
     * Handle the event that is generated after a customer is saved in Magento (JSON-RPC)
     *
     * @param array $arguments
     *
     * @return int
     */
    public function mageCustomerSaveAfter($arguments = [])
    {
        // Check if this call is valid
        if (!isset($arguments['customer'])) {
            return false;
        }

        if (!isset($arguments['customer']['email'])) {
            return false;
        }

        // Fetch the right variables
        $customer = $arguments['customer'];

        if (isset($customer['addresses'][0][0])) {
            $address = $customer['addresses'][0][0];
            unset($customer['addresses']);
        } else {
            $address = [];
        }

        // Try to load the user through the Joomla! ID stored in Magento
        if (isset($customer['joomla_id'])) {
            $user = JFactory::getUser();
            $user->load($customer['joomla_id']);
        }

        // Try to load the user through its email-address
        if (!isset($user) || !isset($user->id) || !$user->id > 0) {
            $user = $this->getUser()->loadByEmail($customer['email']);
        }

        // Encrypt the user-password before continuing
        if (isset($customer['password'])) {
            $customer['password'] = MageBridge::decrypt($customer['password']);
        }

        // Start building the data-input for creating the Joomla! user
        if (!empty($customer['name']) && !empty($customer['email'])) {
            $data = [];

            // Include the received email
            if (!empty($customer['email'])) {
                $data['email'] = $customer['email'];
            }

            // Include the real name
            $data['name'] = $this->getRealname($user, $customer);

            // Include the username
            $data['username'] = $this->getUsername($user, $customer);

            // Set the firstname and lastname
            $data['magebridgefirstlast'] = [
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],];

            // Include the password
            if (!empty($customer['password'])) {
                $data['password'] = $customer['password'];
                $data['password2'] = $customer['password'];
            }

            // Activate this account, if it's also activated in Magento
            if (isset($customer['is_active'])) {
                if ($customer['is_active'] == 1) {
                    $data['activation'] = '';
                    $data['block'] = 0;
                } else {
                    $data['block'] = 1;
                }
            }

            // Add the usergroup ID to this user (based upon groups configured in #__magebridge_usergroups)
            $usergroups = (is_array($user->groups)) ? $user->groups : [];
            $customer['usergroup'] = MageBridgeUserHelper::getJoomlaGroupIds($customer, $usergroups);
            //MageBridgeModelDebug::getInstance()->trace("Customer group", $customer['usergroup']);

            if (!empty($customer['usergroup'])) {
                $user->groups = $customer['usergroup'];
            }

            // If this user did not exist yet, create it
            if ($this->params->get('autocreate', 1) == 1 && ($user == false || $user->id == 0)) {
                $user = $this->getUser()->create($data, true);

                if (is_object($user) && $user->id > 0) {
                    // Save the user through the Profile Connectors
                    $profile = new MageBridgeConnectorProfile();
                    $profile->onSave($user, $customer, $address);

                    // Return the new user-ID
                    return $user->id;
                } else {
                    return 3;
                }
            }

            // Bind the data to the current user
            if ($user->bind($data) == false) {
                return false;
            }

            // Save the user
            if ($user->save() == false) {
                return false;
            }

            // Save the user through the Profile Connectors
            $profile = new MageBridgeConnectorProfile();
            $profile->onSave($user, $customer, $address);

            // Run the newsletter plugins
            if (isset($customer['is_subscribed'])) {
                JPluginHelper::importPlugin('magebridgenewsletter');
                $this->app->triggerEvent('onNewsletterSubscribe', [$user, (bool)$customer['is_subscribed']]);
            }

            // Return the user ID for convenience
            return $user->id;
        }

        return false;
    }

    /**
     * Handle the event that is generated after an order is saved in Magento (JSON-RPC)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageCheckoutOnepageControllerSuccessAction($arguments = [])
    {
        // Fetch the arguments
        $products = $arguments['order']['products'];
        $customer = $arguments['order']['customer'];
        $orderState = (isset($arguments['order']['state'])) ? $arguments['order']['state'] : null;

        // Don't continue if this is a guest-only order
        if (!isset($customer['email'])) {
            return false;
        }

        // Load the corresponding Joomla! user
        $user = $this->getUser()->loadByEmail($customer['email']);

        // Loop through the products and run the product connector
        foreach ($products as $product) {
            $productQty = (isset($product['qty'])) ? $product['qty'] : 1;

            if ($productQty < 1) {
                $productQty = 1;
            }

            MageBridgeConnectorProduct::getInstance()->runOnPurchase($product['sku'], $productQty, $user, $orderState, $arguments);
        }

        return true;
    }

    /**
     * Handle the event that is generated after a newsletter subscriber is changed in Magento
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageNewsletterSubscriberChangeAfter($arguments = [])
    {
        // Fetch the arguments
        $subscriber = $arguments['subscriber'];
        $state = $subscriber['state'];

        // Load the corresponding Joomla! user
        $user = $this->getUser()->loadByEmail($subscriber['email']);

        // Run the newsletter plugins
        JPluginHelper::importPlugin('magebridgenewsletter');
        $this->app->triggerEvent('onNewsletterSubscribe', [$user, $state]);

        return true;
    }

    /**
     * Handle the event that is generated after an order is saved in Magento (RPC)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageSalesOrderSaveAfter($arguments = [])
    {
        // Fetch the arguments
        $products = $arguments['order']['products'];
        $customer = $arguments['order']['customer'];

        if (isset($arguments['order']['state'])) {
            $state = $arguments['order']['state'];
        } else {
            $state = null;
        }

        // Don't continue if this is a guest-only order
        if (!isset($customer['email'])) {
            return false;
        }

        // Load the corresponding Joomla! user
        $user = $this->getUser()->loadByEmail($customer['email']);

        // Loop through the products and run the product connector
        foreach ($products as $product) {
            $productQty = (isset($product['qty'])) ? $product['qty'] : 1;

            if ($productQty < 1) {
                $productQty = 1;
            }

            MageBridgeConnectorProduct::getInstance()->runOnPurchase($product['sku'], $productQty, $user, $state, $arguments);
        }

        return true;
    }

    /**
     * Handle the event that is generated after an order is completed in Magento (RPC)
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function mageSalesOrderCompleteAfter($arguments = [])
    {
        // Fetch the arguments
        $products = $arguments['order']['products'];
        $customer = $arguments['order']['customer'];
        $state = $arguments['order']['state'];

        // Don't continue if this is a guest-only order
        if (!isset($customer['email'])) {
            return false;
        }

        // Load the corresponding Joomla! user
        $user = $this->getUser()->loadByEmail($customer['email']);

        // Loop through the products and run the product connector
        foreach ($products as $product) {
            $productQty = (isset($product['qty'])) ? $product['qty'] : 1;

            if ($productQty < 1) {
                $productQty = 1;
            }

            MageBridgeConnectorProduct::getInstance()->runOnPurchase($product['sku'], $productQty, $user, $state, $arguments);
        }

        return true;
    }

    /**
     * Return a MageBridge configuration parameter
     *
     * @param string $name
     *
     * @return mixed $value
     */
    private function getParam($name = null)
    {
        return MageBridgeModelConfig::load($name);
    }

    /**
     * Helper function to determine the right username
     *
     * @param mixed $user
     * @param array $customer
     *
     * @return string
     */
    private function getUsername($user, $customer)
    {
        // If Magento "magically" comes up with an username, use that (for instance, in case of a third party extension)
        if (isset($customer['username'])) {
            return $customer['username'];
        }

        // Do some checks, but only if $user is a valid object
        if (is_object($user)) {
            if ($this->getParam('username_from_email') == 1 || $user->get('username') == $user->get('email')) {
                return $customer['email'];
            } else {
                return $user->get('username');
            }

        // Just use the email-address
        } else {
            return $customer['email'];
        }
    }

    /**
     * Helper function to determine the right name
     *
     * @param mixed $user
     * @param array $customer
     *
     * @return string
     */
    private function getRealname($user, $customer)
    {
        if ($this->getParam('realname_from_firstlast') == 0 && is_object($user)) {
            return $user->get('name');
        }

        if (isset($customer['firstname']) && isset($customer['lastname'])) {
            return $customer['firstname'] . ' ' . $customer['lastname'];
        } else {
            if (isset($customer['name'])) {
                return $customer['name'];
            }
        }

        return null;
    }
}
