
Auth.Registration = {
	init: function(){
		$("#input_username").keyup(Auth.Registration.checkUsername).trigger("keyup");
		$("#input_email").keyup(Auth.Registration.checkEmail).trigger("keyup");
		$("#input_password").keyup(Auth.Registration.checkPassword);

		if($("#input_accept_tac").size()){
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
		$.ajax({
			url: "./auth/local/ajaxEmailExists",
			method: "post",
			data: {email: input.val()},
			dataType: "json",
			context: input,
			success: function(response){
				$(this).removeClass("state-good").removeClass("state-bad");
				$(this).addClass(response ? "state-bad" : "state-good");
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
				url: "./auth/ajaxPasswordStrength",
				method: "post",
				data: {password: input.val()},
				dataType: "json",
				context: input,
				success: function(response){
					var tooWeak	= response < settings.Resource_Users.password_strength_min;
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
				url: "./auth/local/ajaxUsernameExists",
				method: "post",
				data: {username: input.val()},
				dataType: "json",
				context: input,
				success: function(response){
					$(this).removeClass("state-good").removeClass("state-bad");
					$(this).addClass(response ? "state-bad" : "state-good");
				}
			});
		}
	}
};
