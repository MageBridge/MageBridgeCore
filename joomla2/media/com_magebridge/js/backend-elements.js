/*
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @link http://www.yireo.com
 */

/*
 * JavaScript functions for usage with JElement objects
 */

// Function JElementCategory
function jSelectCategory(category_value, category_title, name) 
{
    item_name = document.getElementById(name);
    if(item_name) {
        item_name.value = category_value;
    } else {
        alert('Unable to find element ' + name);
    }

    if(window = document.getElementById('sbox-window')) { window.close(); }
    if(SqueezeBox) { SqueezeBox.close(); }

    item_title = document.getElementById('jform_title');
    if(item_title == false || item_title == null) {
        item_title = $$('input[name=name]')[0];
    }

    if(item_title) {
        current_name = item_title.value;
        if(current_name == '') {
            item_title.value = category_title;
        }
    }
}

// Function JElementProduct
function jSelectProduct(product_value, product_title, name) 
{
    item_name = document.getElementById(name);
    if(item_name) {
        item_name.value = product_value;
    } else {
        alert('Unable to find element ' + name);
    }

    if(window = document.getElementById('sbox-window')) { window.close(); }
    if(SqueezeBox) { SqueezeBox.close(); }

    item_title = document.getElementById('jform_title');
    if(item_title == false || item_title == null) {
        item_title = $$('input[name=name]')[0];
    }

    if(item_title) {
        current_name = item_title.value;
        if(current_name == '') {
            item_title.value = product_title;
        }
    }

    item_label = $$('input[name=label]')[0];
    if(item_label) {
        current_name = item_label.value;
        if(current_name == '') {
            item_label.value = product_title;
        }
    }
}

// Function JElementWidget
function jSelectWidget(widget_value, widget_title, name) 
{
    item_name = document.getElementById(name);
    if(item_name) {
        item_name.value = widget_value;
    } else {
        alert('Unable to find element ' + name);
    }

    if(window = document.getElementById('sbox-window')) { window.close(); }
    if(SqueezeBox) { SqueezeBox.close(); }

    item_title = $('jform_title');
    if(item_title == false || item_title == null) {
        item_title = $$('input[name=name]')[0];
    }

    if(item_title) {
        current_name = item_title.value;
        if(current_name == '') {
            item_title.value = widget_title;
        }
    }
}
