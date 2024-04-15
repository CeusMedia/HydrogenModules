// noinspection JSUnresolvedFunction,JSUnresolvedVariable

/**
 * Ceus Media Forms
 * WordPress plugin for external forms.
 * Author		Christian Würker <christian.wuerker@ceusmedia.de>
 * Version		1.3
 */
let Forms = {
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
		FormsResponseHandler.init(formId);
	},

	collectFormData: function(form){
		let data = {};
		form.find(':input:visible').each(function(){
			if(this.nodeName === "BUTTON")
				return;
			let node = jQuery(this);
			let item = {
				id: this.id,
				type: this.nodeName.toLowerCase(),
				name: this.name,
				label: null,
				value: node.val(),
				valueLabel: null,
				text: null,
			};
			let relatedText = form.find('#input_'+this.name+'-text');
			let relatedLabel = jQuery("label[for='input_"+item.name+"']");
			if(relatedText.size())
				item.text = relatedText.get(0).innerText;
			if(relatedLabel.size())
				item.label = relatedLabel.get(0).innerText;

			if('SELECT' === this.nodeName){
				item.type = "select";
				let i;
				let value = node.val();
				let options = node.children('option');
				for(i=0; i<options.size(); i++){
					if(options.eq(i).val() === value){
						item.valueLabel = options.eq(i).html();
						break;
					}
				}
			}
			if('INPUT' === this.nodeName){
				let inputType = node.attr('type');
				item.type = 'text';

				if('date' === inputType){
					item.type = 'date';
				}
				else if('checkbox' === inputType){
					item.type = 'checkbox';
					item.value = node.is(":checked") ? 'ja' : 'nein';
					item.valueLabel = node.is(":checked") ? 'ja' : 'nein';
				}
				else if('radio' === inputType){
					if('undefined' !== typeof data[this.name]){
						item = data[this.name];
					}
					else{
						item.type = 'radio';
						item.id = 'input_'+this.name;
//						item.value = '['+this.name+']';
						item.valueLabel	= "";
						if(relatedLabel.size())
							item.label = relatedLabel.get(0).innerText;
					}
					if(node.is(":checked")){
						item.value = this.value;
						item.valueLabel	= node.parent().get(0).innerText;
						let radioSpan = jQuery("span#input_"+this.name+"-"+this.value);
						if(radioSpan.size())
							item.valueLabel	= radioSpan.get(0).innerText;
					}
				}
			}
			data[item.name]	= item;
		});
		return data;
	},


	init: function(urlServer, devMode, formId){
		if('undefined' !== typeof devMode)
			Forms.mode = devMode ? 'dev' : 'live';
		Forms.urlServer = urlServer;
		Forms.status = 1;

		//  COLLECT URL REQUEST PARAMETERS
		if(!Object.keys(Forms.parameters).length){
			let i, parameter;
			let parameters = decodeURIComponent(window.location.search.substring(1)).split('&');
			for(i=0; i<parameters.length; i++){
				parameter = parameters[i].split('=');
				parameter[1] = 'undefined' === typeof parameter[1] ? true : parameter[1];
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
			if('INPUT' === this.nodeName || 'SELECT' === this.nodeName){
				if('undefined' !== typeof Forms.parameters[this.name]){
					jQuery(this).val(Forms.parameters[this.name]);
				}
			}
		});
	},

	sendForm: function (elem){
		if(Forms.status !== 1){
			alert('Das Formular wurde nicht initiiert.');
			return false;
		}
		try{
			let form = jQuery(elem);
			form.css({opacity: 1});
			/*			form.find('button').each(function(){jQuery(this).prop('disabled', 'disabled')});*/
			Forms.validateFormData(form);
			if(Forms.errors.length){
				alert('Das Formular wurde nicht korrekt ausgefüllt.' );
//				console.log(Forms.errors);
			}
			else{
				let data = Forms.collectFormData(form);
//				console.log(data);
				jQuery.ajax({
					url: Forms.urlServer+'/manage/form/fill/receive',
					method: 'POST',
					data: {formId: form.data('id'), inputs: data},
					dataType: 'json',
					context: {form: form},
					xhrFields: {
						withCredentials: true
					},
					success: FormsResponseHandler.handle,
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
		form.find(':input[required]:visible').each(function(){
			if('BUTTON' === this.nodeName)
				return;
			let input = jQuery(this);
			if(!input.val().length){
				Forms.errors.push({
					id: this.id,
					rule: 'required',
				});
			}
		});
	}
};

let FormsResponseHandler = {
	form: null,
	init: function(form){
		FormsResponseHandler.form = form;
	},
	handle: function(response){
//		var form = jQuery("#form-"+response.data.formId);
		let form = jQuery(this.form);
		switch(response.status){
			case 'ok':
				FormsResponseHandler.handleOk(response, form);
				break;
			case 'captcha':
				FormsResponseHandler.handleCaptcha(response, form);
				break;
			case 'error':
				FormsResponseHandler.handleError(response, form);
				break;
		}
	},

	handleCaptcha: function(response, form){
		form.find("#input_captcha").val('');
		alert( 'Der Sicherheitscode ist nicht richtig.' );
		form.find(':input').removeProp('disabled');
	},

	handleError: function(response, form){
		form.parent().find(".form-message-error-title").html(response.data.error);
		form.slideUp(500, function(){
			form.parent().find(".form-message-success").slideUp(150);
			form.parent().find(".form-message-error").slideDown(500);
		});
		/*		form.css({opacity: 1});
				form.find('button').each(function(){
					jQuery(this).removeProp('disabled');
				});*/
	},

	handleOk: function(response, form){
		form.slideUp(500, function(){
			form.parent().find(".form-message-error").slideUp(150);
			form.parent().find(".form-message-success").slideDown(500);
		});
	}
};