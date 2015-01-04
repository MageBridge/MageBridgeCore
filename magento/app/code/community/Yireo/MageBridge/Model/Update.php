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
 * MageBridge model handling MageBridge updates
 */
class Yireo_MageBridge_Model_Update extends Mage_Core_Model_Abstract
{
    /*
     * Current MageBridge-version
     */
    private $_current_version = null;

    /*
     * Available MageBridge-version
     */
    private $_new_version = null;

    /*
     * Remote downloader URL
     */
    private $_remote_url = 'http://api.yireo.com/';

    /*
     * Remote domain
     */
    private $_remote_domain = 'api.yireo.com';

    /*
     * Method to get the download-link
     *
     * @access public
     * @param array $arguments
     * @return string
     */
    public function getApiLink($arguments = array())
    {
        $arguments = array_merge($this->getApiArguments(), $arguments);

        foreach($arguments as $name => $value) {
            if($name == 'request') {
                $arguments[$name] = "$value";
            } else {
                $arguments[$name] = "$name,$value";
            }
        }

        return $this->_remote_url . implode('/', $arguments);
    }

    /*
     * Method to get the arguments for the API-link
     *
     * @access public
     * @param null
     * @return array
     */
    public function getApiArguments()
    {
        $domain = preg_replace( '/\:(.*)/', '', $_SERVER['HTTP_HOST'] );
        return array(
            'license' => $this->getLicenseKey(),
            'domain' => $domain,
        );
    }

    /*
     * Method to determine whether an upgrade is needed or not
     *
     * @access public
     * @param null
     * @return bool
     */
    public function upgradeNeeded()
    {
        if(version_compare($this->getNewVersion(), $this->getCurrentVersion(), '>')) {
            return true;
        }
        return true;
    }

