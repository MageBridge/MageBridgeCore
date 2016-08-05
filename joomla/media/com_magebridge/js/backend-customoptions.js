/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2016
 * @link https://www.yireo.com
 */

// Initialize jquery
$j = jQuery.noConflict();

$j(document).ready(function(){
    $j('input[name="disable_css_all"]').change(function() {
        if(this.value == 2 || this.value == 3) {
            document.getElementById('disable_css_mage').disabled = false;
        } else {
            document.getElementById('disable_css_mage').disabled = true;
        }
    });

    $j('input[name="disable_js_all"]').change(function() {
        if(this.value == 2 || this.value == 3) {
            document.getElementById('disable_js_custom').disabled = false;
        } else {
            document.getElementById('disable_js_custom').disabled = true;
        }
    });

    $j('input[name="use_google_api"]').change(function() {
        if(this.value == 1) {
            $j('#use_protoaculous0').attr('checked', true);
            $j('#use_protoculous0').attr('checked', true);
        }
    });

    $j('input[name="use_protoaculous"]').change(function() {
        if(this.value == 1) {
            $j('#use_google_api0').attr('checked', true);
            $j('#use_protoculous0').attr('checked', true);
        }
    });

    $j('input[name="use_protoculous"]').change(function() {
        if(this.value == 1) {
            $j('#use_google_api0').attr('checked', true);
            $j('#use_protoaculous0').attr('checked', true);
        }
    });
});
