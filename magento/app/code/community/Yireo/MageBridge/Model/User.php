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

define( 'MAGEBRIDGE_AUTHENTICATION_FAILURE', 0 );
define( 'MAGEBRIDGE_AUTHENTICATION_SUCCESS', 1 );
define( 'MAGEBRIDGE_AUTHENTICATION_ERROR', 2 );

/*
 * MageBridge model handling users (for both frontend as backend)
 */
class Yireo_MageBridge_Model_User 
{
    /**
     * Data
     */
    protected $_data = null;

    /*
     * Loads the current customer-record
     *
     * @access public
     * @param array $data
     * @return Mage_Customer_Model_Customer
     */
    public function load($data)
    {
        return $this->loadCustomer($data);
    }

    /*
     * Loads the current customer-record
     *
     * @access public
     * @param array $data
     * @return Mage_Customer_Model_Customer
     */
    public function loadCustomer($data)
    {
        // Get a clean customer-object
        $customer = Mage::getModel('customer/customer');
        if(isset($data['website_id'])) $customer->setWebsiteId($data['website_id']);

        // Check if there is already a mapping between Joomla! and Magento for this user
        if(isset($data['joomla_id']) && isset($data['website_id'])) {
            $map = Mage::helper('magebridge/user')->getUserMap(array('joomla_id' => $data['joomla_id'], 'website_id' => $data['website_id']));
            if(isset($map['customer_id'])) {
                $customer->load((int)$map['customer_id']);
            }
        }

        // If we have a valid customer-record return it
        if($customer->getId() > 0) {
            return $customer;
        }

		// Determine the username and email
		$email = (isset($data['email'])) ? $data['email'] : null;
		$email = (isset($data['original_data']['email'])) ? $data['original_data']['email'] : $email;

		$username = (isset($data['username'])) ? $data['username'] : null;
		$username = (isset($data['original_data']['username'])) ? $data['original_data']['username'] : $username;

        // Try to load it by username (if it's an email-address)
        if(!empty($username) && Mage::helper('magebridge/user')->isEmailAddress($username) == true) {
            $customer->loadByEmail(stripslashes($username));

        // Try to load it by email
        } elseif(!empty($email)) {
            $customer->loadByEmail(stripslashes($email));
        }

        return $customer;
    }

    /*
     * Loads the current admin-record
     *
     * @access public
     * @param array $data
     * @return Mage_Admin_Model_User
     */
    public function loadAdminUser($data)
    {
        // Get a clean customer-object
        $user = Mage::getModel('admin/user');

		// Determine the username and email
		$email = (isset($data['email'])) ? $data['email'] : null;
		$email = (isset($data['original_data']['email'])) ? $data['original_data']['email'] : $email;

		$username = (isset($data['username'])) ? $data['username'] : null;
		$username = (isset($data['original_data']['username'])) ? $data['original_data']['username'] : $username;

        // Try to load it by username
        if(!empty($username)) {
            $user->loadByUsername(stripslashes($username));

        // Try to load it by email
        } elseif(!empty($email)) {
            $user->loadByEmail(stripslashes($email));
        }

        return $user;
    }

    /*
     * Perform a Single Sign On if told so in the bridge-request
     * 
     * @access public
     * @param null
     * @return bool
     */
    public function doSSO()
    {
        // Allow for debugging
        Mage::getSingleton('magebridge/core')->setMetaData('debug', true);

        // Get the SSO-flag from $_GET
        $sso = Mage::app()->getRequest()->getQuery('sso');
        $app = Mage::app()->getRequest()->getQuery('app');

        if(!empty($sso) && !empty($app)) {

            switch($sso) {
                case 'logout':
                    $this->doSSOLogout($app);
                    return true;

                case 'login':
                    $this->doSSOLogin($app);
                    return true;
            }
        }

        return false;
    }

