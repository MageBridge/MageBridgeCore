<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewConfig extends YireoCommonView
{
    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        // Load important variables
        $layout = JRequest::getCmd('layout');

        // Initalize common elements
        MageBridgeViewHelper::initialize('Configuration');

        // Load the import-layout directly
        if ($layout == 'import') {
            return parent::display($layout);
        }

        // Load the tabs
        jimport('joomla.html.pane');
        if(class_exists('JPane')) {
            $activeTab = $this->application->getUserStateFromRequest( $this->_option.'.tab', 'tab', 1, 'int' );
            $pane = JPane::getInstance('tabs', array('startOffset' => $activeTab));
            $this->assignRef('pane', $pane);
        } else {
            $pane = false;
            $this->assignRef('pane', $pane);
        }

        // Toolbar options
        if (MageBridgeAclHelper::isDemo() == false) JToolBarHelper::custom( 'export', 'export.png', null, 'Export', false );
        if (MageBridgeAclHelper::isDemo() == false) JToolBarHelper::custom( 'import', 'import.png', null, 'Import', false );
        if (MageBridgeHelper::isJoomla15() == false) JToolBarHelper::preferences('com_magebridge');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::cancel();

        // Extra scripts
        MageBridgeTemplateHelper::load('jquery');
        $this->addJs('backend-config.js');

        // Before loading anything, we build the bridge
        $this->preBuildBridge();

        // Load the configuration and check it
        $config = MagebridgeModelConfig::load();
        $this->checkConfig();

        // Make sure demo-users are not seeing any sensitive data
        if (MageBridgeAclHelper::isDemo() == true) {
            $censored_values = array('supportkey', 'api_user', 'api_key');
            foreach ($censored_values as $censored_value) {
                $config[$censored_value]['value'] = str_repeat('*', strlen($config[$censored_value]['value']));
            }
        }

        // Instantiate the form
        $configData = array('config' => array());
        foreach($config as $name => $configValue) {
            $configData['config'][$name] = $configValue['value'];
        }
        $formFile = JPATH_SITE.'/components/com_magebridge/models/config.xml';
        $form = JForm::getInstance('config', $formFile);
        $form->bind($configData);
		$this->assignRef('form', $form);
        
        // Generate input fields
        $fields = array();
        $fields['disable_css'] = $this->getFieldDisableCss();
        $fields['disable_js_mage'] = $this->getFieldDisableJsMage();
        $fields['disable_js_all'] = $this->getFieldDisableJsJoomla();
        $fields['website'] = $this->getFieldWebsite();
        $fields['customer_group'] = $this->getFieldCustomerGroup();
        $fields['usergroup'] = $this->getFieldUsergroup();
        $fields['encryption'] = JHTML::_('select.booleanlist', 'encryption', null, $config['encryption']['value']);
        $fields['enable_sso'] = JHTML::_('select.booleanlist', 'enable_sso', null, $config['enable_sso']['value']);
        $fields['enable_usersync'] = JHTML::_('select.booleanlist', 'enable_usersync', null, $config['enable_usersync']['value']);
        $fields['username_from_email'] = JHTML::_('select.booleanlist', 'username_from_email', null, $config['username_from_email']['value']);
        $fields['realname_from_firstlast'] = JHTML::_('select.booleanlist', 'realname_from_firstlast', null, $config['realname_from_firstlast']['value']);
        $fields['realname_with_space'] = JHTML::_('select.booleanlist', 'realname_with_space', null, $config['realname_with_space']['value']);
        $fields['enable_auth_backend'] = JHTML::_('select.booleanlist', 'enable_auth_backend', null, $config['enable_auth_backend']['value']);
        $fields['enable_auth_frontend'] = JHTML::_('select.booleanlist', 'enable_auth_frontend', null, $config['enable_auth_frontend']['value']);
        $fields['enable_canonical'] = JHTML::_('select.booleanlist', 'enable_canonical', null, $config['enable_canonical']['value']);
        $fields['protocol'] = $this->getFieldProtocol();
        $fields['method'] = $this->getFieldMethod();
        $fields['http_auth'] = JHTML::_('select.booleanlist', 'http_auth', null, $config['http_auth']['value']);
        $fields['http_authtype'] = $this->getFieldHttpAuthType();
        $fields['backend'] = $this->getFieldBackend();
        $fields['template'] = $this->getFieldTemplate();
        $fields['enforce_ssl'] = $this->getFieldEnforceSSL();
        $fields['update_format'] = $this->getFieldUpdateFormat();
        $fields['update_method'] = $this->getFieldUpdateMethod();
        $fields['debug_log'] = $this->getFieldDebugLog();
        $fields['debug_level'] = $this->getFieldDebugLevel();
        $fields['mobile_magento_theme'] = $this->getFieldMobileMagentoTheme();
        $fields['mobile_joomla_theme'] = $this->getFieldMobileJoomlaTheme();
        $fields['merge_js'] = $this->getFieldMergeJs();
        $fields['users_website_id'] = $this->getFieldUsersWebsiteId();
        $fields['users_group_id'] = $this->getFieldUsersGroupId();
        $fields['api_type'] = $this->getFieldApiType();
        $fields['api_widgets'] = JHTML::_('select.booleanlist', 'api_widgets', null, $config['api_widgets']['value']);
        $fields['preload_all_modules'] = JHTML::_('select.booleanlist', 'preload_all_modules', null, $config['preload_all_modules']['value']);
        $fields['use_rootmenu'] = JHTML::_('select.booleanlist', 'use_rootmenu', null, $config['use_rootmenu']['value']);
        $fields['enforce_rootmenu'] = JHTML::_('select.booleanlist', 'enforce_rootmenu', null, $config['enforce_rootmenu']['value']);
        $fields['enable_cache'] = JHTML::_('select.booleanlist', 'enable_cache', null, $config['enable_cache']['value']);
        $fields['enable_content_plugins'] = JHTML::_('select.booleanlist', 'enable_content_plugins', null, $config['enable_content_plugins']['value']);
        $fields['enable_block_rendering'] = JHTML::_('select.booleanlist', 'enable_block_rendering', null, $config['enable_block_rendering']['value']);
        $fields['enable_jdoc_tags'] = JHTML::_('select.booleanlist', 'enable_jdoc_tags', null, $config['enable_jdoc_tags']['value']);
        $fields['disable_default_css'] = JHTML::_('select.booleanlist', 'disable_default_css', null, $config['disable_default_css']['value']);
        $fields['disable_js_mootools'] = JHTML::_('select.booleanlist', 'disable_js_mootools', null, $config['disable_js_mootools']['value']);
        $fields['disable_js_footools'] = JHTML::_('select.booleanlist', 'disable_js_footools', null, $config['disable_js_footools']['value']);
        $fields['disable_js_frototype'] = JHTML::_('select.booleanlist', 'disable_js_frototype', null, $config['disable_js_frototype']['value']);
        $fields['disable_js_jquery'] = JHTML::_('select.booleanlist', 'disable_js_jquery', null, $config['disable_js_jquery']['value']);
        $fields['disable_js_prototype'] = JHTML::_('select.booleanlist', 'disable_js_prototype', null, $config['disable_js_prototype']['value']);
        $fields['use_google_api'] = JHTML::_('select.booleanlist', 'use_google_api', null, $config['use_google_api']['value']);
        $fields['use_protoaculous'] = JHTML::_('select.booleanlist', 'use_protoaculous', null, $config['use_protoaculous']['value']);
        $fields['use_protoculous'] = JHTML::_('select.booleanlist', 'use_protoculous', null, $config['use_protoculous']['value']);
        $fields['bridge_cookie_all'] = JHTML::_('select.booleanlist', 'bridge_cookie_all', null, $config['bridge_cookie_all']['value']);
        $fields['offline'] = JHTML::_('select.booleanlist', 'offline', null, $config['offline']['value']);
        $fields['debug'] = JHTML::_('select.booleanlist', 'debug', null, $config['debug']['value']);
        $fields['debug_bar'] = JHTML::_('select.booleanlist', 'debug_bar', null, $config['debug_bar']['value']);
        $fields['debug_console'] = JHTML::_('select.booleanlist', 'debug_console', null, $config['debug_console']['value']);
        $fields['debug_bar_parts'] = JHTML::_('select.booleanlist', 'debug_bar_parts', null, $config['debug_bar_parts']['value']);
        $fields['debug_bar_request'] = JHTML::_('select.booleanlist', 'debug_bar_request', null, $config['debug_bar_request']['value']);
        $fields['debug_bar_store'] = JHTML::_('select.booleanlist', 'debug_bar_store', null, $config['debug_bar_store']['value']);
        $fields['debug_display_errors'] = JHTML::_('select.booleanlist', 'debug_display_errors', null, $config['debug_display_errors']['value']);
        $fields['enable_messages'] = JHTML::_('select.booleanlist', 'enable_messages', null, $config['enable_messages']['value']);
        $fields['enable_breadcrumbs'] = JHTML::_('select.booleanlist', 'enable_breadcrumbs', null, $config['enable_breadcrumbs']['value']);
        $fields['enable_notfound'] = JHTML::_('select.booleanlist', 'enable_notfound', null, $config['enable_notfound']['value']);
        $fields['modify_url'] = JHTML::_('select.booleanlist', 'modify_url', null, $config['modify_url']['value']);
        $fields['link_to_magento'] = JHTML::_('select.booleanlist', 'link_to_magento', null, $config['link_to_magento']['value']);
        $fields['spoof_browser'] = JHTML::_('select.booleanlist', 'spoof_browser', null, $config['spoof_browser']['value']);
        $fields['spoof_headers'] = JHTML::_('select.booleanlist', 'spoof_headers', null, $config['spoof_headers']['value']);
        $fields['curl_post_as_array'] = JHTML::_('select.booleanlist', 'curl_post_as_array', null, $config['curl_post_as_array']['value']);
        $fields['backend_feed'] = JHTML::_('select.booleanlist', 'backend_feed', null, $config['backend_feed']['value']);
        $fields['keep_alive'] = JHTML::_('select.booleanlist', 'keep_alive', null, $config['keep_alive']['value']);
        $fields['filter_content'] = JHTML::_('select.booleanlist', 'filter_content', null, $config['filter_content']['value']);
        $fields['filter_store_from_url'] = JHTML::_('select.booleanlist', 'filter_store_from_url', null, $config['filter_store_from_url']['value']);
        $fields['use_referer_for_homepage_redirects'] = JHTML::_('select.booleanlist', 'use_referer_for_homepage_redirects', null, $config['use_referer_for_homepage_redirects']['value']);
        $fields['use_homepage_for_homepage_redirects'] = JHTML::_('select.booleanlist', 'use_homepage_for_homepage_redirects', null, $config['use_homepage_for_homepage_redirects']['value']);

        $this->assignRef('config', $config);
        $this->assignRef('fields', $fields);

        parent::display($tpl);
    }

    /*
     * Method to check the configuration and generate warnings if needed
     *
     * @param null
     * @return null
     */
    public function checkConfig()
    {
        // Check if the settings are all empty
        if (MagebridgeModelConfig::allEmpty() == true) {
            JError::raiseWarning( 500, JText::sprintf( 'Check the online %s for more information.', MageBridgeHelper::getHelpText('quickstart')));
            return;
        }

        // Otherwise check all values
        $config = MagebridgeModelConfig::load();
        foreach ($config as $c) {
            if (isset($c['name']) && isset($c['value']) && $message = MageBridge::getConfig()->check($c['name'], $c['value'])) {
                JError::raiseWarning( 500, $message );
            }
        }

        return;
    }

    /*
     * Get the HTML-field for a custom field
     *
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function getCustomField($type, $name)
    {
        if (MageBridgeHelper::isJoomla15()) {
            require_once JPATH_COMPONENT.'/elements/'.$type.'.php';
            $fake = null;
            $class = 'JElement'.ucfirst($type);
            $object = new $class();
            return $object->fetchElement($name, MagebridgeModelConfig::load($name), $fake, '');
        } else {
            require_once JPATH_COMPONENT.'/fields/'.$type.'.php';
            jimport('joomla.form.helper');
            $field = JFormHelper::loadFieldType($type);
            $field->setName($name);
            $field->setValue(MagebridgeModelConfig::load($name));
            return $field->getHtmlInput();
        }
    }

    /*
     * Get the HTML-field for the API protocol
     *
     * @param null
     * @return string
     */
    public function getFieldProtocol()
    {
        $options = array(
            array( 'value' => 'http', 'text' => JText::_('HTTP') ),
            array( 'value' => 'https', 'text' => JText::_('HTTPS') ),
        );
        return JHTML::_('select.genericlist', $options, 'protocol', null, 'value', 'text', MagebridgeModelConfig::load('protocol'));
    }

    /*
     * Get the HTML-field for the API method
     *
     * @param null
     * @return string
     */
    public function getFieldMethod()
    {
        $options = array(
            array( 'value' => 'post', 'text' => JText::_('POST') ),
            array( 'value' => 'get', 'text' => JText::_('GET') ),
        );
        return JHTML::_('select.genericlist', $options, 'method', 'disabled="disabled"', 'value', 'text', MagebridgeModelConfig::load('method'));
    }

    /*
     * Get the HTML-field for the Magento Website
     *
     * @param null
     * @return string
     */
    public function getFieldWebsite()
    {
        return $this->getCustomField('website', 'website');
    }

    /*
     * Get the HTML-field for the Magento Website (users import/export)
     *
     * @param null
     * @return string
     */
    public function getFieldUsersWebsiteId()
    {
        return $this->getCustomField('website', 'users_website_id');
    }

    /*
     * Get the HTML-field for the Magento customer group
     *
     * @param null
     * @return string
     */
    public function getFieldCustomerGroup()
    {
        return $this->getCustomField('customergroup', 'customer_group');
    }

    /*
     * Get the HTML-field for the Joomla! usergroup
     *
     * @param null
     * @return string
     */
    public function getFieldUsergroup()
    {
        $usergroups = MageBridgeFormHelper::getUsergroupOptions();
        return JHTML::_('select.genericlist', $usergroups, 'usergroup', null, 'value', 'text', MagebridgeModelConfig::load('usergroup'));
    }

    /*
     * Get the HTML-field for the Magento customer group (users import/export)
     *
     * @param null
     * @return string
     */
    public function getFieldUsersGroupId()
    {
        return $this->getCustomField('customergroup', 'users_group_id');
    }

    /*
     * Get the HTML-field for the Magento CSS-sheets
     *
     * @param null
     * @return string
     */
    public function getFieldDisableCss()
    {
        return $this->getCustomField('stylesheets', 'stylesheets');
    }

    /*
     * Get the HTML-field for the Joomla! JavaScripts
     *
     * @param null
     * @return string
     */
    public function getFieldDisableJsJoomla()
    {
        $options = array(
            array( 'value' => 0, 'text' => JText::_('No')),
            array( 'value' => 1, 'text' => JText::_('Yes')),
            array( 'value' => 2, 'text' => JText::_('Only')),
            array( 'value' => 3, 'text' => JText::_('All except')),
        );

        foreach ($options as $index => $option) {
            $options[$index] = JArrayHelper::toObject($option);
        }

        $current = MagebridgeModelConfig::load('disable_js_all');
        if ($current == 1 || $current == 0) { 
            $disabled = 'disabled="disabled"';
        } else {
            $disabled = null;
        }

        $html = '';
        $html = JHTML::_('select.radiolist', $options, 'disable_js_all', null, 'value', 'text', $current);
        $html .= '<br/>';
        $html .= '<textarea type="text" id="disable_js_custom" name="disable_js_custom" '.$disabled
            . 'rows="5" cols="40" maxlength="255">'
            . MagebridgeModelConfig::load('disable_js_custom')
            . '</textarea>';
        return $html;
    }

    /*
     * Get the HTML-field for the Magento JavaScripts
     *
     * @param null
     * @return string
     */
    public function getFieldDisableJsMage()
    {
        return $this->getCustomField('scripts', 'disable_js_mage');
    }

    /*
     * Get the HTML-field for the Magento mobile theme
     *
     * @param null
     * @return string
     */
    public function getFieldMobileMagentoTheme()
    {
        return $this->getCustomField('theme', 'mobile_magento_theme');
    }

    /*
     * Get the HTML-field for the mobile Joomla! theme
     *
     * @param null
     * @return string
     */
    public function getFieldMobileJoomlaTheme()
    {
        $rows = $this->getTemplateOptions();
        if (MageBridgeHelper::isJoomla15()) {
            $name = 'directory';
            $value = 'name';
        } else {
            $name = 'value';
            $value = 'text';
        }
        return JHTML::_('select.genericlist', $rows, 'mobile_joomla_theme', null, $name, $value, MagebridgeModelConfig::load('mobile_joomla_theme'));
    }

    /*
     * Get the HTML-field for the path to the Magento Admin Panel
     *
     * @param null
     * @return string
     */
    public function getFieldBackend()
    {
        return $this->getCustomField('backend', 'backend');
    }

    /*
     * Get the HTML-field for the Joomla! template
     *
     * @param null
     * @return string
     */
    public function getFieldTemplate()
    {
        $rows = $this->getTemplateOptions();
        if (MageBridgeHelper::isJoomla15()) {
            $name = 'directory';
            $value = 'name';
        } else {
            $name = 'value';
            $value = 'text';
        }
        return JHTML::_('select.genericlist', $rows, 'template', null, $name, $value, MagebridgeModelConfig::load('template'));
    }

    /*
     * Get the HTML-field for the HTTP Authentication method-type
     *
     * @param null
     * @return string
     */
    public function getFieldHttpAuthType()
    {
        $options = array(
            array( 'value' => CURLAUTH_ANY, 'text' => 'CURLAUTH_ANY'),
            array( 'value' => CURLAUTH_ANYSAFE, 'text' => 'CURLAUTH_ANYSAFE'),
            array( 'value' => CURLAUTH_BASIC, 'text' => 'CURLAUTH_BASIC'),
            array( 'value' => CURLAUTH_DIGEST, 'text' => 'CURLAUTH_DIGEST'),
            array( 'value' => CURLAUTH_GSSNEGOTIATE, 'text' => 'CURLAUTH_GSSNEGOTIATE'),
            array( 'value' => CURLAUTH_NTLM, 'text' => 'CURLAUTH_HTLM'),
        );
        return JHTML::_('select.genericlist', $options, 'http_authtype', null, 'value', 'text', MagebridgeModelConfig::load('http_authtype'));
    }

    /*
     * Get the HTML-field for the debugging-log
     *
     * @param null
     * @return string
     */
    public function getFieldDebugLog()
    {
        $options = array(
            array( 'value' => 'db', 'text' => JText::_('Database') ),
            array( 'value' => 'file', 'text' => JText::_('File').' logs/magebridge.txt'),
            array( 'value' => 'both', 'text' => JText::_('Both')),
        );
        return JHTML::_('select.genericlist', $options, 'debug_log', null, 'value', 'text', MagebridgeModelConfig::load('debug_log'));
    }

    /*
     * Get the HTML-field for the debugging-level
     *
     * @param null
     * @return string
     */
    public function getFieldDebugLevel()
    {
        $options = array(
            array( 'value' => 'all', 'text' => JText::_('All') ),
            array( 'value' => 'error', 'text' => JText::_('Error') ),
            array( 'value' => 'profiler', 'text' => JText::_('Profiler') ),
        );
        return JHTML::_('select.genericlist', $options, 'debug_level', null, 'value', 'text', MagebridgeModelConfig::load('debug_level'));
    }

    /*
     * Get the HTML-field for the Merge JavaScript setting
     *
     * @param null
     * @return string
     */
    public function getFieldMergeJs()
    {
        $options = array(
            array( 'value' => 0, 'text' => JText::_('No')),
            array( 'value' => 1, 'text' => JText::_('Yes, through js/index.php')),
            array( 'value' => 2, 'text' => JText::_('Yes, through Magento merge')),
        );
        return JHTML::_('select.genericlist', $options, 'merge_js', null, 'value', 'text', MagebridgeModelConfig::load('merge_js'));
    }

    /*
     * Get the HTML-field for the Enforce SSL setting
     *
     * @param null
     * @return string
     */
    public function getFieldEnforceSSL()
    {
        $options = array(
            array( 'value' => 0, 'text' => JText::_('None')),
            array( 'value' => 1, 'text' => JText::_('Entire Joomla! site')),
            array( 'value' => 2, 'text' => JText::_('Shop only')),
            array( 'value' => 3, 'text' => JText::_('Checkout and customer-pages only (EXPERIMENTAL)')),
        );
        return JHTML::_('select.genericlist', $options, 'enforce_ssl', null, 'value', 'text', MagebridgeModelConfig::load('enforce_ssl'));
    }

    /*
     * Get the HTML-field for the API Type parameter
     *
     * @param null
     * @return string
     */
    public function getFieldApiType()
    {
        $options = array(
            array( 'value' => 'jsonrpc', 'text' => 'JSON-RPC' ),
        );
        return JHTML::_('select.genericlist', $options, 'api_type', null, 'value', 'text', MagebridgeModelConfig::load('api_type'));
    }

    /*
     * Get the HTML-field for the Update Format parameter
     *
     * @param null
     * @return string
     */
    public function getFieldUpdateFormat()
    {
        $options = array(
            //array( 'value' => 'tar', 'text' => 'tar' ),
            array( 'value' => 'tar.gz', 'text' => 'tar.gz' ),
            array( 'value' => 'zip', 'text' => 'zip' ),
        );
        return JHTML::_('select.genericlist', $options, 'update_format', null, 'value', 'text', MagebridgeModelConfig::load('update_format'));
    }

    /*
     * Get the HTML-field for the Update Method parameter
     *
     * @param null
     * @return string
     */
    public function getFieldUpdateMethod()
    {
        $options = array(
            array( 'value' => 'joomla', 'text' => 'Joomla! core (fopen)' ),
            array( 'value' => 'curl', 'text' => 'MageBridge (CURL)' ),
        );
        return JHTML::_('select.genericlist', $options, 'update_method', null, 'value', 'text', MagebridgeModelConfig::load('update_method'));
    }

    /*
     * Shortcut method to build the bridge for this page
     *
     * @param null
     * @return null
     */
    public function preBuildBridge()
    {
        // Register the needed segments
        $register = MageBridgeModelRegister::getInstance();
        $register->add('headers');
        $register->add('api', 'customer_group.list');
        $register->add('api', 'magebridge_websites.list');

        // Build the bridge and collect all segments
        $bridge = MageBridge::getBridge();
        $bridge->build();
    }

    /*
     * Method to build a list of template-options
     *
     * @param null
     * @return array
     */
    protected function getTemplateOptions()
    {
        // Get the template-options
        if (MageBridgeHelper::isJoomla15()) {
            require_once(JPATH_ADMINISTRATOR.'/components/com_templates/helpers/template.php');
            $options = TemplatesHelper::parseXMLTemplateFiles(JPATH_SITE.'/templates');
        } else {
            require_once(JPATH_ADMINISTRATOR.'/components/com_templates/helpers/templates.php');
            $options = TemplatesHelper::getTemplateOptions(0);
        }

        // Construct an empty option
        $option = new stdClass;
        if (MageBridgeHelper::isJoomla15()) {
            $option->directory = null;
            $option->name = null;
        } else {
            $option->value = null;
            $option->text = null;
        }
        array_unshift($options, $option);

        // Return the options
        return $options;
    }

    /*
     * Method to get all the different tabs
     */
    public function getTabs()
    {
        $tabs= array();
        return $tabs;
    }

    /*
     * Method to print a specific tab
     * @deprecated
     */
    public function printTab($name, $id, $template)
    {
        if($this->pane) {
            echo $this->pane->startPanel($name, $id);
            echo $this->loadTemplate($template);
            echo $this->pane->endPanel();
        } else {
		    echo '<div class="tab-pane" id="'.$id.'">';
            echo $this->loadTemplate($template);
            echo '</div>';
        }
    }

    /*
     * Method to print a specific fieldset
     */
    public function printFieldset($form, $fieldset)
    {
        if ($this->pane) {
            echo $this->pane->startPanel(JText::_($fieldset->label), $fieldset->name);
            foreach($form->getFieldset($fieldset->name) as $field) {
                echo $this->loadTemplate('field', array('field' => $field));
            }
            echo $this->pane->endPanel();
        } else {
		    echo '<div class="tab-pane" id="'.$fieldset->name.'">';
            foreach($form->getFieldset($fieldset->name) as $field) {
                echo $this->loadTemplate('field', array('field' => $field));
            }
            echo '</div>';
        }
    }
}
