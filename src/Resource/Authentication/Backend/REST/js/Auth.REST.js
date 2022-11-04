if(typeof Auth === "undefined")
	var Auth = {};

Auth.Rest = {};
Auth.Rest.Register = {
	validUsername: false,
	validEmail: false,
	countries:  [],
	checkEmail: function(email){
		AJAX.send("./auth/rest/ajaxEmailExists", {email: email}, function(json){
			var input = Auth.Rest.Register.container.find("#input_email");
			switch(json){
				case -1:
					input.removeClass("error success").addClass("warning");
					Auth.Rest.Register.validEmail = false;
					Auth.Rest.Register.updateSubmitButton();
					break;
				case 0:
					input.removeClass("error warning").addClass("success");
					Auth.Rest.Register.validEmail = true;
					Auth.Rest.Register.updateSubmitButton();
					break;
				case 1:
				default:
					input.removeClass("success warning").addClass("error");
					Auth.Rest.Register.validEmail = false;
					Auth.Rest.Register.updateSubmitButton();
					break;
			}
		});
	},
	checkUsername: function(username){
		AJAX.send("./auth/rest/ajaxUsernameExists", {username: username}, function(json){
			var input = Auth.Rest.Register.container.find("#input_username");
			switch(json){
				case 0:
					input.removeClass("error warning").addClass("success");
					Auth.Rest.Register.validUsername = true;
					Auth.Rest.Register.updateSubmitButton();
					break;
				case 1:
					input.removeClass("success warning").addClass("error");
					Auth.Rest.Register.validUsername = false;
					Auth.Rest.Register.updateSubmitButton();
					break;
				case -1:
				default:
					input.removeClass("error success").addClass("warning");
					Auth.Rest.Register.validUsername = false;
					Auth.Rest.Register.updateSubmitButton();
					break;
			}
		});
	},
	init: function(containerSelector){
		this.container = jQuery(containerSelector);
		this.container.find("#input_username").bindWithDelay("input change keyup", function(){
			var input = jQuery(this);
			if(input.data('lastValue') !== input.val() )
			Auth.Rest.Register.checkUsername(input.val());
			input.data('lastValue', input.val());
		}, 150);
		if(this.container.find("#input_username").val().length)
			Auth.Rest.Register.checkUsername(this.container.find("#input_username").val());
		this.container.find("#input_email").bindWithDelay("input change keyup", function(){
			Auth.Rest.Register.checkEmail(jQuery(this).val());
		}, 150);
		if(this.container.find("#input_email").val().length)
			Auth.Rest.Register.checkEmail(this.container.find("#input_email").val());
	    this.container.find(".typeahead").each(function(){
	        jQuery(this).typeahead({
	            source: Auth.Rest.Register.countries,
	            items: 4
	        });
	    });
	},
	updateSubmitButton: function(){
		var button = Auth.Rest.Register.container.find("button.btn-primary");
		var status = this.validUsername && this.validEmail;
		button.prop({disabled: status ? null : "disabled"});
	}
};
