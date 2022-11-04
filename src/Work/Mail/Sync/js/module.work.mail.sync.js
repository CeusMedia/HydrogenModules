var WorkMailSync = {
	init: function(){
		jQuery("#input_sameUsername").on("change", function(){
			jQuery("#input_targetUsername").removeAttr("readonly");
			if(jQuery("#input_sameUsername").is(":checked"))
				jQuery("#input_targetUsername").attr("readonly", "readonly");
			WorkMailSync.validateForm();
		});
		jQuery("#input_samePassword").on("change", function(){
			jQuery("#input_targetPassword").removeAttr("readonly");
			if(jQuery("#input_samePassword").is(":checked"))
				jQuery("#input_targetPassword").attr("readonly", "readonly");
			WorkMailSync.validateForm();
		});
		jQuery("#input_sourceMailHostId,#input_targetMailHostId").on("change", function(){
			WorkMailSync.validateForm();
		});
		jQuery("#input_sourceMailHostId").trigger("change");
		jQuery("#input_sameUsername").trigger("change");
		jQuery("#input_samePassword").trigger("change");

		jQuery("#input_ssl").on("change", function(){
			jQuery("#input_port").val(143);
			if(jQuery("#input_ssl").is(":checked"))
				jQuery("#input_port").val(993);
		});
	},
	validateForm: function(){
		var sourceId = jQuery("#input_sourceMailHostId").val();
		var targetId = jQuery("#input_targetMailHostId").val();

		var sourceUsername = jQuery("#input_sourceUsername").val();
		var targetUsername = jQuery("#input_targetUsername").val();
		if(jQuery("#input_sameUsername").is(":checked"))
			targetUsername = sourceUsername;

		jQuery("#button_save").attr("disabled", "disabled");
		if(sourceId != targetId || sourceUsername != targetUsername)
			jQuery("#button_save").removeAttr("disabled");
	}
};
