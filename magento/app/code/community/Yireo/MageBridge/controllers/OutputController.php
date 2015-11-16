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

/**
 * MageBridge output tests
 */
class Yireo_MageBridge_OutputController extends Mage_Core_Controller_Front_Action
{
    /**
     * Output test 1
     *
     * @access public
     * @param null
     * @return null
     */
    public function test1Action()
    {
        echo 'test1';
    }

    /**
     * Output test 2
     *
     * @access public
     * @param null
     * @return null
     */
    public function test2Action()
    {
        echo 'test2';
        exit;
    }

    /**
     * Output test 3
     *
     * @access public
     * @param null
     * @return null
     */
    public function test3Action()
    {
        $result = array('test3' => 'yes');
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Output test 4
     *
     * @access public
     * @param null
     * @return null
     */
    public function test4Action()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Output test 5
     *
     * @access public
     * @param null
     * @return null
     */
    public function test5Action()
    {
        // @todo: Test whether Content-Type is correct in Joomla
        header('Content-Type: text/xml');
        echo '<test>test5</test>';
        exit;
    }

    /**
     * Output test 6
     *
     * @access public
     * @param null
     * @return null
     */
    public function test6Action()
    {
        if($this->getRequest()->isXmlHttpRequest()) {
            echo 'test6 is xml';
        } else {
            echo 'test6 is not xml';
        }
        exit;
    }

    /**
     * Output test 7
     *
     * @access public
     * @param null
     * @return null
     */
    public function test7Action()
    {
        Mage::getSingleton('core/session')->addError('Test7: Adding an error and then redirect');
        return $this->_redirect('customer/account/login');
    }

    /**
     * Output test 8
     *
     * @access public
     * @param null
     * @return null
     */
    public function test8Action()
    {
        Mage::getSingleton('magebridge/core')->setForcePreoutput(true);
        echo 'test8';
    }

    /**
     * Output test 9
     *
     * @access public
     * @param null
     * @return null
     */
    public function test9Action()
    {
        $url = Mage::getModel('core/url')->getUrl('customer/account');
        $this->getResponse()->setRedirect($url);
    }

    /**
     * Output test 10
     *
     * @access public
     * @param null
     * @return null
     */
    public function test10Action()
    {
        if (isset($_GET['test'])) {
            echo 'test=' . (int)$_GET['test']; 
        } else {
            echo 'No GET variable "test" given';
        }
    }

    /**
     * Output test 11
     *
     * @access public
     * @param null
     * @return null
     */
    public function test11Action()
    {
        $zipname = BP . '/skin/frontend/base/default/magebridge/test/test.zip';

        if (file_exists($zipname) == false) {
            die('File does not exist: ' . $zipname);
        }

        header('X-MageBridge-Test: test11');
        header('Content-Disposition: attachment; filename="' . basename($zipname) . '";');
        header('Content-Length: ' . filesize($zipname));
        header('Content-type: application/octet-stream');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: ' . date('r'));

        echo file_get_contents($zipname);
        exit;
    }

    /**
     * Output test 12
     *
     * @access public
     * @param null
     * @return null
     */
    public function test12Action()
    {
        $pdfname = BP . '/skin/frontend/base/default/magebridge/test/test.pdf';

        if (file_exists($pdfname) == false) {
            die('File does not exist');
        }

        header('X-MageBridge-Test: test12');
        header('Content-Disposition: inline; filename="'. basename($pdfname) . '";');
        header('Content-Length: ' . filesize($pdfname));
        header('Content-type: application/pdf');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: '.date('r'));

        echo file_get_contents($pdfname);
        exit;
    }
}
