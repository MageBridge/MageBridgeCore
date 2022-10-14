<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2016
 * @license Open Source License
 * @link https://www.yireo.com
 */

/*
 * Class for authenticating customers through Joomla!
 */
class Yireo_MageBridge_Model_Rewrite_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Customer authentication
     *
     * @param   string $username
     * @param   string $password
     * @return  bool
     */
    public function authenticate($username, $password)
    {
        // Try to authenticate this user using the normal Magento way
        $rt = false;
        try {
            $rt = parent::authenticate($username, $password);
        } catch(Exception $e) {
            $rt = false;
        }

        // Continue when Joomla! Authentication is enabled
        if ($rt == false && Mage::helper('magebridge')->allowJoomlaAuth() == true) {
            Mage::getSingleton('magebridge/debug')->notice('Calling Joomla! authentication through API for customer '.$username);

            // Perform the actual call through RPC to Joomla!
            $api_result = Mage::getSingleton('magebridge/client')->call('magebridge.login', [$username, $password]);
            if (is_array($api_result) && !empty($api_result)) {
                // Load the customer
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

                if (!empty($api_result['email'])) {
                    $customer->loadByEmail($api_result['email']);
                } else {
                    $customer->loadByEmail($username);
                }

                // Create this customer-record if it does not yet exist
                if (!$customer->getId() > 0) {
                    // Load a basic record
                    $customer->setEmail($username);
                    $customer->setPassword($password);
                    $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                    $customer->setId(null);
                    $customer->save();

                    // Load the full record
                    $customer->setEmail($username);
                    $customer = Mage::getModel('customer/customer')->load($customer->getId());
                    $customer->setConfirmation(null);
                    $customer->save();

                // Remember that Magento authentication failed, so reset the password
                } else {
                    $customer->changePassword($password);
                    $customer->setConfirmation(null);
                    $customer->save();
                }

                // Load the current customer-object with the details we have so far
                $this->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
                $this->loadByEmail($customer->getEmail());

                // Now the customer exists, so try again
                Mage::dispatchEvent('customer_customer_authenticated', [
                   'model' => $this,
                   'password' => $password,
                ]);

                $rt = true;
            }
        }

        if ($rt != true) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Authentication failed.'), self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
        }

        return $rt;
    }
}
