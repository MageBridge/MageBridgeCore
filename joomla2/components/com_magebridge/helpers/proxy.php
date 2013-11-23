<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
        
/*
 * Helper for proxy
 */
class MageBridgeProxyHelper 
{
    /*
     * Proxy uploads
     *
     * @param null
     * @return bool
     */
    public static function upload()
    {
        // Don't do anything outside of the MageBridge component
        if (JRequest::getCmd('option') != 'com_magebridge') {
            return array();
        }

        // Define some variables
        $application = JFactory::getApplication();
        $tmp_files = array();

        // Automatically handle file uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $name => $file) {

                if (empty($file['tmp_name']) || empty($file['name'])) {
                    continue;
                }

                // Detect file upload problems
                $error_msg = null;
                switch($file['error']) {
                    case 1: 
                    case 2: 
                        $error_msg = JText::sprintf('Upload of %s exceeded the maximum size [%d]', $file['name'], $file['error']);
                        break;
    
                    case 3:
                    case 4:
                    case 6:
                    case 7:
                    case 8:
                        $error_msg = JText::sprintf('Error when uploading file %s [%d]', $file['name'], $file['error']);
                        break;
                }

                // Move the uploaded file to the Joomla! tmp-directory
                if (is_readable($file['tmp_name'])) {

                    // Upload the specific file
                    jimport('joomla.filesystem.file');
                    $tmp_file = JFactory::getApplication()->getCfg('tmp_path').'/'.$file['name'];
                    JFile::upload($file['tmp_name'], $tmp_file);

                    // Check if the file is there
                    if (!is_file($tmp_file) || !is_readable($tmp_file)) {
                        $error_msg = JText::sprintf('Unable to read uploaded file %s', $tmp_file);
                
                    } else if (!filesize($tmp_file) > 0) {
                        $error_msg = JText::sprintf('Uploaded file %s is empty', $tmp_file);
        
                    } else {
                        $tmp_files[$name] = $tmp_file;
                    }
                } else {
                    $error_msg = JText::sprintf('Uploaded file %s is not readable', $tmp_file);
                }

                // Handle errors
                if (!empty($error_msg)) {

                    // See if we can redirect back to the same old page
                    $request = JRequest::getString('request');

                    if (preg_match('/\/uenc\/([a-zA-Z0-9\,\-\_]+)/', $request, $uenc)) {
                        $page = MageBridgeEncryptionHelper::base64_decode($uenc[1]);
                        if (!empty($uenc) && !empty($page)) {

                            // Remove the old file
                            self::cleanup($tmp_files);

                            // Redirect to the old page
                            $application->redirect($page, $error_msg, 'error');
                            $application->close();
                            return;
                        }
                    }

                    // If no redirect could be given, do not handle this at all, but just set an error
                    $application->enqueueMessage($error_msg, 'error');
                }

            }
        }

        return $tmp_files;
    }

    /*
     * Cleanup temporary uploads
     *
     * @param null
     * @return bool
     */
    public static function cleanup($tmp_files)
    {
        if (count($tmp_files) > 0) {
            foreach ($tmp_files as $tmp_file) {
                if (is_file($tmp_file)) {
                    jimport('joomla.filesystem.file');
                    JFile::delete($tmp_file);
                }
            }
        }

        return true;
    }
}
