ModuleManageShop = {};
ModuleManageShopShipping = {
	toggleModalCountries: function(){
		if(jQuery("#modalAddZone #input_fallback").is(":checked"))
			jQuery("#modalAddZone #modal-countries").hide();
		else
			jQuery("#modalAddZone #modal-countries").show();
	},
	toggleModalWeight: function(){
		if(jQuery("#modalAddGrade #input_fallback").is(":checked"))
			jQuery("#modalAddGrade #input_weight").addClass("disabled").prop("disabled", true);
		else
			jQuery("#modalAddGrade #input_weight").removeClass("disabled").prop("disabled", null);
	}
};
