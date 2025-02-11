let Module_Info_Newsletter_Form  = {

	init: function(){
		let selector = "#input_accept, #input_firstname, #input_surname, #input_email";
		let callback = Module_Info_Newsletter_Form.updateFormButton;
		jQuery(selector).on("change", callback);
		callback();
	},
	updateFormButton: function(){
		let button = $("#button_save");
		button.prop("disabled", "disabled");
		if(!$("#input_accept").is(":checked"))
			return;
		if(!$("#input_firstname").val().length)
			return;
		if(!$("#input_surname").val().length)
			return;
		if(!$("#input_email").val().length)
			return;
		button.prop("disabled", null);
	}
};
