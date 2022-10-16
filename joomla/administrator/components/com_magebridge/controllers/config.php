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

/**
 * MageBridge Controller
 */
class MageBridgeControllerConfig extends YireoCommonController
{
    /**
     * Handle the task 'cancel'
     */
    public function cancel()
    {
        // Redirect back to the form-page
        return $this->setRedirect(JRoute::_('index.php?option=com_magebridge'), $this->msg, $this->msg_type);
    }

    /**
     * Handle the task 'save'
     */
    public function save()
    {
        // Security check
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Store the data
        $this->store();

        // Redirect back to the form-page
        return $this->setRedirect('index.php?option=com_magebridge', $this->msg, $this->msg_type);
    }

    /**
     * Handle the task 'apply'
     */
    public function apply()
    {
        // Security check
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Store the data
        $this->store();

        // Redirect back to the form-page
        return $this->setRedirect('index.php?option=com_magebridge&view=config', $this->msg, $this->msg_type);
    }

    /**
     * Extend the default store-method
     *
     * @param array $post
     *
     * @return null
     */
    public function store($post = [])
    {
        // Security check
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Validate whether this task is allowed
        if ($this->_validate(true, true) == false) {
            return false;
        }

        // Fetch the POST-data
        $post = $this->_app->input->post->getArray();
        $post = $this->fixPost($post);

        // Get the model
        $model = $this->getModel('config');

        // Store these data with the model
        if ($model->store($post)) {
            $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_SAVED', $this->_app->input->getCmd('view'));

            return true;
        }

        $this->msg = JText::sprintf('LIB_YIREO_CONTROLLER_ITEM_NOT_SAVED', $this->_app->input->getCmd('view'));
        $error = $model->getError();

        if (!empty($error)) {
            $this->msg .= ': ' . $error;
        }

        $this->msg_type = 'error';

        return false;
    }

    /**
     * @param $post
     *
     * @return mixed
     */
    protected function fixPost($post)
    {
        $post['api_key'] = $this->_app->input->post->get('api_key', '', 'raw');
        $post['api_user'] = $this->_app->input->post->get('api_user', '', 'raw');

        // Override with new JForm-output (temp)
        if (isset($post['config'])) {
            foreach ($post['config'] as $name => $value) {
                $post[$name] = $value;
            }

            unset($post['config']);
        }

        return $post;
    }

    /**
     * Method to import configuration from XML
     */
    public function import()
    {
        $this->_app->input->set('layout', 'import');

        parent::display();
    }

    /**
     * Method to export configuration to XML
     */
    public function export()
    {
        // Gather the variables
        $config = MageBridgeModelConfig::load();

        $date = date('Ymd');
        $host = str_replace('.', '_', $_SERVER['HTTP_HOST']);
        $filename = 'magebridge-joomla-' . $host . '-' . $date . '.xml';
        $output = $this->getOutput($config);

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . strlen($output));
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename=' . $filename);
        print $output;

        // Close the application
        $application = $this->_app;
        $application->close();
    }

    /**
     * @param $upload
     *
     * @return bool
     */
    protected function isValidUpload($upload)
    {
        if (empty($upload)) {
            return false;
        }

        if (empty($upload['name'])) {
            return false;
        }

        if (empty($upload['tmp_name'])) {
            return false;
        }

        if (empty($upload['size'])) {
            return false;
        }

        return true;
    }

    /**
     * Method to handle the upload of a new CSV-file
     *
     * @return array
     */
    public function upload()
    {
        // Construct the needed variables
        $upload = $this->_app->input->get('xml', null, 'files');

        // Check whether this is a valid download
        if ($this->isValidUpload($upload) == false) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', JText::_('File upload failed on system level'), 'error');

            return false;
        }

        // Check for empty content
        $xmlString = @file_get_contents($upload['tmp_name']);

        if (empty($xmlString)) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', JText::_('Empty file upload'), 'error');

            return false;
        }

        $xml = @simplexml_load_string($xmlString);

        if (!$xml) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', JText::_('Invalid XML-configuration'), 'error');

            return false;
        }

        $config = [];

        foreach ($xml->children() as $parameter) {
            $name = (string) $parameter->name;
            $value = (string) $parameter->value;
            if (!empty($name)) {
                $config[$name] = $value;
            }
        }

        if (empty($config)) {
            $this->setRedirect('index.php?option=com_magebridge&view=config&task=import', JText::_('Nothing to import'), 'error');

            return false;
        }

        MageBridgeModelConfig::getSingleton()->store($config);
        $this->setRedirect('index.php?option=com_magebridge&view=config', JText::_('Imported configuration succesfully'));

        return true;
    }

    /**
     * Method to get all XML output
     */
    private function getOutput($config)
    {
        $xml = null;

        if (!empty($config)) {
            $xml .= "<configuration>\n";

            foreach ($config as $c) {
                $xml .= "	<parameter>\n";
                $xml .= "		<id>" . $c['id'] . "</id>\n";
                $xml .= "		<name>" . $c['name'] . "</name>\n";
                $xml .= "		<value><![CDATA[" . $c['value'] . "]]></value>\n";
                $xml .= "	</parameter>\n";
            }

            $xml .= "</configuration>\n";
        }

        return $xml;
    }

    /**
     * Method to validate a change-request
     *
     * @param boolean $check_token
     * @param boolean $check_demo
     *
     * @return boolean
     */
    protected function _validate($check_token = true, $check_demo = true)
    {
        // Check the token
        if ($check_token == true && (JSession::checkToken('post') == false && JSession::checkToken('get') == false)) {
            $msg = JText::_('JINVALID_TOKEN');
            $link = 'index.php?option=com_magebridge&view=home';
            $this->setRedirect($link, $msg);

            return false;
        }

        // Check demo-access
        if ($check_demo == true && MageBridgeAclHelper::isDemo() == true) {
            $msg = JText::_('LIB_YIREO_CONTROLLER_DEMO_NO_ACTION');
            $link = 'index.php?option=com_magebridge&view=config';
            $this->setRedirect($link, $msg);

            return false;
        }

        return true;
    }
}
