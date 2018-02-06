ModuleInfoContactForm = {
	createModalSuccessLayer: function(){
		var layer = jQuery("<div></div>").hide();
		layer.html("<big><strong>Vielen Dank für Ihre Nachfrage!</big></strong><br/><br/><p>Die Nachricht wurde gesendet.</p><p>Wir werden Anliegen zeitnah behandeln.</p>");
		layer.addClass("alert alert-success modal-result-layer");
		return layer;
	},
	createModalErrorLayer: function(message){
		var layer = jQuery("<div></div>").hide();
		layer.html("<big><strong>Es ist ein Fehler aufgetreten!</big></strong><br/><br/><p>Fehler: "+message+".</p><p>Bitte probieren Sie es später noch einmal.</p>");
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
					form.find(".modal-body").append(layer.fadeIn(150));
					window.setTimeout(function(){
						form = jQuery(form);
						form.find("button,a.btn").removeAttr("disabled", "disabled");
						form.find(".modal-header button.close").trigger("click");
						form.find("#input_question,#input_request").html("");
						form.find("div.alert").fadeOut(2000);
					}, 3500, form);
				}
				else{
					var layer = ModuleInfoContactForm.createModalErrorLayer(response.message);
					form.find(".modal-body").append(layer.fadeIn(150));
					window.setTimeout(function(){
						form = jQuery(form);
						form.find("button,a.btn").removeAttr("disabled", "disabled");
						form.find("div.alert").fadeOut(2000);
					}, 2500, form);
				}
			}
		});
	}
};
