/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2011
 * @link http://www.yireo.com
 */

// Simple replacement of the VarienForm class
VarienForm = Class.create();
VarienForm.prototype = {
    initialize: function(formId, firstFieldFocus){
        this.form = $j(formId);
        if (!this.form) {
            return;
        }
        if(this.firstFieldFocus){
            try{
                //Form.Element.focus(Form.findFirstElement(this.form))
            }
            catch(e){}
        }
    },

    submit : function(url){
        this.form.submit();
    }
}

Varien.searchForm = Class.create();
Varien.searchForm.prototype = {
    initialize : function(form, field, emptyText){
        this.form = $j(form);
        this.field = $j(field);
        this.emptyText = emptyText;
        this.blur();
    },

    initAutocomplete : function(url, destinationElement){
    }
}
