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

	init: function(userId, rememberUser){
		Auth.userId = userId;
		Auth.config	= settings.Resource_Authentication;
		if(userId){
			if(!rememberUser)
				Auth.initAutoLogout();
			Auth.initSessionRefresh();
		}
	},

	initAutoLogout: function(){
		var minutes = parseInt(Auth.config.logout_auto_minutes);
		if(!Auth.config.logout_auto || !minutes)
			return;
		Auth.autoLogout.seconds = minutes * Auth.oneMinute;
		$.ajax({
			url: './ajax/auth/isAuthenticated',
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
				Auth.autoLogout.left = minutes * Auth.oneMinute;
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
					url: './ajax/auth/isAuthenticated',
					dataType: 'json',
					type: 'POST',
					success: function(json){
						if(typeof json.data.result !== "undefined"){
							if(!json.data.result)
								document.location.reload();
						}
					}
				});
			}, minutes * Auth.oneMinute * 1000
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
			var factorHour		= 60 * Auth.oneMinute;
			var hours	= Math.floor(Auth.autoLogout.left / factorHour );
			var minutes = Math.floor((Auth.autoLogout.left - hours * factorHour) / Auth.oneMinute);
			var seconds = Auth.autoLogout.left - hours * factorHour - minutes * Auth.oneMinute;
			if((""+seconds).length == 1)
				seconds = "0"+seconds;
			if(hours && (""+minutes).length == 1)
				minutes = "0"+minutes;
			var label	= minutes+":"+seconds;
			if(hours)
				label	= hours+":"+label;
			if(Auth.autoLogout.left <= Auth.oneMinute)
				label	= $("<span></span>").addClass( "label label-important").html(label);
			$("#auth-auto-logout-timer").html(label);
			Auth.autoLogout.left--;
		}
	}
};

(function () {
	var authVersion = settings.Resource_Authentication._version;
	if(!authVersion || authVersion.match(/0\.[0.5]\.[0-8]/))
		Auth.init(0);
}());
