<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

/*
 * MageBridge API-model for user resources
 */
class Yireo_MageBridge_Model_User_Api extends Mage_Api_Model_Resource_Abstract
{
    /*
     * API-method to sync user-data to Magento
     *
     * @access public
     * @param array $options
     * @return array
     */
    public function save($data = array())
    {
        // Disable all event forwarding
        if(isset($data['disable_events'])) {
            Mage::getSingleton('magebridge/core')->disableEvents();
        }

        // Make sure the data is there, and contains at least an email
        if(empty($data) || !isset($data['email'])) {
            Mage::getSingleton('magebridge/debug')->warning('No email in data');
            return false;
        }

        Mage::getSingleton('magebridge/debug')->trace('User data from Joomla!', $data);

        // Save the customer
        $rt = $this->saveCustomer($data);

        // Save the backend-user 
        if(isset($data['admin']) && (bool)$data['admin'] == true) {
            $this->saveAdminUser($data);
        }

        return $rt;
    }

    /*
     * API-method to save a customer to the database
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function saveCustomer($data = array())
    {
        try {
            // Initialize the session
            Mage::getSingleton('core/session', array('name'=>'frontend'));
            $session = Mage::getSingleton('customer/session');

            // Load the customer 
            $customer = Mage::getModel('magebridge/user')->load($data);
            if(empty($customer)) {
                return false;
            }

            // Set new values
            if($customer->getId() > 0 == false) {
                $customer->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
                if(isset($data['website_id'])) $customer->setWebsiteId((int)$data['website_id']);
            }

            // Load the new data
            foreach($data as $name => $value) {
                if(!empty($name)) {

                    // Skip fields that look like address-fields
                    if(preg_match('/^address_/', $name)) {
                        continue;
                    }

                    // Build the new method-name
                    $method = Mage::helper('magebridge')->stringToSetMethod($name);
                    if(empty($method)) {
                        continue;
                    }

                    // Call the method to set the new value
                    $customer->$method($value);
                }
            }

            // Support for MageBridge First Last plugin
            if(!empty($data['magebridgefirstlast']['firstname'])) {
                $customer->setFirstname($data['magebridgefirstlast']['firstname']);
            }
            if(!empty($data['magebridgefirstlast']['lastname'])) {
                $customer->setLastname($data['magebridgefirstlast']['lastname']);
            }

            // Set required entries
            if($customer->getFirstname() == '') $customer->setFirstname('-');
            if($customer->getLastname() == '') $customer->setLastname('-');

            // Set the customer group if it is not set yet
            if(!$customer->getGroupId() > 0 && !empty($data['default_customer_group'])) {
                $customer->setGroupId($data['default_customer_group']);
            }

            // Override the value of customer group
            if(!empty($data['customer_group'])) {
                $customer->setGroupId($data['customer_group']);
            }

            // Try to save the customer
            if($customer->save() == false) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save customer '.$customer->getEmail());
            }

            // Set the confirmation
            if(empty($data['activation']) || (isset($data['block']) && $data['block'] == 0)) {
                $customer->setConfirmation(null);
                $customer->save();
            }

            // Try to save the new mapping
            $this->saveMapping($customer, $data);

            // Decrypt and set the password 
            if(isset($data['password_clear'])) {
                $data['password_clear'] = trim($data['password_clear']);
                if(!empty($data['password_clear'])) {
                    $data['password_clear'] = Mage::helper('magebridge/encryption')->decrypt($data['password_clear']);
                    $customer->load($customer->getId()); 
                    $customer->changePassword($data['password_clear']);
                }
            }
                    
            // Get the current password-hash
            $data['hash'] = $customer->getPasswordHash();

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load customer: '.$e->getMessage());
        }

        // Try to save the address
        if(!empty($customer)) {
            try {
                $this->saveAddress($customer, $data);
            } catch(Exception $e) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save customer address: '.$e->getMessage());
            }
        }

        return $data;
    }

    /*
     * API-method to save an admin user to the database
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function saveAdminUser($data = array())
    {
        try {

            // Load the customer 
            $user = Mage::getModel('magebridge/user')->loadAdminUser($data);

            // Set new values for new users
            if($user->getId() > 0 == false) {
                $user->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', time()));
            }
    
            // Workaround to prevent the password from being reset
            $user->setNewPassword(false);
            $user->setOrigData('password', $user->getPassword());

            // Load the new data
            foreach($data as $name => $value) {
                if(!empty($name)) {
                    if($name == 'password') continue;
                    $user->setData($name, $value);
                }
            }

            // Support for MageBridge First Last plugin
            if(!empty($data['magebridgefirstlast']['firstname'])) {
                $user->setFirstname($data['magebridgefirstlast']['firstname']);
            }
            if(!empty($data['magebridgefirstlast']['lastname'])) {
                $user->setLastname($data['magebridgefirstlast']['lastname']);
            }

            // Set required entries
            if($user->getFirstname() == '') $user->setFirstname('-');
            if($user->getLastname() == '') $user->setLastname('-');

            // Decrypt and set the password 
            if(isset($data['password_clear'])) {
                $data['password_clear'] = trim($data['password_clear']);
                if(!empty($data['password_clear'])) {
                    $data['password_clear'] = Mage::helper('magebridge/encryption')->decrypt($data['password_clear']);
                    if(!empty($data['password_clear'])) {
                        $user->setPassword($data['password_clear']);
                    }
                }
            }

            // Try to save the user
            if($user->save() == false) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save admin-user '.$user->getEmail());
            }

                    
        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load admin-user: '.$e->getMessage());
        }

        return $data;
    }

    /*
     * Helper-method to save the Joomla!/Magento mapping
     *
     * @access private
     * @param array $data
     * @return array
     */
    private function saveMapping($customer, $data)
    {
        if(isset($data['joomla_id'])) {

            $customer->load($customer->getId());
            $map = array(
                'customer_id' => $customer->getId(),
                'joomla_id' => $data['joomla_id'],
                'website_id' => $customer->getWebsiteId(),
            );

            if(Mage::helper('magebridge/user')->saveUserMap($map) == false) {
                Mage::getSingleton('magebridge/debug')->error('Failed to save customer mapping '.$customer->getEmail());
            }

        } else {
            Mage::getSingleton('magebridge/debug')->notice('No mapping possible');
        }
    }