    /*
     * Method to perform the actual upgrade
     *
     * @access public
     * @param null
     * @return string
     */
    public function doUpgrade()
    {
        // File format
        $format = 'zip';

        // Set the umask
        $this->setFilesUmask();
        
        // Simple check for ZIP-support
        if($format == 'zip' && !class_exists('ZipArchive') && !class_exists('PharData')) {
            $msg = 'WARNING: PHP-classes ZipArchive and PharData are missing. Updates might fail.';
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        // Simple check for TAR-support
        if($format == 'tgz' && !class_exists('PharData')) {
            $msg = 'WARNING: PHP-class PharData is missing.';
            Mage::getSingleton('adminhtml/session')->addError($msg);
        }

        // Check for the file
        $tmpdir = Mage::getConfig()->getOptions()->getTmpDir();
        if($format == 'tgz') $tmpfile = $tmpdir.DS.'Yireo_MageBridge_patch.tgz';
        if($format == 'zip') $tmpfile = $tmpdir.DS.'Yireo_MageBridge_patch.zip';

        // Make sure it does not contain just an error
        if(is_readable($tmpfile)) {
            $tmpcontents = file_get_contents($tmpfile);
            if(strstr($tmpcontents, 'Download denied')) {
                @unlink($tmpfile);
            }
        }
        
        // If the file is not there, download it
        if(is_readable($tmpfile) == false) {

            // Check whether the tmpdir is writable
            if(!is_writable($tmpdir)) {
                $msg = 'ERROR: '.$tmpdir.' is not writable';
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return $msg;
            }

            // Construct the download-URL
            if($format == 'tgz') $download_file = 'Yireo_MageBridge_patch.tgz';
            if($format == 'zip') $download_file = 'Yireo_MageBridge_patch.zip';
            $download_url = $this->getApiLink(array('resource' => 'download', 'request' => $download_file));

            // Get the remote data
            $data = $this->_getRemote($download_url);
            if(empty($data)) {
                $msg = 'ERROR: Downloaded update-file is empty';
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return $msg;
            }

            // Fill the local ZIP-file with the remote data 
            file_put_contents($tmpfile, $data);
            chmod($tmpfile, 0644);
        }

        // Check the filesize of the patch
        $contents = file_get_contents($tmpfile);
        if(empty($contents)) {
            Mage::getSingleton('adminhtml/session')->addError('ERROR: Downloaded archive is empty');
        }

        // For safety, turn off error_reporting on this point
        ini_set('error_reporting', 0);

        // Set the root-directory
        $rootDir = Mage::getBaseDir();

        // ZIP-format
        if($format == 'zip') {

            // Try to extract ZIP-archive using ZipArchive
            if(class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if($zip->open($tmpfile) === true) {
                    $rt = $zip->extractTo($rootDir);
                    $zip->close();

                    if($rt == false) {
                        $msg = 'ERROR: Failed to extract ZIP in '.$rootDir;
                        Mage::getSingleton('adminhtml/session')->addError($msg);
                        return $msg;
                    }

                    @unlink($tmpfile);

                } else {
                    $msg = 'ERROR: Unable to open ZIP '.$tmpfile;
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                    return $msg;
                }

            // Try to extract ZIP-archive using PharData
            } elseif(class_exists('PharData')) {
                $zip = new PharData($tmpfile);
                $rt = $zip->extractTo($rootDir);

                if($rt == false) {
                    $msg = 'ERROR: Failed to extract ZIP in '.$rootDir;
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                    return $msg;
                }

                @unlink($tmpfile);

            // Try to extra ZIP-archive using exec-function (assuming unzip is installed)
            } elseif(function_exists('exec')) {
                @exec('unzip -o '.$tmpfile.' -d '.$rootDir);
                @unlink($tmpfile);

            // Failed to extract ZIP-archive
            } else {
                $msg = 'ERROR: Failed to extract the ZIP-archive';
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return $msg;
            }

        // TGZ-format
        } elseif($format == 'tgz') {

            // Try to extract TAR-archive using TGZ-class
            if(class_exists('PharData')) {
                $tgz = new PharData($tmpfile);
                $tgz->decompress(); 

                $tar = new PharData(preg_replace('/\.(tar.gz|tgz)$/', '.tar', $tmpfile));
                $rt = $tar->extractTo($rootDir);

                if($rt == false) {
                    $msg = 'ERROR: Failed to extract TGZ in '.$rootDir;
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                    return $msg;
                }

                @unlink($tmpfile);

            // Try to extra TGZ-archive using exec-function (assuming tar is installed)
            } elseif(function_exists('exec')) {
                @exec('tar -xzf '.$tmpfile.' -C '.$rootDir);
                @unlink($tmpfile);

            // Failed to extract TGZ-archive
            } else {
                $msg = 'ERROR: Failed to extract the TGZ-archive';
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return $msg;
            }
        }

        // Remove obsolete files
        Mage::helper('magebridge/update')->removeFiles();

        // Reset the cached API-details
        Mage::getConfig()->deleteConfig('magebridge/settings/xmlrpc_url'); // legacy
        Mage::getConfig()->deleteConfig('magebridge/settings/api_url');
        Mage::getConfig()->deleteConfig('magebridge/settings/api_user');
        Mage::getConfig()->deleteConfig('magebridge/settings/api_key');

        // Rewove the cache
        Mage::getConfig()->removeCache();

        // Finalize with a notice
        Mage::getSingleton('adminhtml/session')->addSuccess('MageBridge has been upgraded');
        return null;
    }

    /*
     * Method to set the umask on files
     *
     * @access public
     * @param null
     * @return string
     */
    public function setFilesUmask()
    {
        // Set the umask as for index.php
        $indexPermissions = substr(sprintf('%o', fileperms(Mage::getBaseDir().DS.'index.php')), -4);
        @chmod(Mage::getBaseDir().DS.'js'.DS.'index.php', octdec($indexPermissions));
        $umask = 666 - (int)$indexPermissions;
        $umask = str_pad($umask, 4, '0', STR_PAD_LEFT);
        @umask($umask);
    }

    /*
     * Method to get the current MageBridge-version
     *
     * @access public
     * @param null
     * @return string
     */
    public function getCurrentVersion()
    {
        if(empty($this->_current_version)) {
            $config = Mage::app()->getConfig()->getModuleConfig('Yireo_MageBridge');
            $this->_current_version = (string)$config->version;
        }
        return $this->_current_version;
    }

    /*
     * Method to get the available MageBridge-version
     *
     * @access public
     * @param null
     * @return string
     */
    public function getNewVersion()
    {
        if(empty($this->_new_version)) {

            if(gethostbyname($this->_remote_domain) == $this->_remote_domain) {
                return 'ERROR: DNS lookup of '.$this->_remote_domain.' failed. External DNS lookups seem to be disabled.';
            }

            if(@fsockopen($this->_remote_domain, 80, $errno, $errmsg, 5) == false) {
                return 'ERROR: Failed to open a connection to host "'.$this->_remote_domain.'" on port 80. Perhaps a firewall is in the way?';
            }

            $arguments = array('resource' => 'versions', 'request' => 'downloads/magebridge');
            $url = $this->getApiLink($arguments);
            $this->_data = $this->_getRemote($url);

            if(preg_match('/^Restricted access/', $this->_data)) {
                return 'ERROR: Restricted access. Is your licensing correct?';
            } elseif(empty($this->_data)) { 
                return 'ERROR: Empty reply. Is CURL enabled?';
            }

            try {
                $doc = new SimpleXMLElement($this->_data);
            } catch(Exception $e) {
                return 'ERROR: Update check failed. Is your licensing correct?';
            }
            $this->_new_version = (string)$doc->magento;
        }
        return $this->_new_version;
    }

    /*
     * Method to get the current license-key
     *
     * @access public
     * @param null
     * @return string
     */
    public function getLicenseKey()
    {
        return Mage::getStoreConfig('magebridge/hidden/support_key');
    }

    /*
     * Method to get a remote file through HTTP
     *
     * @access public
     * @param string $url
     * @return mixed
     */
    private function _getRemote($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $data = curl_exec($ch);
        return $data;
    }
}
