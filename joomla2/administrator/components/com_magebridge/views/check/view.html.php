<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2013
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// Check to ensure this file is included in Joomla!  
defined('_JEXEC') or die();

// Require the parent view
require_once JPATH_COMPONENT.'/view.php';

/**
 * HTML View class 
 *
 * @static
 * @package MageBridge
 */
class MageBridgeViewCheck extends YireoView
{
    protected $loadToolbar = false;

    /*
     * List of all checks
     */
    private $_checks = array();

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function display($tpl = null)
    {
        if (JRequest::getCmd('layout') == 'browser') {
            $this->displayBrowser($tpl);

        } else if (JRequest::getCmd('layout') == 'result') {
            $this->displayResult($tpl);

        } else {
            $this->displayDefault($tpl);
        }
    }

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function displayDefault($tpl)
    {
        // Initalize common elements
        MageBridgeViewHelper::initialize('System Check');

        // Load libraries
        JHTML::_('behavior.tooltip');

        JToolBarHelper::custom( 'refresh', 'preview.png', 'preview_f2.png', 'Refresh', false );

        $checks = $this->get('checks');
        $this->assignRef('checks', $checks);
        parent::display($tpl);
    }

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function displayBrowser($tpl)
    {
        // Initalize common elements
        MageBridgeViewHelper::initialize('Internal Browse Test');

        JToolBarHelper::custom( 'refresh', 'preview.png', 'preview_f2.png', 'Browse', false );

        $url = MagebridgeModelConfig::load('url').'magebridge.php';
        $host = MagebridgeModelConfig::load('host');

        $this->assignRef('url', $url);
        $this->assignRef('host', $host);
        parent::display('browser');
    }

    /*
     * Display method
     *
     * @param string $tpl
     * @return null
     */
    public function displayResult($tpl)
    {
        // Fetch configuration data
        $url = MagebridgeModelConfig::load('url').'magebridge.php';
        $host = MagebridgeModelConfig::load('host');

        // Do basic resolving on the host if it is not an IP-address
        if (preg_match('/^([0-9\.]+)$/', $host) == false) {
            $host = preg_replace('/\:[0-9]+$/', '', $host);
            if (gethostbyname($host) == $host) {
                die('ERROR: Failed to resolve hostname "'.$host.'" in DNS');
            }
        }

        // Try to open a socket to port 80
        if (@fsockopen($host, 80, $errno, $errmsg, 5) == false) {
            die('ERROR: Failed to open a connection to host "'.$host.'" on port 80. Perhaps a firewall is in the way?');
        }

        // Try to open a socket to port 443
        //if (@fsockopen($host, 443, $errno, $errmsg, 5) == false) {
        //    die('ERROR: Failed to open a socket to host "'.$host.'" on port 443. Perhaps a firewall is in the way?');
        //}

        // Initialize the proxy
        $proxy = MageBridgeModelProxy::getInstance();
        $proxy->setAllowRedirects(false);
        $content = $proxy->getRemote($url, array('mbtest' => '1'), 'post');

        // Detect proxy errors
        $proxy_error = $proxy->getProxyError();
        if (!empty($proxy_error)) {
            die('ERROR: Proxy error: '.$proxy_error);
        }


        // Detect the HTTP status
        $http_status = $proxy->getHttpStatus();
        if ($http_status != 200) {
            die('ERROR: Encountered a HTTP Status '.$http_status);
        }

        // Parse the content
        if (!empty($content)) {

            // Detect HTML-page
            if (preg_match('/\<\/html\>$/', $content)) {
                die('ERROR: Data contains HTML not JSON');
            }

            $data = json_decode($content, true);
            if (!empty($data)) {
                if (array_key_exists('mbtest', $data)) {
                    die('SUCCESS: Magento-side of the bridge is available');
                }
                die('ERROR: JSON response contains unknown data: '.var_export($data, true));

            } else {
                die('ERROR: Failed to decode JSON');
            }
        } else {
            die('ERROR: Empty content');
        }
    }
}