    /*
     * Perform a Single Sign On logout
     * 
     * @access private
     * @param string $app
     * @return null
     */
    private function doSSOLogout($app = 'site') 
    {
        Mage::getSingleton('magebridge/debug')->notice('doSSOLogout('.$app.'): '.session_id());

        // Decrypt the userhash
        $userhash = Mage::app()->getRequest()->getQuery('userhash');
        $username = Mage::helper('magebridge/encryption')->decrypt($userhash);

        // Initialize the session and end it
        if($app == 'admin') {

            $user = Mage::getSingleton('admin/session')->getUser();
            if(!empty($user) && $user->getUsername() == $username) {
                Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
                $session = Mage::getSingleton('adminhtml/session');
                $session->unsetAll();
                setcookie( 'adminhtml', null );
                session_destroy();
            }

        } else {
    
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if(!empty($customer) && $customer->getEmail() == $username) {
                Mage::getSingleton('core/session', array('name'=>'frontend'));
                Mage::getSingleton('customer/session')->logout();
                setcookie( 'frontend', null );
                session_destroy();
            }
        }

        // Redirect
        header( 'HTTP/1.1 302');
        header( 'Location: '.base64_decode(Mage::app()->getRequest()->getQuery('redirect')));
        return true;
    }
    
    /*
     * Perform a Single Sign On login
     * 
     * @access private
     * @param string $app
     * @return bool
     */
    private function doSSOLogin($app = 'site') 
    {
        Mage::getSingleton('magebridge/debug')->notice('doSSOLogin ['.$app.']: '.session_id());

        // Construct the redirect back to Joomla!
        $host = null;
        $arguments = array(
            'option=com_magebridge',
            'task=login',
        );

        // Loop to detect other variables
        foreach(Mage::app()->getRequest()->getQuery() as $name => $value) {
            if($name == 'base') $host = base64_decode($value);
            if($name == 'token') $token = $value;
        }

        // Decrypt the userhash
        $userhash = Mage::app()->getRequest()->getQuery('userhash');
        $username = Mage::helper('magebridge/encryption')->decrypt($userhash);

        // Backend / frontend login
        if($app == 'admin') {
            $newhash = $this->doSSOLoginAdmin($username);
        } else {
            $newhash = $this->doSSOLoginCustomer($username);
        }

        $arguments[] = 'hash='.$newhash;
        $arguments[] = $token.'=1';

        // Redirect
        header( 'HTTP/1.1 302');
        header( 'Location: '.$host.'index.php?'.implode('&', $arguments ));
        return true;
    }

    /*
     * Perform an customer SSO login
     * 
     * @access private
     * @param string $username
     * @return string
     */
    private function doSSOLoginCustomer($username)
    {
        // Initialize the session
        Mage::getSingleton('core/session', array('name'=>'frontend'));
        $session = Mage::getSingleton('customer/session');

        // Initialize the customer
        $customer = $session->getCustomer();
        $customer->loadByEmail($username);
        if(!$customer->getId() > 0) return null;

        Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [frontend] username '.$customer->getEmail());

        // Process the hash
        $passwordhash = $customer->getPasswordHash();
        $returnhash = md5($passwordhash);

        // Save the customer in the actual data if this simple authentication succeeds
        $session->setCustomerAsLoggedIn($customer);
        session_regenerate_id();
        setcookie('frontend', session_id());

        return $returnhash;
    }

    /*
     * Perform an admin SSO login
     * 
     * @access private
     * @param string $username
     * @return string
     */
    private function doSSOLoginAdmin($username) 
    {
        return null;

        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        if(isset($_COOKIE['adminhtml'])) {
            Mage::getSingleton('adminhtml/session')->setSessionId($_COOKIE['adminhtml']);
        }

        $user = Mage::getSingleton('admin/user');
        $user->loadByUsername($username);
        $oldhash = $user->getPassword();
        $newhash = md5(md5($oldhash));

        if($user->getId()) {

            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }

            // Initialize the session
            $session = Mage::getSingleton('admin/session');
            if($session->getAdmin() == null || $session->getAdmin()->getId() == false) {

                Mage::getSingleton('magebridge/debug')->notice('doSSOLogin [admin]: Login user '.$username);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                //$session->revalidateCookie();

                session_regenerate_id();
                setcookie('adminhtml', session_id());

            }
        }

