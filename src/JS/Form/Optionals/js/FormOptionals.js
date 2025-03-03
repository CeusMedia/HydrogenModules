// noinspection JSUnresolvedFunction,JSUnresolvedVariable

/**
 *	Handles optional contents related to input elements.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2012-2023 Ceus Media (https://ceusmedia.de)
 */
let FormOptionals = {
	init: function (selector) {
		if(typeof selector === "undefined")										//  no specific container set
			selector = "body";													//  assume body as work container
		let items = jQuery(selector).find(":input.has-optionals");				//  find all input elements having optionals
		items.on("change change-update", function () {							//  bind event on change of input elements
			FormOptionals.showOptionals(this);									//  apply handling of optionals
		}).trigger("change-update");											//  trigger event to apply input values on start
	},
	showOptionals: function (elem) {
		let form = jQuery(elem.form);											//  get input containing form as a parent container
		let name = jQuery(elem).attr("name");									//  get name of input
		let type = jQuery(elem).attr("type");									//  get type of input
		let value = jQuery(elem).val().replace(/[( @\.]/g, '_');				//  get cleansed value

		if (type === "checkbox" && name.match(/\[\]$/))							//  if input is checkbox of a checkbox group
			name = name.replace(/\[\]/, '') + "-" + value;						//  get cleansed name and combine
		let identifier = name + "-" + value;									//  build identifier by name and cleansed value
		if (type === "checkbox" )												//  if input is checkbox
			identifier = name + "-" + jQuery(elem).prop("checked");				//  extend identifier by checkbox state

		let optionals = form.find(".optional." + name);							//  get all optionals for input within form
		let toHide = optionals.not("." + identifier);							//  collect optionals not having identifier
		let toShow = optionals.filter("." + identifier);						//  collect optionals having identifier

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
			jQuery(elem).data("status", 1);										//  note initial run
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
		let $selfInput		= container.filter(":input")
		let $childInputs	= container.find(":input");
		$selfInput.add($childInputs).each(function(){
			let elem = jQuery(this);
			if(elem.attr("required")){
				elem.data("optionals-required", elem.attr("required"));
				elem.removeAttr("required");
			}
		});
	},
	enableRequired: function(container){
		let $selfInput		= container.filter(":input")
		let $childInputs	= container.find(":input");
		$selfInput.add($childInputs).each(function(){
			let elem = jQuery(this);
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
