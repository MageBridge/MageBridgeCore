<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @license GNU Public License
 * @link https://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include the parent controller
jimport('joomla.application.component.controller');

/**
 * MageBridge SSO Controller
 *
 * @package MageBridge
 */
class MageBridgeControllerSso extends YireoAbstractController
{
    /**
     * Method to make login an user
     */
    public function login()
    {
        // Fetch the user-email
        $user_email = MageBridgeEncryptionHelper::decrypt(JFactory::getApplication()->input->getString('token'));
        $application = JFactory::getApplication();

        // Perform a post-login
        $rt = MageBridge::getUser()->postlogin($user_email, null, true);

        // Determine the redirect URL
        $redirectUrl = base64_decode(JFactory::getApplication()->input->getString('redirect'));
        if (empty($redirectUrl)) {
            $redirectUrl = MageBridgeModelBridge::getInstance()->getMagentoUrl();
        }

        // Redirect
        $application->redirect($redirectUrl);
        $application->close();
    }

    /**
     * Method to make logout the current user
     */
    public function logout()
    {
        // Perform a logout
        $user = JFactory::getUser();
        $application = JFactory::getApplication();
        $application->logout($user->get('id'));

        // Determine the redirect URL
        $redirectUrl = base64_decode(JFactory::getApplication()->input->getString('redirect'));
        if (empty($redirectUrl)) {
            $redirectUrl = MageBridgeModelBridge::getInstance()->getMagentoUrl();
        }

        // Redirect
        $application->redirect($redirectUrl);
        $application->close();
    }
}
