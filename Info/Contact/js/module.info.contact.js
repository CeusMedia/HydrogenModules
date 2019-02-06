ModuleInfoContactForm = {
	durationLayerFadeIn: 150,
	durationLayerFadeOut: 2000,
	durationLayerShow: 4000,
	resultBlocks: {
		success: '',
		error: ''
	},
	createModalSuccessLayer: function(){
		var layer = jQuery("<div></div>").stop(true, true).hide();
		layer.html(this.resultBlocks.success);
		layer.addClass("alert alert-success modal-result-layer");
		return layer;
	},
	createModalErrorLayer: function(message){
		var layer = jQuery("<div></div>").stop(true, true).hide();
		layer.html(this.resultBlocks.error.replace(/##errorMessage##/, message));
		layer.addClass("alert alert-error modal-result-layer");
		return layer;
	},
	sendContactForm: function(elem){
		var form = jQuery(elem);
		form.find("button,a.btn").attr("disabled", "disabled");
		jQuery.ajax({
			url: form.attr("action"),
			method: "POST",
			dataType: "JSON",
			data: form.serialize(),
			success: function(response){
				if(response.status === "data"){
					var layer = ModuleInfoContactForm.createModalSuccessLayer();
					form.find(".modal-body").append(layer.fadeIn(ModuleInfoContactForm.durationLayerFadeIn));
					window.setTimeout(function(){
						form = jQuery(form);
						form.find("button,a.btn").removeAttr("disabled", "disabled");
						form.find(".modal-header button.close").trigger("click");
						form.find("#input_question,#input_request").html("");
						form.find("div.alert").fadeOut(ModuleInfoContactForm.durationLayerFadeOut);
					}, ModuleInfoContactForm.durationLayerShow, form);
				}
				else{
					var layer = ModuleInfoContactForm.createModalErrorLayer(response.message);
					form.find(".modal-body").append(layer.fadeIn(ModuleInfoContactForm.durationLayerFadeIn));
					window.setTimeout(function(){
						form = jQuery(form);
						form.find("button,a.btn").removeAttr("disabled", "disabled");
						form.find("div.alert").fadeOut(ModuleInfoContactForm.durationLayerFadeOut);
					}, ModuleInfoContactForm.durationLayerShow, form);
				}
			}
		});
	},
	setResultBlocks: function(blocks){
		this.resultBlocks = blocks;
	}
};
