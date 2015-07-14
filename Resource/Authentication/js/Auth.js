var Auth = {

	autoLogout: {
		seconds: 0,
		interval: null,
		left: 0,
		url: './auth/logout'
	},
	config: [],
	oneMinute: 60,
	userId: 0,

	init: function(userId){
		Auth.userId = userId;
		Auth.config	= settings.Resource_Authentication;
		if(userId){
			Auth.initAutoLogout();
			Auth.initSessionRefresh();
		}
	},

	initUserRegistration: function(){
		$("#input_username").keyup(Auth.Check.username).trigger("keyup");
		$("#input_email").keyup(Auth.Check.email).trigger("keyup");
		$("#input_password").keyup(Auth.Check.password);

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

	initAutoLogout: function(){
		var minutes = parseInt(Auth.config.logout_auto_minutes);
		if(!Auth.config.logout_auto || !minutes)
			return;
		Auth.autoLogout.seconds = minutes * 60;
		$.ajax({
			url: './auth/ajaxIsAuthenticated',
			dataType: 'json',
			success: function(status){
				if(!status)
					return;
				if(Auth.config.logout_auto_forward_controller){
					Auth.autoLogout.url += '/'+Auth.config.logout_auto_forward_controller;
					if(Auth.config.logout_auto_forward_action)
						Auth.autoLogout.url += '/'+Auth.config.logout_auto_forward_action;
				}
				Auth.autoLogout.url += '?autoLogout';
				Auth.refreshAutoLogoutInterval();
				$("body").on('click', function(){
					Auth.refreshAutoLogoutInterval();
				});
				Auth.autoLogout.left = minutes * 60;
				Auth.updateAutoLogoutTimer();
				window.setInterval(Auth.updateAutoLogoutTimer, 1000);
			}
		});
	},

	initSessionRefresh:function(){
		var minutes = parseInt(Auth.config.session_refresh_minutes);
		if(!Auth.config.session_refresh || !minutes)
			return;
		window.setInterval(
			function(){
				$.ajax({
					url: './auth/ajaxRefreshSession',
					dataType: 'json',
					type: 'POST',
					success: function(json){
						if(!json){
							document.location.reload();
						}
					}
				});
			}, minutes * 60 * 1000
		);
	},

	refreshAutoLogoutInterval: function(){
		if(Auth.autoLogout.interval)
			window.clearInterval(Auth.autoLogout.interval);
		Auth.autoLogout.left = Auth.autoLogout.seconds;
		Auth.autoLogout.interval = window.setInterval(function(){
			document.location.href = Auth.autoLogout.url;
		}, Auth.autoLogout.seconds * 1000);
	},

	updateAutoLogoutTimer: function(){
		if(Auth.autoLogout.left > 0){
			var factorMinute	= 60;
			var factorHour		= 60 * factorMinute;
			var hours	= Math.floor(Auth.autoLogout.left / factorHour );
			var minutes = Math.floor((Auth.autoLogout.left - hours * factorHour) / factorMinute);
			var seconds = Auth.autoLogout.left - hours * factorHour - minutes * factorMinute;
			if((""+seconds).length == 1)
				seconds = "0"+seconds;
			if(hours && (""+minutes).length == 1)
				minutes = "0"+minutes;
			var label	= minutes+":"+seconds;
			if(hours)
				label	= hours+":"+label;
			if(Auth.autoLogout.left <= 60)
				label	= $("<span></span>").addClass( "label label-important").html(label);


			$("#auth-auto-logout-timer").html(label);
			Auth.autoLogout.left--;
		}
	}

};

Auth.Check = {
	email: function(event){
		var input = $(event.target);
		if(!input.val().length){
			input.removeClass("state-good").removeClass("state-bad");
			return;
		}
		$.ajax({
			url: "./auth/ajaxEmailExists",
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

	password: function(event){
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

	username: function(event){
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
				url: "./auth/ajaxUsernameExists",
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


(function () {
	var authVersion = settings.Resource_Authentication._version;
	if(!authVersion || authVersion.match(/0\.[0.5]\.[0-8]/))
		Auth.init(0);
}());
