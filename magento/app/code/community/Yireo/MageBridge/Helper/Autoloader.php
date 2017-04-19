<?php
/**
 * MageBridge
 *
 * @author Yireo
 * @package MageBridge
 * @copyright Copyright 2017
 * @license Open Source License
 * @link https://www.yireo.com
 */

/**
 * Class Yireo_MageBridge_Helper_Autoloader
 */
class Yireo_MageBridge_Helper_Autoloader
{
    /**
     * Yireo_MageBridge_Helper_Autoloader constructor.
     */
    public function __construct()
    {
        require_once BP . '/lib/Yireo/Common/System/Autoloader.php';
    }

    /**
     * @return bool
     */
    public function load()
    {
        \Yireo\Common\System\Autoloader::init();
        \Yireo\Common\System\Autoloader::addPath(BP . '/lib/Yireo');

        return true;
    }
}