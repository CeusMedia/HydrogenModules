/*global Function, Object, UI, console, jQuery, settings, window */

if (UI === undefined) {
    var UI = {};
}
if (UI.Form === undefined) {
    UI.Form = {};
}

UI.Form.Changes = {

    getInputs: function (selectorOrContainer) {
        "use strict";
        selectorOrContainer = selectorOrContainer || "body";
        var container = jQuery(selectorOrContainer),
            inputs = container.find("input").not("[type=checkbox]");
        return inputs.add(container.find("select")).add(container.find("textarea"));
    },

    init: function () {
        "use strict";
        var config = settings.JS_Form_Changes;
        if (config.auto_enabled) {
            UI.Form.Changes.Indicator.applyTo(config.auto_selector, {lock: config.auto_lock});
        }
    }
};

UI.Form.Changes.Indicator = {

    applyTo: function (selectorOrContainer, options) {
        "use strict";
        options = jQuery.extend({}, options);
        var form = jQuery(selectorOrContainer).addClass("ui-form-changes");
        UI.Form.Changes.getInputs(form).each(function () {
            var input = jQuery(this);
            input.data("original-value", input.val());
			input.data("original-container", selectorOrContainer);
            input.bind("keyup.FormChanges change.FormChanges", function () {
                if (jQuery(this).val() !== input.data("original-value")) {
                    jQuery(this).addClass("changed");
                    if (options.lock) {
                        UI.Form.Changes.Lock.enable();
                    }
                } else {
                    jQuery(this).removeClass("changed");
                    if (options.lock) {
                        UI.Form.Changes.Lock.detect(".ui-form-changes");
                    }
                }
            });
        });
        form.find("button[type='reset']").bind("click.FormChanges", function (event) {
            event.preventDefault();
            form.get(0).reset();
            UI.Form.Changes.getInputs(form).trigger("keyup.FormChanges");
            event.stopPropagation();
            return false;
        });
        if (options.lock) {
            form.find("button[type='submit']").bind("click", UI.Form.Changes.Lock.disable);
        }
/*    },
	evaluateInput: function(selectorOrContainer){
		$(selectorOrContainer){


*/	}
};

UI.Form.Changes.Lock = {

    message: "There are unsaved changes within this page.",
    state: 0,

    applyTo: function (selectorOrContainer) {
        "use strict";
        selectorOrContainer = selectorOrContainer || "body";
        var container = jQuery(selectorOrContainer);
        container.find(":input").bind("change.FormChangesLock", function () {
            UI.Form.Changes.Lock.detect(selectorOrContainer);
        });
    },

    detect: function (selectorOrContainer) {
        "use strict";
        var container = jQuery(selectorOrContainer || "body"),
            hasChanges = container.find(":input.changed").size();
        if (hasChanges) {
            UI.Form.Changes.Lock.enable();
        } else {
            UI.Form.Changes.Lock.disable();
        }
    },

    disable: function () {
        "use strict";
        if (UI.Form.Changes.Lock.state === 1) {
            UI.Form.Changes.Lock.state = 0;
            jQuery(window).unbind("beforeunload.FormChangesLock");
            jQuery("body").removeClass("form-changes-locked");
        }
    },

    //  @link    http://jsfiddle.net/XZAWS/
    enable: function () {
        "use strict";
        if (UI.Form.Changes.Lock.state === 0) {
            UI.Form.Changes.Lock.state = 1;
            jQuery(window).bind("beforeunload.FormChangesLock", function (event) {
                event.returnValue = UI.Form.Changes.Lock.message;
                return UI.Form.Changes.Lock.message;
            });
            jQuery("body").addClass("form-changes-locked");
        }
    }
};
