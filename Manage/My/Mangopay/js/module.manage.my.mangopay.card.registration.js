var ModulePaymentMangopayCardRegistration = {
	validNumber: false,
	validDate: false,
	validCvv: false,
	init: function(){
		jQuery("#input_cardNumber").bind("input", this.validateCardNumber);
		jQuery("#input_cardDate").bind("input", this.validateCardDate);
		jQuery("#input_cardCvx").bind("input", this.validateCardCvv);
		this.updateSubmitButton();
	},
	updateSubmitButton: function(){
		var button = jQuery("form button[type=submit]");
		var valid = this.validNumber && this.validDate && this.validCvv;
		button.prop("disabled", "disabled");
		if(valid)
			button.removeProp("disabled");
	},
	validateCardDate: function(){
		var input = jQuery("#input_cardDate");
		var isValid = false;
		var value = input.val();
		var date, year, month;
		input.removeClass("error").removeClass("success");
		if(value.length){
			if(value.match(/^\d{2}\/\d{2}$/)){
				date = new Date();
				year = parseInt(value.replace(/^(\d{2})\/(\d{2})$/, "20$2"), 10);
				month = parseInt(value.replace(/^(\d{2})\/(\d{2})$/, "$1"), 10);
				if(year >= date.getFullYear()){
					if(month > 0 && month <= 12){
						var isThisYear = year == date.getFullYear();
						if(!isThisYear || month >= date.getMonth() + 1)
							isValid = true;
					}
				}
			}
			jQuery("#input_cardExpirationDate").val(input.val().replace(/\//, ""));
			input.addClass(isValid ? "success" : "error");
			ModulePaymentMangopayCardRegistration.validDate	= isValid;
		}
		ModulePaymentMangopayCardRegistration.validDate	= isValid;
		ModulePaymentMangopayCardRegistration.updateSubmitButton();
	},
	validateCardNumber: function(){
		var input = jQuery("#input_cardNumber");
		ModulePaymentMangopayCardRegistration.validNumber = false;
		ModulePaymentMangopayCardRegistration.updateSubmitButton();
		if(!input.val().length){
			input.removeClass("error").removeClass("success");
			return;
		}
		jQuery.ajax({
			url: "./manage/my/mangopay/card/registration/ajaxValidateCardNumber",
			data: {cardNumber: input.val(), cardProvider: cardProvider},
			method: "post",
			dataType: "json",
			context: input,
			success: function(json){
				if(json.status == "data"){
					this.removeClass("error").removeClass("success");
					ModulePaymentMangopayCardRegistration.validNumber = json.data;
					ModulePaymentMangopayCardRegistration.updateSubmitButton();
					this.removeClass("error").removeClass("success");
					this.addClass(json.data ? "success" : "error");
				}
			}
		})
	},
	validateCardCvv: function(){
		var input = jQuery("#input_cardCvx");
		var isValid = false;
		input.removeClass("error").removeClass("success");
		if(input.val().length){
			isValid = input.val().match(/^\d{3}$/);
			input.addClass(isValid ? "success" : "error");
		}
		ModulePaymentMangopayCardRegistration.validCvv	= isValid;
		ModulePaymentMangopayCardRegistration.updateSubmitButton();
	}
};
