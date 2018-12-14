
var FormOptionals = {
	init: function (selector) {
		if(typeof selector === "undefined")
			selector = "body";
		var items = jQuery(selector).find(":input.has-optionals");
		items.on("change change-update", function () {
			FormOptionals.showOptionals(this);
		}).trigger("change-update");
	},
	showOptionals: function (elem) {
		var form = jQuery(elem.form);
		var name = jQuery(elem).attr("name");
		var type = jQuery(elem).attr("type");
		var value = name+"-" + jQuery(elem).val().replace(/[(@\.]/g, '_');
		if (type === "checkbox")
			value = name + "-" + jQuery(elem).prop("checked");

		var toHide = form.find(".optional." + name).not("." + value);
		var toShow = form.find(".optional." + value);

		if (type === "radio") {													//  element input is of type radio
			if (!jQuery(elem).prop("checked")) {								//  this radio is NOT checked
				toShow = jQuery();												//  do not show anything, will be done on selected element
				if (form.find(":input[name="+name+"]:checked").length) {		//  there is a preselected radio in this group
					toHide = jQuery();											//  do not hide anything, will be done on selected element
				}
			}
		}

		FormOptionals.disableRequired(toHide);
		FormOptionals.enableRequired(toShow);

		if (!jQuery(elem).data("status")) {										//  initial run
			toHide.hide();														//  hide disabled optionals right now
			toShow.show();														//  show enabled optionals right now
			jQuery(elem).data("status", 1);										//  note inital run
			return;
		}

		switch (jQuery(elem).data('animation')) {								//  watch for transition style
			case 'fade':
				toHide.fadeOut();
				toShow.fadeIn();
				break;
			case 'slide':
				toHide.slideUp(jQuery(elem).data('speed-hide'));
				toShow.slideDown(jQuery(elem).data('speed-show'));
				break;
			default:
				toHide.hide();
				toShow.show();
		}
	},
	disableRequired: function(container){
		var $selfInput		= container.filter(":input")
		var $childInputs	= container.find(":input");
		$selfInput.add($childInputs).each(function(){
			var elem = jQuery(this);
			if(elem.attr("required")){
				elem.data("optionals-required", elem.attr("required"));
				elem.removeAttr("required");
			}
		});
	},
	enableRequired: function(container){
		var $selfInput		= container.filter(":input")
		var $childInputs	= container.find(":input");
		$selfInput.add($childInputs).each(function(){
			var elem = jQuery(this);
			if(elem.data("optionals-required")){
				elem.attr("required", elem.data("optionals-required"));
				elem.data("optionals-required", null);
			}
		});
	}
};

/* @deprecated by hook
function showOptionals (elem) {
	FormOptionals.showOptionals(elem);
}*/