        return $newhash;
    }

    /*
     * Perform an user-authentication (Joomla! onAuthenticate event)
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function authenticate($data = array())
    {
        return $this->login($data);
    }

    /*
     * Perform an user-login (Joomla! onAuthenticate event)
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

        // Decrypt the login credentials
        $data['username'] = Mage::helper('magebridge/encryption')->decrypt($data['username'], 'customer username');
        $data['password'] = Mage::helper('magebridge/encryption')->decrypt($data['password'], 'customer password');

        // Determine whether to do a backend or a frontend login
        switch($data['application']) {
            case 'admin':
                return $this->loginAdmin($data);

            default:
                return $this->loginCustomer($data);
        }

        return array();
    }

    /*
     * Perform an customer-login (Joomla! onAuthenticate event)
     * 
     * @access private
     * @param array $data
     * @return array
     */
    private function loginCustomer($data = array()) 
    {
        // Get the username and password
        $username = $data['username'];
        $password = $data['password'];

        try {
            $session = Mage::getSingleton('customer/session');
        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to start customer session');
            return $data;
        }

        try {

            if($session->isLoggedIn()) {
                Mage::getSingleton('magebridge/debug')->error('Already logged in');
                $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
    
            } elseif($session->login($username, $password)) {

                Mage::getSingleton('magebridge/debug')->notice('Login of '.$username);
                $customer = $session->getCustomer();
                $session->setCustomerAsLoggedIn($customer);

                $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                $data['email'] = $customer->getEmail();
                $data['fullname'] = $customer->getName();
                $data['hash'] = $customer->getPasswordHash();

            } else {
                Mage::getSingleton('magebridge/debug')->error('Login failed');
                $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to login customer "'.$username.'": '.$e->getMessage());
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform an admin-login (Joomla! onAuthenticate event)
     * 
     * @access private
     * @param array $data
     * @return array
     */
    private function loginAdmin($data) 
    {
        // Get the username and password
        $username = $data['username'];
        $password = $data['password'];

        try {

            Mage::getSingleton('magebridge/debug')->notice('Admin login of '.$username);

            $user = Mage::getSingleton('admin/user');
            $user->login($username, $password);
            if($user->getId()) {

                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }

                $session = Mage::getSingleton('admin/session');
                $session->setIsFirstVisit(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                
                session_regenerate_id();

                $data['state'] = MAGEBRIDGE_AUTHENTICATION_SUCCESS;
                $data['email'] = null;
                $data['fullname'] = null;
                $data['hash'] = md5($user->getPassword());

            } else {
            
                Mage::getSingleton('magebridge/debug')->error('Admin login failed');
                $data['state'] = MAGEBRIDGE_AUTHENTICATION_FAILURE;
            }

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to login admin: '.$e->getMessage());
            $data['state'] = MAGEBRIDGE_AUTHENTICATION_ERROR;
            return $data;
        }

        return $data;
    }

    /*
     * Perform a logout
     *
     * @access public
     * @param array $data
     * @return array
     */
    public function logout($data)
    {
        // Disable all event forwarding
        if(isset($data['disable_events'])) {
            Mage::getSingleton('magebridge/core')->disableEvents();
        }

        Mage::getSingleton('magebridge/debug')->notice('Logout customer');
        try {
            $session = Mage::getSingleton('customer/session');
            $session->logout();
            $data['state'] = 0;

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to logout customer: '.$e->getMessage());
            $data['state'] = 2;
        }

        return $data;
    }
}
