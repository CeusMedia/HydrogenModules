var Auth = {

	oneMinute: 60,
	autoLogout: {
		seconds: 0,
		interval: null,
		left: 0,
		url: './auth/logout'
	},
	config: [],

	init: function(){
		Auth.config	= settings.Resource_Authentication;
		Auth.initAutoLogout();
		Auth.initSessionRefresh();
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
					dataType: 'html',
					type: 'POST'
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

Auth.init();
