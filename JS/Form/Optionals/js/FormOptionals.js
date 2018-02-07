
var FormOptionals = {
	init: function () {
		$(":input.has-optionals").bind("change change-update", function () {
			FormOptionals.showOptionals(this);
		}).trigger("change-update");
	},
	showOptionals: function (elem) {
		var form = $(elem.form);
		var name = $(elem).attr("name");
		var type = $(elem).attr("type");
		var value = name+"-" + $(elem).val();
		if (type === "checkbox") {
			value = name + "-" + $(elem).prop("checked");
		}

		var toHide = form.find(".optional." + name).not("." + value);
		var toShow = form.find(".optional." + value);

		if (type === "radio") {													//  element input is of type radio
			if (!$(elem).prop("checked")) {										//  this radio is NOT checked
				toShow = jQuery();												//  do not show anything, will be done on selected element
				if (form.find(":input[name="+name+"]:checked").size()) {		//  there is a preselected radio in this group
					toHide = jQuery();											//  do not hide anything, will be done on selected element
				}
			}
		}

		FormOptionals.disableRequired(toHide);
		FormOptionals.enableRequired(toShow);

		if (!$(elem).data("status")) {											//  initial run
			toHide.hide();														//  hide disabled optionals right now
			toShow.show();														//  show enabled optionals right now
			$(elem).data("status", 1);											//  note inital run
			return;
		}

		switch ($(elem).data('animation')) {									//  watch for transition style
			case 'fade':
				toHide.fadeOut();
				toShow.fadeIn();
				break;
			case 'slide':
				toHide.slideUp($(elem).data('speed-hide'));
				toShow.slideDown($(elem).data('speed-show'));
				break;
			default:
				toHide.hide();
				toShow.show();
		}
	},
	disableRequired: function(container){
		container.find(":input").each(function(){
			var elem = jQuery(this);
			if(elem.attr("required")){
				elem.data("optionals-required", elem.attr("required"));
				elem.removeAttr("required");
			}
		});
	},
	enableRequired: function(container){
		container.find(":input").each(function(){
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
