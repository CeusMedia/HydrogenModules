var ModuleResourceAuthLocal = {
	regExpEmail: /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i
};
ModuleResourceAuthLocal.Password = {
	init: function(){
		$("#input_password_email").on("input", function(event){
			var button = $("#button_save");
			var value = $(event.target).val();
			button.attr("disabled","disabled");
			if(!value.replace(/\s/, '').length)
				return;
			if(!value.match(ModuleResourceAuthLocal.regExpEmail))
				return;
			button.removeAttr("disabled");
		});
	}
};

ModuleResourceAuthLocal.Login = {
	init: function(){
		if(jQuery("#input_login_username").val())
			jQuery("#input_login_password").focus();
		else
			jQuery("#input_login_username").focus();
	}
};

ModuleResourceAuthLocal.Registration = {
	init: function(){
		$("#input_username").keyup(this.checkUsername).trigger("keyup");
		$("#input_email").keyup(this.checkEmail).trigger("keyup");
		$("#input_password").keyup(this.checkPassword);

		if($("#input_accept_tac").length){
			$("#button_save").attr("disabled","disabled");
			$("#input_accept_tac").change(function(){
				$("#button_save").attr("disabled","disabled");
				if($(this).is(":checked"))
					$("#button_save").removeAttr("disabled");
			});
		}
		else{
			$("#button_save").removeAttr("disabled");
		}
	},

	checkEmail: function(event){
		var input = $(event.target);
		if(!input.val().length){
			input.removeClass("state-good").removeClass("state-bad");
			return;
		}
		if(!input.val().match(ModuleResourceAuthLocal.regExpEmail)){
			input.removeClass("state-good").removeClass("state-bad");
			return;
		}
		$.ajax({
			url: "./ajax/auth/local/emailExists",
			method: "post",
			data: {email: input.val()},
			dataType: "json",
			context: input,
			success: function(response){
				$(this).removeClass("state-good").removeClass("state-bad");
				$(this).addClass(response.data ? "state-bad" : "state-good");
			}
		});
	},

	checkPassword: function(event){
		var input = $(event.target);
		if(!input.val().length){
			input.removeClass("state-good").removeClass("state-bad");
			return;
		}
		if(input.val().length < settings.Resource_Users.password_length_min){
			input.removeClass("state-good").addClass("state-bad");
			return;
		}
		else if(settings.Resource_Users.password_strength_min){
			$.ajax({
				url: "./ajax/auth/passwordStrength",
				method: "post",
				data: {password: input.val()},
				dataType: "json",
				context: input,
				success: function(response){
					var tooWeak	= response.data < settings.Resource_Users.password_strength_min;
					$(this).removeClass("state-good").removeClass("state-bad");
					$(this).addClass(tooWeak ? "state-bad" : "state-good");
				}
			});
		}
		else{
			input.removeClass("state-bad").addClass("state-good");
		}
	},

	checkUsername: function(event){
		var input = $(event.target);
		var lenMin = settings.Resource_Users.name_length_min;
		var lenMax = settings.Resource_Users.name_length_max;
		var length = input.val().length;
		if(!length){
			input.removeClass("state-good").removeClass("state-bad");
			return;
		}
		if(input.data("last") != input.val()){
			if(settings.Resource_Users.name_preg){
				var preg = settings.Resource_Users.name_preg;
				var flags = preg.replace(/.*\/([gimy]*)$/, '$1');
				var pattern = preg.replace(new RegExp('^/(.*?)/'+flags+'$'), '$1');
				var regex = new RegExp(pattern, flags);
				if(!regex.test(input.val())){
					input.val(input.data("last"));
				}
			}
			input.data("last", input.val());
			if(lenMin > length || length > lenMax ){
				input.removeClass("state-good").addClass("state-bad");
				return;
			}
			$.ajax({
				url: "./ajax/auth/local/usernameExists",
				method: "post",
				data: {username: input.val()},
				dataType: "json",
				context: input,
				success: function(response){
					$(this).removeClass("state-good").removeClass("state-bad");
					$(this).addClass(response.data ? "state-bad" : "state-good");
				}
			});
		}
	}
};
