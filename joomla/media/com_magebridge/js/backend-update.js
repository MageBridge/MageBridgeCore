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
    $j('input#toggler').change(function() {
        if($j('input#toggler').is(':checked')) {
            var checked = true;
        } else {
            var checked = false;
        }

        $j('input.package').attr('checked', checked);
    });

    $j('td.select').click(function() {
        var currentId = $j(this).parent().attr('id'); 
        var inputId = 'input#package_input_' + currentId.replace(/package_/, '');
        
        if($j(inputId).is(':checked')) {
            var checked = false;
        } else {
            var checked = true;
        }
        $j(inputId).attr('checked', checked);
    });
});