    /*
     * Helper-method to save this address
     *
     * @access private
     * @param Mage_Customer_Model_Customer $customer
     * @param array $data
     * @return array
     */
    private function saveAddress($customer, $data)
    {
        if(empty($customer) || !is_object($customer)) {
            return false;
        }

        $address = $customer->getPrimaryBillingAddress();
        if(empty($address) || !is_object($address)) {
            $address = Mage::getModel('customer/address');
        }

        // Load the new data
        $fields = false;
        foreach($data as $name => $value) {

            // Skip fields that do not look like address-fields
            if(!preg_match('/^address_/', $name)) {
                continue;
            }

            // Build the new method-name
            $name = preg_replace('/^address_/', '', $name);
            $method = Mage::helper('magebridge')->stringToSetMethod($name);
            if(empty($method)) {
                continue;
            }

            // Call the method to set the new value
            $fields = true;
            $address->$method($value);
        }

        // If no fields are set by now, it makes sense to try and save the address
        if($fields == false) return false;

        // Set specific fields for the address
        $address
            ->setIsPrimaryBilling(true)
            ->setIsPrimaryShipping(true)
        ;

        // Save the address
        if($address->save() == false) {
            Mage::getSingleton('magebridge/debug')->error('Failed to save address '.$customer->getEmail());
            return false;
        }

        return true;
    }

    /*
     * API-method to delete a customer 
     *
     * @access public
     * @param array $data
     * @return bool
     */
    public function delete($data = array())
    {
        // Use this, to make sure Magento thinks it's dealing with the right app.
        // Otherwise _protectFromNonAdmin() will make this fail.
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));

        // Initialize the customer
        $customer = Mage::getModel('magebridge/user')->load($data);

        // Delete the customer
        if($customer->getId() && $customer->isDeleteable()) {
            Mage::getSingleton('magebridge/debug')->notice('Customer delete');
            $customer->delete();
            return true;
        } elseif($customer->isDeleteable() == false) {
            Mage::getSingleton('magebridge/debug')->error('Customer is not deleteable');
        } else {
            Mage::getSingleton('magebridge/debug')->error('Customer did not exist');
        }

        return false;
    }

    /*
     * API-method to login a customer (SSI not SSO, without passwords)
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function login($data = array())
    {
        // Disable all event forwarding
        if(isset($data['disable_events'])) {
            Mage::getSingleton('magebridge/core')->disableEvents();
        }

        // Decrypt the email address
        $data['email'] = Mage::helper('magebridge/encryption')->decrypt($data['email'], 'email');
        $data['email'] = stripslashes($data['email']);

        // Determine whether to do a backend or a frontend login
        switch($data['application']) {

            case 'admin':
                // SSI in the backend is not needed (yet)
                break;

            default:
                $session = Mage::getSingleton('customer/session');
                $customer = $session->getCustomer();
                $customer->loadByEmail($data['email']);
                $session->setCustomerAsLoggedIn($customer);
                break;
        }

        return array();
    }
}
