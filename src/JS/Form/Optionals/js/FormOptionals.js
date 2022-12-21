/**
 *	Handles optional contents related to input elements.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2012-2019 Ceus Media (https://ceusmedia.de)
 */
var FormOptionals = {
	init: function (selector) {
		if(typeof selector === "undefined")										//  no specific container set
			selector = "body";													//  assume body as work container
		var items = jQuery(selector).find(":input.has-optionals");				//  find all input elements having optionals
		items.on("change change-update", function () {							//  bind event on change of input elements
			FormOptionals.showOptionals(this);									//  apply handling of optionals
		}).trigger("change-update");											//  trigger event to apply input values on start
	},
	showOptionals: function (elem) {
		var form = jQuery(elem.form);											//  get input containing form as a parent container
		var name = jQuery(elem).attr("name");									//  get name of input
		var type = jQuery(elem).attr("type");									//  get type of input
		var value = jQuery(elem).val().replace(/[( @\.]/g, '_');				//  get cleansed value

		if (type === "checkbox" && name.match(/\[\]$/))							//  if input is checkbox of a checkbox group
			name = name.replace(/\[\]/, '') + "-" + value;						//  get cleansed name and combine
		var identifier = name + "-" + value;									//  build identifier by name and cleansed value
		if (type === "checkbox" )												//  if input is checkbox
			identifier = name + "-" + jQuery(elem).prop("checked");				//  extend identifier by checkbox state

		var optionals = form.find(".optional." + name);							//  get all optionals for input within form
		var toHide = optionals.not("." + identifier);							//  collect optionals not having identifier
		var toShow = optionals.filter("." + identifier);						//  collect optionals having identifier

		if (type === "radio") {													//  element input is of type radio
			if (!jQuery(elem).prop("checked")) {								//  this radio is NOT checked
				toShow = jQuery();												//  do not show anything, will be done on selected element
				if (form.find(":input[name="+name+"]:checked").length) {		//  there is a preselected radio in this group
					toHide = jQuery();											//  do not hide anything, will be done on selected element
				}
			}
		}

		FormOptionals.disableRequired(toHide);									//  remove "required" attribute on optionals to hide
		FormOptionals.enableRequired(toShow);									//  restore "required" attribute on optionals to show

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
