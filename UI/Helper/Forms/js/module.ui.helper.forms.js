/**
 * Ceus Media Forms
 * Wordpress plugin for external forms.
 * Author		Christian Würker <christian.wuerker@ceusmedia.de>
 * Version		1.0
 */
var Forms = {
	mode: 'live',
	status: 0,
	urlServer: null,
	parameters: {},

	apply: function(formId){
		if(typeof Forms.parameters['preseed'] !== "undefined")
			Forms.prefillData(formId);
		if( Forms.mode === 'dev' )
			Forms.initCollapsableRows(formId);
		FormOptionals.init("#"+formId);
	},

	collectFormData: function(form){
		var data = {};
		form.find(':input').each(function(){
			if(this.nodeName === "BUTTON")
				return;
			var node = jQuery(this);
			var item = {
				id: this.id,
				type: this.nodeName.toLowerCase(),
				name: this.name,
				label: null,
				value: node.val(),
				valueLabel: null,
			};
			var relatedLabel = jQuery("label[for='input_"+item.name+"']");
			if(relatedLabel.size())
				item.label = relatedLabel.get(0).innerText;

			if(this.nodeName === "SELECT"){
				item.type = "select";
				var value = node.val();
				var options = node.children('option');
				for(i=0; i<options.size(); i++){
					if(options.eq(i).val() == value){
						item.valueLabel = options.eq(i).html();
						break;
					}
				}
			}
			if(this.nodeName === "INPUT"){
				var inputType = node.attr('type');

				if(inputType === "date"){
					item.type = "date";
				}
				if(inputType === "checkbox"){
					item.type = "checkbox";
					item.value = node.is(":checked") ? 'ja' : 'nein';
					item.valueLabel = node.is(":checked") ? 'ja' : 'nein';
				}
				else if(inputType === "radio"){
					if(typeof data[this.name] !== "undefined"){
						item = data[this.name];
					}
					else{
						item.type = "radio";
						item.id = 'input_'+this.name;
//						item.value = '['+this.name+']';
						item.valueLabel	= "";
						if(relatedLabel.size())
							item.label = relatedLabel.get(0).innerText;
					}
					if(node.is(":checked")){
						item.value = this.value;
						item.valueLabel	= node.parent().get(0).innerText;
						var radioSpan = jQuery("span#input_"+this.name+"-"+this.value);
						if(radioSpan.size())
							item.valueLabel	= radioSpan.get(0).innerText;
					}
				}
				else{
					item.type = 'text';
				}
			}
			data[item.name]	= item;
		});
		return data;
	},

	handleResponse: function(response){
//		console.log(response);
		var form = jQuery("#form-"+response.data.formId);
		if(response.status == "ok"){
			form.slideUp(500, function(){
				form.parent().find(".form-message-error").slideUp(150);
				form.parent().find(".form-message-success").slideDown(500);
			});
		}
		else if(response.status == "error"){
			form.parent().find(".form-message-error-title").html(response.data.error);
			form.slideUp(500, function(){
				form.parent().find(".form-message-success").slideUp(150);
				form.parent().find(".form-message-error").slideDown(500);
			});
/*			form.css({opacity: 1});
			form.find(':input').removeProp('disabled');
			form.find('button').each(function(){
				jQuery(this).removeProp('disabled');
			});*/
		}
	},

	init: function(urlServer, devMode, formId){
		if(Forms.urlServer)
			return Forms;
		if(typeof devMode !== "undefined")
			Forms.mode = devMode ? 'dev' : 'live';
		Forms.urlServer = urlServer;
		Forms.status = 1;

		//  COLLECT URL REQUEST PARAMETERS
		if(!Object.keys(Forms.parameters).length){
			var parameter;
			var parameters = decodeURIComponent(window.location.search.substring(1)).split('&');
			for( i=0; i<parameters.length; i++){
				parameter = parameters[i].split('=');
				parameter[1] = typeof parameter[1] === "undefined" ? true : parameter[1];
				Forms.parameters[parameter[0]] = parameter[1];
			}
		}
		return Forms;
	},

	initCollapsableRows: function(formId){
		jQuery("#"+formId+" .cmforms-row").prepend(jQuery('<span></span>')
			.attr({class: 'trigger-close'}).html('Zeile verbergen')
			.bind('click', function(elem){jQuery(elem.target).parent().slideUp()}));
	},

	prefillData: function(formId){
		jQuery("#"+formId+" :input").each(function(){
			if(this.nodeName == "INPUT" || this.nodeName == "SELECT"){
				if(typeof Forms.parameters[this.name] !== "undefined"){
					jQuery(this).val(Forms.parameters[this.name]);
				}
			}
		});
	},

	sendForm: function (elem){
		if(Forms.status != 1){
			alert('Das Formular wurde nicht initiiert.');
			return false;
		}
		try{
			var form = jQuery(elem);
			form.css({opacity: 1});
			form.find('button').each(function(){jQuery(this).prop('disabled', 'disabled')});
			Forms.validateFormData(form);
			if(Forms.errors.length){
				alert('Das Formular wurde nicht korrekt ausgefüllt.' );
//				console.log(Forms.errors);
			}
			else{
				var data = Forms.collectFormData(form);
//				console.log(data);
				jQuery.ajax({
					url: Forms.urlServer+'?action=fill_receive',
					method: 'POST',
					data: {inputs: data},
					dataType: 'json',
					success: Forms.handleResponse,
					error: function(a,b){
						console.log(a);
						console.log(b);
					}
				});
			}
		}
		catch (e){
			alert('Bei der Verarbeitung des Formulars ist ein Fehler aufgetreten.' );
			console.log(e);
		}
		return false;
	},

	validateFormData: function(form){
		Forms.errors	= [];
		form.find(':input').each(function(){
			if(this.nodeName === "BUTTON")
				return;
			var input = jQuery(this);
			if(input.attr('required')){
				if(!input.val().length){
					Forms.errors.push({
						id: this.id,
						rule: 'required',
					});
					return;
				}
			}
		});
	}
};
