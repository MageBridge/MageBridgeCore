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

// Include the parent controller
require_once JPATH_COMPONENT . '/controller.php';

/**
 * MageBridge Controller
 */
class MageBridgeControllerUsers extends MageBridgeController
{
    /**
     * Method to export users to CSV
     *
     * @param null
     *
     * @return null
     */
    public function import()
    {
        $this->_app->input->set('layout', 'import');
        parent::display();
    }

    /**
     * Method to export users to CSV
     *
     * @param null
     *
     * @return null
     */
    public function export()
    {
        // Gather the variables
        $users = $this->getUserList();
        $website_id = MageBridgeModelConfig::load('users_website_id');
        $group_id = MageBridgeModelConfig::load('users_group_id');

        // Perform preliminary checks
        if (empty($users)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', JText::_('No users found'), 'error');

            return false;
        }

        if (empty($website_id)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', JText::_('Website not configured in export parameters'), 'error');

            return false;
        }

        if (empty($group_id)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users', JText::_('Customer Group not configured in export parameters'), 'error');

            return false;
        }

        $date = date('Ymd');
        $filename = 'magebridge-export-joomla-users_' . $date . '.csv';
        $output = $this->getOutput($users, $website_id, $group_id);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . strlen($output));
        header('Content-type: text/x-csv');
        header('Content-Disposition: attachment; filename=' . $filename);
        print $output;

        // Close the application
        $application = JFactory::getApplication();
        $application->close();
    }

    /**
     * Method to handle the upload of a new CSV-file
     *
     * @param null
     *
     * @return array
     */
    public function upload()
    {
        // Construct the needed variables
        $upload = $this->_app->input->getVar('csv', null, 'files');
        $user_records_ok = 0;
        $user_records_fail = 0;

        // Check whether this is a valid download
        if (empty($upload) || empty($upload['name']) || empty($upload['tmp_name']) || empty($upload['size'])) {
            $this->setRedirect('index.php?option=com_magebridge&view=users&task=import', JText::_('File upload failed on system level'), 'error');

            return false;
        }

        // Check for empty content
        $csv = @file_get_contents($upload['tmp_name']);
        if (empty($csv)) {
            $this->setRedirect('index.php?option=com_magebridge&view=users&task=import', JText::_('Empty file upload'), 'error');

            return false;
        }

        // Turn the CSV-content into a workable array
        $lines = explode("\n", $csv);

        if (!empty($lines)) {
            // Parse the header of this CSV file
            $header = $this->parseLine(array_shift($lines));

            // Extract usable user-fields from this header
            $email = array_search('email', $header);
            $firstname = array_search('firstname', $header);
            $lastname = array_search('lastname', $header);

            // Loop through the other lines to fetch the usable user-fields
            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }

                $fields = $this->parseLine($line);

                $user = [
                    'email' => $fields[$email],
                    'firstname' => $fields[$firstname],
                    'lastname' => $fields[$lastname],];
                $user = MageBridgeUserHelper::convert($user);
                $rt = MageBridgeModelUser::getInstance()->create($user, true);

                if ($rt == true) {
                    $user_records_ok++;
                } else {
                    $user_records_fail++;
                }
            }
        }

        $this->setRedirect('index.php?option=com_magebridge&view=users', JText::sprintf('Imported %d users succesfully, %d users failed', $user_records_ok, $user_records_fail));

        return true;
    }

    /**
     * Method to get all the users
     *
     * @param null
     *
     * @return array
     */
    private function getUserList()
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT u.* FROM #__users AS u");

        return $db->loadObjectList();
    }

    /**
     * Method to get all CSV output
     *
     * @param null
     *
     * @return null
     */
    private function getOutput($users, $website_id, $group_id)
    {
        $output = '"group_id","website","firstname","lastname","email"' . "\n";

        foreach ($users as $user) {
            $user = MageBridgeUserHelper::convert($user);

            $values = [];
            $values[] = $group_id;
            $values[] = $website_id;
            $values[] = $user->firstname;
            $values[] = $user->lastname;
            $values[] = $user->email;

            foreach ($values as $index => $value) {
                $value = '"' . str_replace('"', '\"', trim($value)) . '"';
                $values[$index] = $value;
            }

            $output .= implode(',', $values) . "\n";
        }

        return $output;
    }

    /**
     * Method to parse an individual CSV-line
     *
     * @param string
     *
     * @return array
     */
    private function parseLine($line)
    {
        $fields = explode(',', $line);

        if (!empty($fields)) {
            foreach ($fields as $index => $field) {
                $field = preg_replace('/^\"/', '', $field);
                $field = preg_replace('/\"$/', '', $field);
                $field = str_replace('\"', '"', $field);
                $fields[$index] = $field;
            }
        }

        return $fields;
    }
}
