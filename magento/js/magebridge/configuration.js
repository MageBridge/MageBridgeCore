/**
 * Magento Bridge
 *
 * @author Yireo
 * @package Magento Bridge
 * @copyright Copyright 2015
 * @license Open Source License
 * @link http://www.yireo.com
 */

Event.observe(window, 'load', function() {
    var apiDetect = $('magebridge_settings_api_detect');
    if(apiDetect) {
        if(apiDetect.getValue() == 1) {
            $('magebridge_settings_api_url').disable();
            $('magebridge_settings_api_user').disable();
            $('magebridge_settings_api_key').disable();
        }
    
        apiDetect.observe('change', function(event) {
            if(this.getValue() == 0) {
                $('magebridge_settings_api_url').enable();
                $('magebridge_settings_api_user').enable();
                $('magebridge_settings_api_key').enable();
            } else {
                $('magebridge_settings_api_url').disable();
                $('magebridge_settings_api_user').disable();
                $('magebridge_settings_api_key').disable();
            }
        });
    }

});
