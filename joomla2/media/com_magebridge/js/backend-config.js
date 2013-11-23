/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @link http://www.yireo.com
 */

// Initialize jquery
$j = jQuery.noConflict();

$j(document).ready(function(){

    newip = $j('a#remoteaddr').html();

    $j('input#debug1').change(function() {
        ip = $j('input[name=debug_ip]').val();
        if($j('input#debug1').is(':checked') && ip == "") {
            $j('input[name=debug_ip]').val(newip);
        }
    });

    $j('a#remoteaddr').click(function() {
        $j('input[name=debug_ip]').val(newip);
    });
});

function toggleFieldset(id)
{
    id = '#' + id;
    $j(id).slideToggle();
    return false;
}
