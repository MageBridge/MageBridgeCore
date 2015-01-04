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
 * MageBridge model for handling the Magento head-block
 */
class Yireo_MageBridge_Model_Headers extends Yireo_MageBridge_Model_Block
{
    /*
     * Method to get the current Magento headers
     *
     * @access public
     * @param nul
     * @return array
     */
    public static function getHeaders()
    {
        Mage::getSingleton('magebridge/debug')->notice('Load headers');

        // Try to get the FrontController
        try {
            $controller = Mage::getSingleton('magebridge/core')->getController();
            $controller->getAction()->renderLayout();

        } catch(Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to load controller: '.$e->getMessage());
            return false;
        }

        // Get the head-block from the current layout
        try {
            $head = $controller->getAction()->getLayout()->getBlock('head');
            if(method_exists($head,'getRobots')) $head->getRobots();
            if(method_exists($head,'getIncludes')) $head->getIncludes();

            // Get the data from this block-object
            if(!empty($head)) {

                // Fetch meta-data from the MageBridge request
                $disable_css = Mage::getSingleton('magebridge/core')->getMetaData('disable_css');
                $disable_js = Mage::getSingleton('magebridge/core')->getMetaData('disable_js');
                $app = Mage::getSingleton('magebridge/core')->getMetaData('app');

                // Prefetch the headers to remove items first (but don't do this from within the Joomla! Administrator)
                if($app != 1 && (!empty($disable_css) || !empty($disable_js))) {
                    $headers = $head->getData();
                    foreach($headers['items'] as $index => $item) {
                        if(isset($item['name']) && (in_array($item['name'], $disable_css) || in_array($item['name'], $disable_js))) {
                            $head->removeItem($item['type'], $item['name']);
                        }
                    }
                }
                
                // Refetch the headers
                $headers = $head->getData();

                // Parse the headers before sending it to Joomla!
                foreach($headers['items'] as $index => $item) {

                    $item['path'] = null;
                    switch($item['type']) {

                        case 'js':
                        case 'js_css':
                            $item['path'] = Mage::getBaseUrl('js').$item['name'];
                            $item['file'] = Mage::getBaseDir().DS.'js'.DS.$item['name'];
                            break;

                        case 'skin_js':
                        case 'skin_css':
                            $item['path'] = Mage::getDesign()->getSkinUrl($item['name']);
                            $item['file'] = Mage::getDesign()->getFilename($item['name'], array('_type' => 'skin'));
                            break;

                        default:
                            $item['path'] = null;
                            break;
                    }

                    $headers['items'][$index] = $item;
                }

                // Add merge scripts
                if(Mage::getStoreConfigFlag('dev/js/merge_files') == 1) {
                    $js = array();
                    foreach($headers['items'] as $item) {
                        if(isset($item['file']) && is_readable($item['file'])) {
                            if(preg_match('/js$/', $item['type'])) {
                                $js[] = $item['file'];
                            }
                        }
                    }
                    if(!empty($js)) {
                        $headers['merge_js'] = Mage::getDesign()->getMergedJsUrl($js);
                    } else {
                        $headers['merge_js'] = null;
                    }
                }

                // Add merge CSS
                if(Mage::getStoreConfigFlag('dev/css/merge_css_files') == 1) {
                    $css = array();
                    foreach($headers['items'] as $item) {
                        if(isset($item['file']) && is_readable($item['file'])) {
                            if(preg_match('/css$/', $item['type'])) {
                                $css[] = $item['file'];
                            }
                        }
                    }
                    if(!empty($css)) {
                        $headers['merge_css'] = Mage::getDesign()->getMergedCssUrl($css);
                    } else {
                        $headers['merge_css'] = null;
                    }
                }

                // Add custom scripts
                $headers['custom'] = array();

                // Get the childhtml script
                $childhtmlScript = $head->getChildHtml();
                $headers['custom']['child_html'] = Mage::helper('magebridge/encryption')->base64_encode($childhtmlScript);

                // Get the translator script
                $translatorScript = Mage::helper('core/js')->getTranslatorScript();
                $headers['custom']['translate'] = Mage::helper('magebridge/encryption')->base64_encode($translatorScript);

                return $headers;
            }
            return false;

        } catch( Exception $e) {
            Mage::getSingleton('magebridge/debug')->error('Failed to get headers: '.$e->getMessage());
            return false;
        }
    }
}
